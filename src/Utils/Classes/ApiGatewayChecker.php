<?php

namespace Utils\Classes;

/**
 * This class knows about names and signatures of all API operations,
 * and has validator for each operation`s argument
 * Class ApiGatewayChecker
 * @package Utils\Classes
 */
class ApiGatewayChecker
{
    /**
     * Returns all known API methods with signatures
     * @return array
     */
    public static function getApiMethods()
    {
        return [
            //keys here is API operations` names
            'transaction' => [
                //list of params of method 'transaction' with checking functions
                'email'  => function($param) {return filter_var($param, FILTER_VALIDATE_EMAIL) !== false;},
                'amount' => function($param) {return filter_var($param, FILTER_VALIDATE_FLOAT) !== false;},
            ],
            /* another operations of our API */
        ];
    }

    /**
     * Returns array with method`s signature by method`s name, of false if method is not found
     * @param sting $methodName - name or the API method to search (case insensitive)
     * @return array|bool
     */
    public static function getMethodSignature($methodName)
    {
        $methodNameLowercased = mb_strtolower($methodName);
        $apiMethods = self::getApiMethods();
        return isset($apiMethods[$methodNameLowercased]) ? $apiMethods[$methodNameLowercased] : false;
    }
}