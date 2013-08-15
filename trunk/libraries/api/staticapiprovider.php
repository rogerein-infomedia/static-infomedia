<?php
class API_StaticApiProvider
{
    /**
     * @var API_StaticApiProvider
     */
    private static $_Instance;

    /**
     * @var MongoDB
     */
    private $_MongoDBInstance;

    private $_User;
    const PUBLIC_KEY = 'cbeca29d4d7614d4ccf095c187ed2716e2f98eef';
    const ORIGINALS_FOLDER = 'originals';

    private function __construct()
    {
        $this->_MongoDBInstance = MongoDBWrapper::getMongoDBInstance();
    }

    public static function getInstance()
    {
        if(is_null(self::$_Instance))
            self::$_Instance = new API_StaticApiProvider();

        return self::$_Instance;
    }

    private function validateUser()
    {
        if(isset($_POST['hash']))
        {
            $tokens = explode('_', $_POST['hash']);

            if(count($tokens) == 2)
            {
                $user = $this->_MongoDBInstance->remoteUser->findOne(array('_id' => new MongoId($tokens[0])));
                if($user)
                {
                    if($tokens[1] != sha1($user['username'] . '_' . $user['password'] . '_' . self::PUBLIC_KEY))
                        throw new Exception($this->authError());
                    else
                        $this->_User = $user;
                }
                else
                {
                    throw new Exception($this->authError());
                }
            }
        }
        else
        {
            throw new Exception($this->invalidHashError());
        }
    }


    //
    // Error Handling & Output
    // ------------------------------------------------------------------------------------------------------------
    //
    public function error($code, $message)
    {
        return $this->message($code, $message, true);
    }

    private function invalidHashError()
    {
        return $this->error(HTTP_BAD_REQUEST, 'Invalid Hash');
    }

    private function authError()
    {
        return $this->error(HTTP_UNAUTHORIZED, 'Invalid Username, Password or AccessKey');
    }

    public function message($code, $message, $data = array(), $error = false)
    {
        return json_encode(array(
            'code' => $code,
            'error' => $error,
            'message' => $message,
            'data' => $data
        ));
    }

