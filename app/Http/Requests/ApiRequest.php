<?php

namespace App\Http\Requests;
use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends Request
{
    const ERROR_API_CALLING = 'You have to specify a method (eg. POST, PUT, ...) and a correct object url to call the API';
    const ERROR_CURL_ERROR = 'HTTP error while calling the API. Error code and message: ';
    const ERROR_CSCART_API_MESSAGE = 'Message from CS-Cart API: ';

    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    );
    protected $apiKey;
    protected $userLogin;
    protected $apiUrl;

    public function __construct()
    {
        $this->setUserLogin(env('CSCART_USER_LOGIN'));
        $this->setApiKey(env('CSCART_API_KEY'));
        $this->setApiUrl(env('CSCART_API_URL'));
    }

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function setUserLogin($userLogin) {
        $this->userLogin = $userLogin;
    }

    public function setApiUrl($apiUrl) {
        $this->apiUrl = trim($apiUrl, '/').'/api/';
    }

    public function apiCall($method, $objectUrl, $data = '', $params = array()) {
        if (!empty($method) && !empty($objectUrl)) {
            return $this->makeRequest($objectUrl, $method, $data, $params);
        }
        else {
            return self::ERROR_API_CALLING;
        }
    }

    protected function makeRequest($objectUrl, $method, $data = '', $params = array()) {
        $ch = curl_init();

        $opts = self::$CURL_OPTS;

        $opts[CURLOPT_URL] = $this->initUrl($objectUrl, $params);
        $opts[CURLOPT_USERPWD] = $this->getAuthString();
        $opts[CURLOPT_SSL_VERIFYHOST ] = 0;
        $opts[CURLOPT_SSL_VERIFYPEER  ] = 0;
        $this->setHeader($opts, 'Content-Type: application/json');


        if ($method == 'POST' || $method == 'PUT') {
            $postdata = $this->generatePostData($data);
        } else {
            unset($data);
        }
        
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $opts[CURLOPT_CUSTOMREQUEST] = 'POST';
                $opts[CURLOPT_RETURNTRANSFER] = TRUE;
                $opts[CURLOPT_POSTFIELDS] = $postdata;
                $this->setHeader($opts, 'Content-Length: ' . strlen($postdata));
                break;
            case 'PUT':
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_RETURNTRANSFER] = TRUE;
                $opts[CURLOPT_POSTFIELDS] = $postdata;
                $this->setHeader($opts, 'Content-Length: ' . strlen($postdata));
                break;
            case 'DELETE':
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
        }
        
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);

        if ($result === false) {
            print_r(curl_error($ch));
            curl_close($ch);
        }
        curl_close($ch);
        return $this->parseResult($result);
    }

    protected function initUrl($objectUrl, $params)
    {
        $params = http_build_query($params);
        $params = $params? '?'.$params:'';
        return $this->apiUrl . $objectUrl . $params;
    }

    protected function getAuthString() {
        return $this->userLogin . ":" . $this->apiKey;
    }

    protected function setHeader(&$opts, $headerString) {
        $opts[CURLOPT_HTTPHEADER][] = $headerString;
    }

    protected function generatePostData($data) {
        return json_encode($data);
    }

    protected function parseResult($jsonResult){
        $result = (array)json_decode($jsonResult);
        if (!empty($result['message'])) {
            return self::ERROR_CSCART_API_MESSAGE.$result['message'];
        } else {
            return $result;
        }
    }

}