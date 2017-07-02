<?php

function __autoload($class)
{
    $parts = explode('\\', $class);
    require realpath(__DIR__ . '/..') . '/' . (implode("/", $parts)) . '.php';
}