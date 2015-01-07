<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: Link.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 20.11.2012r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

class Link
{
    protected $path;
    protected $parameters = array();

    /**
     *
     * @param string $path The path to file, where the link directs to.
     * @param string[] $parameters An associative array of parameters.
     *
     * @throws Exception When $parameters isn't an array.
     *
     */
    public function __construct($path, $parameters = array())
    {
        if (!is_array($parameters)) {
            throw new \Exception("Argument @parameters must be an array!");
        }

        $this->path =       $path;
        $this->parameters = $parameters;
    }

    /**
     *
     * Adds the parametr (/Get/ variable) to the object.
     *
     * @param string $name Parameter's name (name of the /Get/ variable).
     * @param string $value Parameter's value.
     * 
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     *
     * @param string $name A name of the parameter (/Get/ variable).
     *
     * @return string The value of parameter, whose name was given in the argument.
     *
     */
    public function getParameter($name)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        } else {
            return false;
        }
    }

    /**
     *
     * Deletes the paremeter, whose name was given in the argument, from the object.
     *
     * @param string $name The name of the parameter (/Get/ variable).
     *
     */
    public function deleteParameter($name)
    {
        if (isset($this->parameters[$name])) {
            unset($this->parameters[$name]);
        }
    }

    /**
     *
     * @return string[] The array with all parameters added to the object.
     *
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     *
     * @return string The ready and processed link in fallowing shape: path_to_file?parameter1=value1&amp;parameter2=value2...and so on...
     *
     */
    public function get()
    {
        $link = $this->path;

        if (count($this->parameters) > 0) {
            $array = array();

            foreach ($this->parameters as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $single) {
                        $array[] = $name.'[]='.$single;
                    }
                } else {
                    $array[] = $name.'='.$value;
                }
            }

            $parameters = implode('&amp;', $array);

            $link .= '?'.$parameters;
        }

        return $link;
    }

    /**
     *
     * @return string The rready and processed to modrewrite link in fallowing shape: path_to_file/value1/value2...and so on...
     *
     */
    public function getForModRewrite()
    {
        $path = pathinfo($this->path);

        $link = null;

        if(preg_match('/^[a-zA-Z0-9_\-]+$/', $path['dirname'])) $link .= '/'.$path['dirname'];

        $link .= '/'.$path['filename'];

        if(count($this->parameters)) $link .= '/'.implode('/', $this->parameters);

        return $link;
    }

    /**
     *
     * @return Link The Link object created by current relative address of webpage.
     *
     */
    static public function create()
    {
        $array = $_GET;
        $path = $_SERVER['PHP_SELF'];

        return new self($path, $array);
    }
}
