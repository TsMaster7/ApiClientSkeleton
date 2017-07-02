<?php

namespace Utils\Classes;

/**
 * Simple wrapper for operating with HTTP-request
 * Class Request
 * @package Utils\Classes
 */
class Request
{
    /**
     * Returns array of http-request parameters
     * @return array
     */
    public static function getParams()
    {
        //let`s assume that this proxy-application will work with GET and POST only,
        //at least we don`t need more options for testing
        return self::isPost() ? $_POST : $_GET;
    }

    /**
     * Checks if http-request method is POST
     * @return bool
     */
    public static function isPost()
    {
        return 'POST' === $_SERVER['REQUEST_METHOD'] ? true : false;
    }

    /**
     * Gets cookies
     * @return array
     */
    public static function getCookies()
    {
        return $_COOKIE;
    }
}