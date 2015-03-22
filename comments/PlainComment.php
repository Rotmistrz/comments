<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: PlainComment.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 08.11.2014r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

class PlainComment extends Comment
{
    protected $email;
    protected $www;
    protected $ip;
    protected $errors;
    
    /**
     *
     * @param integer $id The identifier of the comment.
     * @param string $group The name of comments' group, which this comment belongs to.
     * @param string $nick The nickname of the commenting person.
     * @param string $email An e-mail address.
     * @param string $www An address of the commenting person's webpage.
     * @param string $content The content of the comment.
     * @param DateTime $date The date of comment's adding.
     *
     */
    public function __construct($id, $group, $nick, $email, $www, $content, \DateTime $date)
    {
        parent::__construct($id, $group, $content, $date);

        $this->nick =   $nick;
        $this->email =  $email;
        $this->www =    $www;
        $this->ip =     $_SERVER['REMOTE_ADDR'];
        $this->errors = array();
    }
    
    /**
     *
     * @return string The HTML code of the comment.
     *
     */
    public function get()
    {
        \Twig_Autoloader::register();
        
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TEMPLATES_DIR));

        $body = $twig->render(
                    'plain_comment.twig.html',
                    array(
                    'id' =>           $this->id,
                    'admin_status' => $this->isAdminLoged(),
                    'edit_path' =>    $this->getEditPath(),
                    'delete_path' =>  $this->getDeletePath(),
                    'nick' =>         $this->nick,
                    'email' =>        $this->email,
                    'webpage' =>      $this->www,
                    'date' =>         $this->date->format('d f Y\r., H:i'),
                    'content' =>      $this->content
                    ));
                    
        return $body;
    }
    
    /**
     *
     * Checks whether the comment's data are correct. If they aren't, it gives an error's array to the $errors field of the object.
     *
     * @return boolean True or false.
     *
     */
    public function valid() {
        $errors = array();        
        
        if (strlen($this->nick) < 1) {
            $errors[] = "Proszę się przedstawić.";
        }
        if (strlen($this->nick) > 40) {
            $errors[] = "Proszę podać nick krótszy niż 40 znaków.";
        }
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Proszę wprowadzić poprawny adres poczty elektronicznej.";
        }
        if (!empty($this->www) && !filter_var($this->www, FILTER_VALIDATE_URL)) {
            $errors[] = "Proszę podać poprawny adres swojej strony internetowej.";
        }    
        if (strlen($this->content) < 5) {
            $errors[] = "Proszę napisać coś treściwszego.";
        }
        if (strlen($this->content) > 65535) {
            $errors[] = "Podziwiam Twój zapał literacki, jednak Twój wpis jest zbyt obszerny. Proszę napisać coś krótszego niż 65 535 znaków. Oto wpisany przed chwilą tekst:<br />".$this->content;
        }
        
        if (count($errors) > 0) {
            $this->errors = $errors;
            
            return false;
        } else {
            return true;   
        }
    }

    /**
     *
     * @return string|false The user's e-mail address or false.
     *
     */
    public function getEmail()
    {
        return (!empty($this->email)) ? $this->email : false;
    }

    /**
     *
     * @return string|false The user's website address or false.
     *
     */
    public function getWww()
    {
        return (!empty($this->www)) ? $this->www : false;
    }

    /**
     *
     * @return string[] An array with errors of uncorrect data in comment.
     *
     */
    public function getErrorsArray() {
        return $this->errors;   
    }

    /**
     *
     * @return string[]|false The identifier in database and the Date object with time when a comment was added to this group using this ip address, or false, if it hasn't ever happened.
     *
     */
    public function checkIp()
    {
        $pdo = DataBase::getInstance();        
        $fpdo = new \FluentPDO($pdo);        
        
        $query = $fpdo->from(IP_TABLE)->where('group_name', $this->group)->where('ip_address', $this->ip)->limit(1);
        
        if($query->count() > 0) {
            $result = $query->fetch();

            return array('ip_id' => $result['ip_id'], 'date' => Date::createFromFormat('Y-m-d H:i:s', $result['date']));
        }
        else {
            return false;
        }
    }

    /**
     *
     * Adds this comment to the database. It bears in mind, when a comment was added using this IP recently.
     *
     * @return boolean True or false.
     *
     * @throws CommentException.
     *
    **/
    public function add()
    {
        $current_time = time();
        $date = date('Y-m-d H:i:s', $current_time);

        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
        
        if ($last = $this->checkIp()) {
            $ip_id = $last['ip_id'];
            $LastDate = $last['date'];        
            $limit = $LastDate->getTimeStamp() + TIME_LIMIT;

            if ($current_time < $limit) {
                $differential = $limit - $current_time;
                $to_wait = ceil($differential / 60);

                throw new CommentException("Proszę poczekać jeszcze ".$to_wait." minut(y), aby dodać kolejny komentarz.");
            } else {
                $ip_query = $fpdo->update(IP_TABLE)->set(array('date' => $date))->where('ip_id', $ip_id);
            }
        } else {
            $values = array('group_name' => $this->group, 'ip_address' => $this->ip, 'date' => $date);            
            
            $ip_query = $fpdo->insertInto(IP_TABLE)->values($values);
        }

        if (!$ip_query->execute()) {
            return false;
        }

        $values = array('id' => null, 'group_name' => $this->group, 'nick' => $this->nick, 'email' => $this->email, 'www' => $this->www, 'content' => $this->content, 'date' => $date, 'admin_id' => null);

        $query = $fpdo->insertInto(COMMENTS_TABLE)->values($values);
        
        if ($query->execute()) {
        	   return true;
        } else {
        	   return false;
        }
    }

    /**
     *
     * Modifies the comment.
     *
     * @return boolean True or false.
     *
    **/
    public function edit()
    {
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);

        $set = array('nick' => $this->nick, 'email' => $this->email, 'www' => $this->www, 'content' => $this->content);
        $query = $fpdo->update(COMMENTS_TABLE)->set($set)->where('id', $this->id);        
        
        if ($query->execute()) {
        	   return true;
        } else {
        	   return false;
        }
    }

    /**
     *
     * Creates a form, compliting it with data of the comment. If the comment has its own id, it puts a path to edit it.
     * In other case, the path leads to adding a new comment.
     *
     * @return string The HTML code of the form.
     *
    **/
    public function getForm()
    {
        $editing = (!empty($this->id) && $this->id > 0) ? true : false;

        $path = ($editing) ? $this->getEditPath() : $this->getAddPath(); 

        $Captcha = new MathCaptcha();

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TEMPLATES_DIR));
        
        $body = $twig->render(
                            'plain_comment_form.twig.html',
                            array(
                            'path' =>        $path,
                            'editing' =>     $editing,
                            'id' =>          $this->id,
                            'group' =>       $this->group,
                            'nick' =>        $this->nick,
                            'email' =>       $this->email,
                            'webpage' =>     $this->www,
                            'content' =>     $this->content,
                            'operation' =>   $Captcha->getOperation(),
                            'result' =>      sha1($Captcha->getResult()),
                            'return_link' => $_SESSION['return']
                            ));

        return $body;
    }
}
