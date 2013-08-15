<?php
class StaticApi
{
    private $_Hash;
    const REMOTE_KEY = 'cbeca29d4d7614d4ccf095c187ed2716e2f98eef';
    const BASE_URL = 'http://static.infomedia.im/api/';

    public function __construct($username, $password, $public_key)
    {
        $this->_Hash = $public_key . '_' . sha1($username . '_' . sha1($password) . '_' . self::REMOTE_KEY);
    }

    public function getAuthHash()
    {
        return $this->_Hash;
    }

    public function uploadImage($localId, $title, $filePath)
    {
        $this->uploadFile($localId, $title, $filePath, self::BASE_URL . 'uploadImage');
    }

    public function uploadAsset($localId, $title, $filePath)
    {
      $this->uploadFile($localId, $title, $filePath, self::BASE_URL . 'uploadAsset');
    }

    protected function uploadFile($localId, $title, $filePath, $toURL)
    {
        $ch = curl_init();
        $data = array(
            'localId' => $localId,
            'binary' => file_get_contents($filePath),
            'hash' => $this->getAuthHash(),
            'title' => $title,
        );

        curl_setopt($ch, CURLOPT_URL, $toURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);
    }
}