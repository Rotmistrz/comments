<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: User.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 10.11.2014r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

class User
{
    protected $id;
    protected $login;
    protected $lastVisit;
    
    /**
     *
     * @param integer $id The identifier of user.
     * @param string $login The unique login of the user.
     * @param DateTime|null $lastVisit The object representing the date, when the user was in the administrative panel last time.
     *
     */
    public function __construct($id, $login, \DateTime $lastVisit = null)
    {
        $this->id = $id;
        $this->login = $login;
        $this->lastVisit = $lastVisit;
    }

    /**
     *
     *  @return integer The user's id.
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string The user's login.
     *
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     *
     * @return Date The Date object with date, when user was in administrative panel recently.
     *
     */
    public function getLastVisit()
    {
        return $this->lastVisit;
    }

    /**
     *
     * Updates the date of the last visit in the admin's panel.
     *
     * @return boolean True or false.
     *
     */
    public function updateLastVisit()
    {
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
        $query = $fpdo->update(ADMINS_TABLE)->set(array('last_visit' => date('Y-m-d H:i:s')))->where('admin_id', $this->id);

        if ($query->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Logs the user out.
     *
     * @return boolean True or false.
     *
     */
    public function logout()
    {
        $old = $_SESSION['user'];
        unset($_SESSION['user']);
        session_destroy();

        if (!empty($old) && !isset($_SESSION['user'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Compares login and password given in arguments with data in the database.
     *
     * @param string $login
     * @param string $password
     *
     * @return User|false The User object or false.
     *
     */
    public static function login($login, $password)
    {
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
        $query = $fpdo->from(ADMINS_TABLE)->where('login', $login)->limit(1);

        if ($result = $query->fetch()) {
            $real_password = $result['password'];
            
            if ($real_password === crypt($password, $real_password)) {
                $_SESSION['user']['id'] = $result['admin_id'];
                $_SESSION['user']['login'] = $result['login'];
                $_SESSION['user']['last_visit'] = $result['last_visit'];
        
                return new User($result['admin_id'], $result['login'], Date::createFromFormat('Y-m-d H:i:s', $result['last_visit']));
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     *
     * @return User The User object of admin, who is loged in currently.
     *
     */
    public static function getInstance()
    {
        if (isset($_SESSION['user'])) {
            return new self($_SESSION['user']['id'], $_SESSION['user']['login'], Date::createFromFormat('Y-m-d H:i:s', $_SESSION['user']['last_visit']));
        } else {
            return false;
        }
    }
}
