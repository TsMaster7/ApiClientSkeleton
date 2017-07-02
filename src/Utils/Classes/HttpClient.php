<?php

namespace Utils\Classes;

/**
 * Simple CURL-based HTTP-client
 * Sign and sends post-requests, and parses responses
 * Class HttpClient
 * @package Utils\Classes
 */
class HttpClient
{
    private $curl = null;

    private $url;
    private $clientId = 'w92w9ue838eu2h5y';   //client id for server auth
    private $digestSalt = 'Black rock';       //client salt for auth token

    private function setCommonOptions()
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER         => true,   // return headers
            CURLOPT_FOLLOWLOCATION => false,  // don`t follow redirects
            CURLOPT_USERAGENT      => 'curl', // name of client
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT        => 120,    // time-out on response
        ];

        if ($this->curl) {
            curl_setopt_array($this->curl, $options);
        }
        return $this;
    }

    /**
     * Devides Http-response on head and body
     * @param $response - full http-response
     * @return array|bool - return head and body in array of false if response empty
     */
    private function parseResponse($response)
    {
        $responseParts = explode("\r\n\r\n", $response);
        if (is_array($responseParts) && count($responseParts) == 2) {
            return [
                'headers' => $responseParts[0],
                'body'    => $responseParts[1]
            ];
        }
        return false;
    }

    public function __construct($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            throw new \Exception('Curl error: Invalid URL specified: ' . $url);
        }
        if (!$this->curl = curl_init($url)) {
            throw new \Exception('Curl error: Initialization failed!');
        }

        $this->url = $url;
        $this->setCommonOptions();

        return $this;
    }

    /**
     * Set curl options to send post request,
     * adds clientId and authToken to the request
     * @param array $params
     * @return $this
     */
    public function setPost(array $params = [])
    {
        if ($this->curl) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            $params['clientId'] = $this->clientId;
            $params['authToken'] = sha1($this->url . $this->digestSalt);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        return $this;
    }

    /**
     * Sends request, parses the response and return encoded as php object result (if response`s body is correct JSON),
     * or error structure if something went wrong
     * @return array|null
     */
    public function execute()
    {
        $result = null;

        $response = curl_exec($this->curl);
        $responseCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $errors = curl_error($this->curl);

        if ($responseCode != '200') {
            if ($errors) {
                $result = ['error' => $errors];
            }
            $result['response_code'] = $responseCode;
        }

        if ($response && !$errors && $responseCode == '200') {
            $responseParts = $this->parseResponse($response);
            if ($responseParts && isset($responseParts['body'])) {
                //we have body of http-response now
                if ($encodedResult = json_decode($responseParts['body'])) {
                    //we have encode server`s response now
                    $result = $encodedResult;
                } else {
                    //server`s response body is not in json, something was wrong
                    print $responseParts['body'];
                    $result = ['error' => 'Unknown format of server`s response '];
                }
            } else {
                //problems with parsing http-response, empty body or something else
                $result = ['error' => 'Empty or invalid server`s response'];
            }
        }

        curl_close($this->curl);

        return $result;
    }
}