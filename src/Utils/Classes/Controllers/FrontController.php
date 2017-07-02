<?php

namespace Utils\Classes\Controllers;

use Utils\Classes\ApiGatewayChecker;
use Utils\Classes\AuthStub;
use Utils\Classes\HttpClient;
use Utils\Classes\Request;

/**
 * Front controller of client application
 * Class FrontController
 * @package Utils\Classes\Controllers
 */
class FrontController
{
    //url of server application
    const API_SERVER_TEST_URL = 'http://127.0.0.1:8000';

    /**
     * Requests api application via http-client
     * @param $methodSegment - request uri for api server (for example /transaction/tesmail@test.com/1000)
     * @return array - decoded api response or error structure if something went wrong
     */
    private function requestApi($methodSegment)
    {
        $response = null;
        try {
            $httpClient = new HttpClient($this::API_SERVER_TEST_URL . $methodSegment) ;
            $httpClient->setPost();
            $response = $httpClient->execute();
        }
        catch (\Exception $ex) {
            $response = ['error' => $ex->getMessage()];
        }
        return $response;
    }

    /**
     * Auth stub method
     * @return bool
     */
    private function checkAuthToken()
    {
        return isset($_COOKIE["authToken"]) && AuthStub::checkAuthToken($_COOKIE["authToken"]);
    }

    /**
     * Renders logging page (main and only one page of client application)
     */
    private function renderBasePage()
    {
        setcookie("authToken", AuthStub::generateAuthToken(), time()+3600);
        print file_get_contents('log.html');
    }

    /**
     * Processes post requests and call requestApi (if all OK)
     * Renders result structure in JSON
     */
    private function processPost()
    {
        $errors = [];
        $result = null;

        if ($this->checkAuthToken()) {
            $params = Request::getParams();

            //api operation name ('transaction') should be passed in 'method' parameter
            if (isset($params['method']) &&
                $apiCheckerData = ApiGatewayChecker::getMethodSignature($params['method'])
            ) {
                //first part of api request uri
                $apiUrl = '/' . $params['method'];

                //reading api signature from gateway storage and extracting arguments from http-request
                //sea ApiGatewayChecker for more information
                foreach ($apiCheckerData as $paramName => $checker) {
                    if (isset($params[$paramName]) && $checker($params[$paramName])) {
                        $apiUrl .= ('/' . urlencode($params[$paramName]));
                    } else {
                        $errors[] = 'Parameter "' . $paramName . '" is undefined or invalid';
                    }
                }
                if (!$errors) {
                    //all arguments successfully extracted - try to send the request
                    $result = $this->requestApi($apiUrl);
                }
            } else {
                //query param 'method' was not found or unknown for API gateway
                $errors[] = 'API Operation is not set or unsupported';
            }
        }
        else {
            //auth error
            $errors[] = 'Auth error';
        }

        //render json-encoded answer:
        //object with list of errors (if client`s error occurred),
        //or response from http-client
        print count($errors) > 0 ? json_encode(['error' => implode(',', $errors)]) : json_encode($result);
    }

    /**
     * Handles all http-requests,
     * there are only two actions now: process post http-request or simply display logging page
     */
    public function run()
    {
        // if post-request occurred and client is authorised - parses request, sends it to the API server,
        // and render result or error message
        if (Request::isPost()) {
            $this->processPost();
        } else {
            //otherwise simply shows logging page
            $this->renderBasePage();
        }
    }
}