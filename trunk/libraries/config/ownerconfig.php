<?php

/**
 * Class Config_OwnerConfig
 * @property string id
 * @property string username
 * @property string password
 * @property string defaultImage
 * @property string asssetsFolder
 * @property string[] sizes
 */
class Config_OwnerConfig
{
    /**
     * @param $username
     * @return bool|Config_OwnerConfig
     */
    public static function load($username)
    {
        $KEY = $username . '_OwnerConfig';
        $isProduction = ConfigHandler::item('env') == ENV_PRODUCTION;
        if($isProduction && ($object = apc_fetch($KEY)))
        {
            return unserialize($object);
        }
        else
        {
            $xml = simplexml_load_file('../config/' . $username . '.xml');

            if($xml)
            {
                $object = new self;
                $object->id = (int)((string)$xml->id);
                $object->username = $username;

                $algorithm = (string)$xml->password->attributes()->algorithm;
                $object->password = call_user_func($algorithm, (string)$xml->password);

                $object->defaultImage = (string)$xml->defaultImage;
                $object->asssetsFolder = (string)$xml->asssetsFolder;

                $sizes = (array)$xml->sizes;
                $object->sizes = $sizes['size'];

                if($isProduction)
                    apc_store($KEY, serialize($object), 3600 * 24);
                return $object;
            }
        }

        return false;
    }

    public function isValidHash($hash, $publicKey)
    {
        return sha1($this->username . '_' . $this->password . '_' . $publicKey) == $hash;
    }
}