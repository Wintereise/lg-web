<?php
/**
 * Created by PhpStorm.
 * User: Tomoyo
 * Date: 1/29/2015
 * Time: 午後 08:32
 */

class lgAPI
{
    private $url, $key;
    private $curl, $response;

    public function __construct ($url, $key)
    {
        $this->reset($url, $key);
    }

    public function sendCommand ($type, $params, $mask = null)
    {
        switch ($type)
        {
            case 'ping':
            case 'ping6':
            case 'traceroute':
            case 'traceroute6':
                curl_setopt($this->curl, CURLOPT_URL, sprintf("%s/%s/%s", $this->url, $type, $params));
            break;
            case 'bgp':
                curl_setopt($this->curl, CURLOPT_URL, sprintf("%s/%s/%s/%s", $this->url, $type, $params, $mask));
            break;
        }
        $this->response = curl_exec($this->curl);
        curl_close($this->curl);
        return $this;
    }

    public function getJsonResponse ()
    {
        return $this->response;
    }

    public function getArrayResponse ()
    {
        return json_decode($this->response, true);
    }

    public function reset ($url, $key)
    {
        $this->url = $url;
        $this->key = (strlen($key) == 64)? $key : null;
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_USERPWD, $key . ":" . $key);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
    }
}