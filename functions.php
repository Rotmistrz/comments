<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: functions.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 08.11.2014r.
 *  Last modificated: 07.01.2015r.
 *
 ********************************************************************/

/**
 *
 * Adds 'http://' prefix to the webpage's address, if that doesn't exists.
 *
 * Returns website's address with 'http://' prefix..
 *
**/
function addHttp($www)
{
    $pos = strpos($www, 'http://');

    if ($pos !== 0) {
        $www = 'http://'.$www;
    } 
     
     return $www;
}

/**
 *
 * @param $string String
 *
 * Deletes the <br /> tags from the string.
 *
 * Returns the string without these tags.
 *
**/
function deleteBrs($string)
{
    return str_replace('<br />', '', $string);
}

/**
 *
 * @param $array Array
 *
 * Checks, whether the array contains only numerous values.
 *
 * Returns true or false.
 *
**/
function isIntegerArray($array)
{
    if (!is_array($array)) {
        return false;
    }
    
    foreach ($array as $element) {
        if (!preg_match('/^[0-9]+$/', $element)) {
            return false;
        }
    }

    return true;
}
