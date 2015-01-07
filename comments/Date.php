<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: Date.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 21.12.2012r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/
 
class Date extends \DateTime
{
    static public $polish_months = array(1 => 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia');
    const POLISH_MONTHS_SIGN = 'f';    
    
    /**
     *
     * @param string|null $time A date/time string. Valid formats are explained in Date and Time Formats. 
     *                          Enter null here to obtain the current time when using the $timezone parameter.
     *
     * @param DateTimeZone $timezone  A DateTimeZone object representing the timezone of $time.
     *                                If $timezone is omitted, the current timezone will be used. 
     *
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone);        
    }    
    
    /**
     *
     * Allows use the aditional sign "f" in date format, which represents polish months words-written.
     * Anyway, it works like normal DateTime::format method.
     *
     * @param string $format Date format.
     *
     * @return string Formated date.
     *
     */
    public function format($format)
    {
        if (preg_match("/^.*[f]+.*$/", $format)) {
            $format = str_replace('f', 'F', $format);
            
            $date = parent::format($format);
            
            $english = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');            
            
            return str_replace($english, self::$polish_months, $date);
        }
        
        return parent::format($format);
    }
    
    /**
     *
     * @param string $format The format that the passed in string should be in. See the formatting options below. In most cases, the same letters as for the date() can be used.
     * @param string $time String representing the time.
     *
     * @return DateTime|false A new DateTime instance or false on failure. 
     *
     */
    public static function createFromFormat($format, $time)
    {
        $DateTime = parent::createFromFormat($format, $time);
        
        return new static($DateTime->format('Y-m-d H:i:s'));
    }
}
