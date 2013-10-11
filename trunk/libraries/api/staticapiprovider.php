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

    /**
     * @var Config_OwnerConfig
     */
    private $_User;

    private $_RequestID;

    const PUBLIC_KEY = 'cbeca29d4d7614d4ccf095c187ed2716e2f98eef';
    const ORIGINALS_FOLDER = 'originals';

    private function __construct()
    {
        $this->_MongoDBInstance = MongoDBWrapper::getMongoDBInstance();
        $this->_RequestID = substr(md5(uniqid()), 0, 5);
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
            $fragments = explode('@', $_POST['hash']);

            if(count($fragments) == 2)
            {
                $owner = Config_OwnerConfig::load($fragments[0]);

                if($owner && $owner->isValidHash($fragments[1], self::PUBLIC_KEY))
                {
                    $this->logRequest('auth', 'SUCCESS', 'Hash: ' . $_POST['hash']);
                    $this->_User = $owner;
                }
                else
                {
                    $this->logRequest('auth', 'FAIL', 'HashValidation not passed (' . $_POST['hash'] . ')');
                    throw new Exception($this->authError());
                }
            }
        }
        else
        {
            $this->logRequest('auth', 'FAIL', 'Hash: ' . $_POST['hash']);
            throw new Exception($this->invalidHashError());
        }
    }


    //
    // Error Handling & Output
    // ------------------------------------------------------------------------------------------------------------
    //
    public function error($code, $message)
    {
        return $this->message($code, $message, null, true);
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

        if(isset($_POST['binary']) && isset($_POST['localId']) && !empty($_POST['localId']) && isset($_POST['title']))
        {
            // Media Type Validation
            $mimeType = $this->getBufferMimeType($_POST['binary']);
            $this->validateBufferMimeType($mimeType, 'imagen');

            // Image Existence Check
            $image = MongoDBWrapper::getMongoDBInstance()->image->findOne(array(
                'owner' => $this->_User->id,
                'refId' => (int)$_POST['localId'],
            ));

            // Store Path Calculation
            $relativeStorePath = str_replace(':owner', $this->_User->username, STORE_RELATIVE_PATH);
            $absolutStorePath = ASSETS_PATH . $relativeStorePath;

            if($image)
            {
                // Old original & thumb unlink
                @unlink(ASSETS_PATH . $image['path']);
                foreach($image['thumbs'] as $thumb)
                {
                    @unlink(ASSETS_PATH . '/' . $thumb['path']);
                }

                $this->logRequest('action', 'IMAGE - DELETED_4_REPLACEMENT', $image);
                $this->_MongoDBInstance->image->remove(array('_id' => new MongoId($image['_id'])));
            }

            // Save
            $jpegMimeType = 'image/jpeg';
            if($mimeType == $jpegMimeType)
                $binary = $_POST['binary'];
            else
                $binary = JPGImageHandler::getImageConverted($_POST['binary'], $mimeType, $jpegMimeType);

            $cacheKey = self::getCacheKeyForImage(true, $_POST['localId'], self::getSEOString($_POST['title']));
            $imageStorePath = $relativeStorePath . FileSystemCache::getInstance()->store($cacheKey, $absolutStorePath , $binary);

            $mongoImage = array(
                'title' => $_POST['title'],
                'owner' => $this->_User->id,
                'created' => new MongoDate(),
                'path' => $imageStorePath,
                'thumbs' => array(),
                'refId' => (int)$_POST['localId'],
                'mimeType' => $mimeType,
            );
            $this->_MongoDBInstance->image->insert($mongoImage, array(
                'fsync' => true
            ));
            $this->logRequest('action', 'IMAGE - UPLOADED', $mongoImage);

            JPGImageHandler::compressImage($absolutStorePath);

            $url = ConfigHandler::item('routes');
            return $this->message(200, 'Image Uploaded', array(
                'sId' => $mongoImage['_id']->{'$id'},
                'permalink' => str_replace(
                    array(':owner', ':id', ':title'),
                    array($this->_User->username, $_POST['localId'], self::getSEOString($_POST['title'])),
                    $url['image']
                )
            ));
        }
        else
        {
            $this->logRequest('action', 'IMAGE - UPLOAD_ERROR', 'File Required');
            return $this->error(HTTP_BAD_REQUEST, 'File Required');
        }
    }

    public function deleteImage()
    {
        // User Validation
        $this->validateUser();

        if(isset($_POST['sId']))
        {
            // Image Existence Check
            $image = MongoDBWrapper::getMongoDBInstance()->image->findOne(array(
                '_id' => new MongoId($_POST['sId'])
            ));

            if($image)
            {
                if($this->_User->id == $image['owner'])
                {
                    // Store Path Calculation
                    $relativeStorePath = str_replace(':owner', $this->_User->username, STORE_RELATIVE_PATH);
                    $absolutStorePath = ASSETS_PATH . $relativeStorePath;

                    @unlink(ASSETS_PATH . $image['path']);
                    foreach($image['thumbs'] as $thumb)
                    {
                        @unlink(ASSETS_PATH . $thumb['path']);
                    }

                    $this->_MongoDBInstance->image->remove(array('_id' => new MongoId($image['_id'])));
                    $this->logRequest('action', 'IMAGE - DELETED', $image);
                    return $this->message(200, 'Image Deleted');
                }
                else
                {
                    $this->logRequest('action', 'IMAGE - DELETE_ERROR', 'Forbidden');
                    return $this->message(403, 'Forbidden');
                }
            }
        }

        $this->logRequest('action', 'IMAGE - DELETE_ERROR', 'File Required (sId missing)');
        return $this->error(HTTP_BAD_REQUEST, 'File Required (sId missing)');
    }

    public function uploadAsset()
    {
        // User Validation
        $this->validateUser();

        if(isset($_POST['binary']) && isset($_POST['localId']) && !empty($_POST['localId']) && isset($_POST['title']))
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

            // Asset Existence Check
            $isUpdate = false;
            if(isset($_POST['sId']) && !empty($_POST['sId']))
            {
                // Update
                $asset = MongoDBWrapper::getMongoDBInstance()->image->findOne(array(
                    'owner' =>$this->_User->id,
                    'refId' => (int)$_POST['localId'],
                    '_id' => new MongoId($_POST['sId'])
                ));

                if(!$asset)
                {
                    $this->logRequest('action', 'ASSET - UPLOAD_ERROR', 'Inexistent Asset');
                    return $this->error(HTTP_BAD_REQUEST, 'Inexistent Asset');
                }

                $isUpdate = true;
            }

            // Store Path Calculation
            $relativeStorePath = str_replace(':owner', $this->_User->username, STORE_RELATIVE_PATH);
            $absolutStorePath = ASSETS_PATH . $relativeStorePath;

            if($isUpdate)
            {
                @unlink(ASSETS_PATH . $asset['path']);
                $this->_MongoDBInstance->asset->remove(array('_id' => new MongoId($asset['_id'])));
                $this->logRequest('action', 'ASSET - DELETED_4_REPLACEMENT', $asset);
            }

            $cacheKey = self::getCacheKeyForAsset($assetType, $_POST['localId'], $_POST['title'], $extension);
            $cacheFileName =  FileSystemCache::getInstance()->store($cacheKey, $absolutStorePath , $_POST['binary'], $extension);

            $mongoAsset = array(
                'owner' => $this->_User->id,
                'type' => $assetType,
                'created' => new MongoDate(),
                'path' => $relativeStorePath . $cacheFileName,
                'refId' => (int)$_POST['localId'],
                'mimeType' => $assetMimeType,
                'fileSize' => filesize($absolutStorePath . $cacheFileName),
                'titulo' => $_POST['title'],
                'ext' => $extension,
            );
            $this->_MongoDBInstance->asset->insert($mongoAsset, array(
                'fsync' => true
            ));

            if($extension == 'jpg')
                JPGImageHandler::compressImage($absolutStorePath . $cacheFileName);


            $this->logRequest('action', 'ASSET - UPLOADED', $mongoAsset);
            $url = ConfigHandler::item('routes');
            return $this->message(200, 'Asset Uploaded', array(
                'sId' => $mongoAsset['_id']->{'$id'},
                'permalink' => str_replace(
                    array(':type', ':id', ':title', ':ext'),
                    array($mongoAsset['type'], $mongoAsset['refId'], self::getSEOString($mongoAsset['titulo']), $mongoAsset['ext']),
                    $url['asset']
                )
            ));
        }
        else
        {
            $this->logRequest('action', 'ASSET - UPLOAD_ERROR', 'File, Id & Title Required');
            return $this->error(HTTP_BAD_REQUEST, 'File, Id & Title Required');
        }
    }

    public function deleteAsset()
    {
        // User Validation
        $this->validateUser();

        if(isset($_POST['sId']))
        {
            $asset = MongoDBWrapper::getMongoDBInstance()->asset->findOne(array(
                '_id' => new MongoId($_POST['sId'])
            ));

            if($asset)
            {
                if($this->_User->id == $asset['owner'])
                {
                    // Store Path Calculation
                    $relativeStorePath = str_replace(':owner', $this->_User->username, STORE_RELATIVE_PATH);
                    $absolutStorePath = ASSETS_PATH . $relativeStorePath;

                    if($asset)
                    {
                        $this->_MongoDBInstance->asset->remove(array('_id' => new MongoId($asset['_id'])));
                        @unlink(ASSETS_PATH . $asset['path']);
                        $this->logRequest('action', 'ASSET - DELETED', $asset);
                    }

                    $this->logRequest('action', 'ASSET - DELETED', $asset);
                    return $this->message(200, 'Image Deleted');
                }
                else
                {
                    $this->logRequest('action', 'ASSET - DELETE_ERROR', 'Forbidden');
                    return $this->message(403, 'Forbidden');
                }
            }
        }
        else
        {
            $this->logRequest('action', 'IMAGE - DELETE_ERROR', 'File Required (sId missing)');
            return $this->error(HTTP_BAD_REQUEST, 'File Required (sId missing)');
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

    protected function logRequest($type, $action, $desc)
    {
        if(ConfigHandler::item($type . 'Log'))
        {
            MongoDBWrapper::getMongoDBInstance()->requestLog->insert(array(
                'date' => new MongoDate(),
                'request' => $this->_RequestID,
                'type' => $type,
                'action' => $action,
                'desc' => $desc,
            ), array('w' => 0));
        }
    }
}