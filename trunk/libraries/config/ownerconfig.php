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
            $object->sizes = array_pop(array_values((array)$xml->sizes));

            return $object;
        }

        return false;
    }

    public function isValidHash($hash, $publicKey)
    {
        return sha1($this->username . '_' . $this->password . '_' . $publicKey) == $hash;
    }
}