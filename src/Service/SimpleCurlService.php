<?php

namespace App\Service;

class SimpleCurlService
{
    private $ch;
    private $url;

    public function __construct($url = null)
    {
        if($url) {
            $this->ch = curl_init($url);
            $this->url = $url;
        } else {
            $this->ch = curl_init();
        }
    }

    public function setOpt($curlOpt, $param)
    {
        curl_setopt($this->ch, $curlOpt, $param);

        return $this;
    }

    public function getResponse()
    {
        $response = curl_exec($this->ch);
        if(!$response) {
            throw new \Exception('Unable to finish request');
        }

        curl_close($this->ch);
        return $response;
    }

    public function curlReset()
    {
        $this->ch = curl_init();
        return $this;
    }
}