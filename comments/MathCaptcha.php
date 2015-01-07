<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: MathCaptcha.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 03.10.2013r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

class MathCaptcha
{
    static private $numerals = array(
                                1 => 'jeden',
                                2 => 'dwa',
                                3 => 'trzy',
                                4 => 'cztery',
                                5 => 'pięć',
                                6 => 'sześć',
                                7 => 'siedem',
                                8 => 'osiem',
                                9 => 'dziewięć'
                                );

    private $word1;
    private $word2;
    private $result;

    public function __construct()
    {
        $numeral1 = rand(1, 9);
        $numeral2 = rand(1, 9);

        $this->word1 = self::$numerals[$numeral1];
        $this->word2 = self::$numerals[$numeral2];

        $this->result = $numeral1 + $numeral2;
    }

    /**
     *
     * @return string The operation, which has to be calculated by the user.
     *
     */
    public function getOperation()
    {
        return $this->word1.' + '.$this->word2.' =';
    }

    /**
     *
     * @return integer The correct result of the operation.
     *
     */
    public function getResult()
    {
        return $this->result;
    }
}
