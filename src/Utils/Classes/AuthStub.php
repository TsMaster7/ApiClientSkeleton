<?php

namespace Utils\Classes;

/**
 * Simulator of simple cookie-based auth tool
 * Class AuthStub
 * @package Utils\Classes
 */
class AuthStub
{
    /**
     * dummy
     * @param $authToken
     * @return bool
     */
    private static function saveAuthToken($authToken)
    {
        return true;
    }

    public static function generateAuthToken()
    {
        $token = substr(sha1(time() . rand(0, 10000)), 0, 24);
        self::saveAuthToken($token);
        return $token;
    }

    /**
     * We trust all clients now
     * @param $authToken
     * @return bool
     */
    public static function checkAuthToken($authToken)
    {
        return true;
    }
    
    

}