    private function getBufferMimeType($content)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($content);
    }

    private function validateBufferMimeType($mimeType, $supportedMimeTypesConfigKey)
    {
        $supportedMimeTypes = ConfigHandler::item('supportedMimeTypes');
        if(!isset($supportedMimeTypes[$supportedMimeTypesConfigKey][$mimeType]))
            throw new Exception($this->error(HTTP_UNSUPPORTED_MEDIA_TYPE, 'Media Type not Supported'));
    }

    //
    // API Methods
    // ------------------------------------------------------------------------------------------------------------
    //
    public function uploadImage()
    {
        // User Validation
        $this->validateUser();

        if(isset($_POST['binary']) && isset($_POST['localId']) && isset($_POST['title']))
        {
            // Media Type Validation
            $mimeType = $this->getBufferMimeType($_POST['binary']);
            $this->validateBufferMimeType($mimeType, 'imagen');

            // Image Existence Check
            $image = MongoDBWrapper::getMongoDBInstance()->image->findOne(array(
                'owner' => MongoDBRef::create('remoteUser', new MongoId($this->_User['_id']->{'$id'})),
                'refId' => $_POST['localId']
            ));

            // Store Path Calculation
            $relativeStorePath = str_replace(':owner', $this->_User['ownerName'], STORE_RELATIVE_PATH);
            $absolutStorePath = ASSETS_PATH . $relativeStorePath;

            if($image)
            {
                // Old original & thumb unlink
                @unlink(ASSETS_PATH . $image['path']);
                foreach($image['thumbs'] as $thumb)
                {
                    @unlink(ASSETS_PATH . $thumb['path']);
                }

                $this->_MongoDBInstance->image->remove(array('_id' => new MongoId($image['_id'])));
            }

            // Save
            $cacheKey = self::getCacheKeyForImage(true, $_POST['localId'], $_POST['title']);
            $imageStorePath = $relativeStorePath . FileSystemCache::getInstance()->store($cacheKey, $absolutStorePath , $_POST['binary']);
            $this->_MongoDBInstance->image->insert(array(
                'owner' => MongoDBRef::create('remoteUser', $this->_User['_id']),
                'created' => new MongoDate(),
                'path' => $imageStorePath,
                'thumbs' => array(),
                'refId' => $_POST['localId'],
                'mimeType' => $mimeType,
            ));

            JPGImageHandler::compressImage($absolutStorePath);

            $url = ConfigHandler::item('routes');
            return $this->message(200, 'Image Uploaded', str_replace(
                array(':owner', ':id', ':title'),
                array($this->_User['ownerName'], $_POST['localId'], self::getSEOString($_POST['title'])),
                $url['image']
            ));
        }
        else
        {
            return $this->error(HTTP_BAD_REQUEST, 'File Required');
        }
    }

    public function uploadAsset()
    {
        // User Validation
        $this->validateUser();

        if(isset($_POST['binary']) && isset($_POST['localId']) && isset($_POST['title']))
        {
            // Media Type Validation
            $assetMimeType = $this->getBufferMimeType($_POST['binary']);
            $assetType = false;
            $extension = false;

            $supportedMimeTypes = ConfigHandler::item('supportedMimeTypes');
            foreach($supportedMimeTypes as $type => $mimeTypes)
            {
                foreach($mimeTypes as $mimeType => $mimeTypeExtension)
                {
                    if($mimeType == $assetMimeType)
                    {
                        $assetType = $type;
                        $extension = $mimeTypeExtension;
                        break;
                    }
                }
            }

            if($assetType === false)
                throw new Exception($this->error(HTTP_UNSUPPORTED_MEDIA_TYPE, 'Media Type not Supported'));

            // Image Existence Check
            $asset = MongoDBWrapper::getMongoDBInstance()->image->findOne(array(
                'owner' => MongoDBRef::create('remoteUser', new MongoId($this->_User['_id']->{'$id'})),
                'refId' => $_POST['localId'],
                'type' => $assetType,
            ));

            // Store Path Calculation
            $relativeStorePath = str_replace(':owner', $this->_User['ownerName'], STORE_RELATIVE_PATH);
            $absolutStorePath = ASSETS_PATH . $relativeStorePath;

            if($asset)
            {
                @unlink(ASSETS_PATH . $asset['path']);
                $this->_MongoDBInstance->asset->remove(array('_id' => new MongoId($asset['_id'])));
            }

            $cacheKey = self::getCacheKeyForAsset($assetType, $_POST['localId'], $_POST['title'], $extension);
            $cacheFileName =  FileSystemCache::getInstance()->store($cacheKey, $absolutStorePath , $_POST['binary'], $extension);
            $this->_MongoDBInstance->asset->insert(array(
                'owner' => MongoDBRef::create('remoteUser', $this->_User['_id']),
                'type' => $assetType,
                'created' => new MongoDate(),
                'path' => $relativeStorePath . $cacheFileName,
                'refId' => $_POST['localId'],
                'mimeType' => $assetMimeType,
                'fileSize' => filesize($absolutStorePath . $cacheFileName),
                'ext' => $extension,
            ));

            if($extension == 'jpg')
                JPGImageHandler::compressImage($absolutStorePath . $cacheFileName);


            return $this->message(200, 'Asset Uploaded');
        }
        else
        {
            return $this->error(HTTP_BAD_REQUEST, 'File, Id & Title Required');
        }
    }

    public static function getCacheKeyForAsset($type, $refId, $title, $ext)
    {
        return $type . '/i' . $refId . '-' . self::getSEOString($title) . '.' . $ext;
    }

    public static function getCacheKeyForImage($isOriginal, $refId, $title)
    {
        $key = '';
        if($isOriginal)
            $key .= 'original/';

        return $key. 'i' . $refId. '-' . self::getSEOString($title);
    }

    public static function getSEOString($text, $separador = '-')
    {
        $text = preg_replace('/&(.)[uml|acute|tilde|cedil|circ|circumflex|ring|grave]+;/i', '\1', strtolower($text));
        $text = htmlentities($text, ENT_NOQUOTES);
        $text = preg_replace('/&(.)[uml|acute|tilde|cedil|circ|circumflex|ring|grave]+;/i', '\1', $text);
        $text = str_replace(array('&ldquo;', '&rdquo;', '&amp'), '', $text);
        $text = preg_replace("/[^a-zA-Z0-9]+/", $separador, $text);
        $text = trim($text, $separador);
        return strtolower($text);
    }
}