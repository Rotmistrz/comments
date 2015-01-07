<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: Comment.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 09.11.2014r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

abstract class Comment implements CommentInterface
{
    protected $id;
    protected $group;
    protected $nick;
    protected $content;
    protected $date;
    protected $admins_flag;
    
    /**
     *
     * @param integer $id The identifier of the comment.
     * @param string $group The name of comments' group, which this comment belongs to.
     * @param string $content The content of the comment.
     * @param DateTime $date The date of comment's adding.
     *
     */
    public function __construct($id, $group, $content, \DateTime $date)
    {
        if (!is_numeric($id) && !empty($id)) {
            throw new CommentException('@param $id musi być liczbą, jeśli nie jest pusty.');
        }
        if (!preg_match('/^[a-zA-Z]+[a-zA-Z0-9_\-]*$/', $group)) {
            throw new CommentException('@param $group może zawierać tylko znaki alfabetu łacińskiego a-z &ndash; i od nich musi się zaczynać &ndash; liczby 0-9, podkreślenie _ i myślnik -.');
        }
            
        $this->id =          $id;
        $this->group =       $group;
        $this->content =     $content;
        $this->date =        $date;
        $this->admins_flag = false;
    }    
    
    abstract public function add(); 
    abstract public function get();
    abstract public function valid();
    
    public function __toString()
    {
        return $this->get();   
    }    
    
    /**
     *
     * @return integer The identifier of the comment.
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string The group, which the comment is assigned to.
     *
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     *
     * @return string The user's nickname.
     *
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     *
     * Sets a value of the nick.
     *
     * @param string $nick Nickname of the commenting person.
     *
     */
    public function setNick($nick)
    {
        $this->nick = $nick;    
    }

    /**
     *
     * @return string A content of the comment.
     *
     */
    public function getContent()
    {
        return $this->content;    
    }
    
    /**
     *
     * Sets a value of the content.
     *
     * @param string $content The comment's content to set.
     *
     */
    public function setContent($content)
    {
        $this->content = $content;    
    }    
    
    /**
     *
     * @return Date An object representing the data of the comment.
     *
     */
    public function getDate()
    {
        return $this->date;    
    }

    /**
     *
     * Sets a flag, which represents, whether the admin is loged in.
     *
     * @param boolean $flag True when the admin is loged on, false in the other case.
     */
    public function setAdminsFlag($flag)
    {
        if (!is_bool($flag)) {
            throw new CommentException('@param $flag musi być wartością logiczną.');
        }
    
        $this->admins_flag = $flag;    
    }
    
    /**
     *
     * @return boolean True, when admin is loged in, false in the other case.
     *
    **/
    public function isAdminLoged()
    {
        return $this->admins_flag;    
    }

    /**
     *
     * @return string The path to the file, where a comment will be added.
     *
     */
    public function getAddPath()
    {
        return PANEL_FILE."?action=add";    
    }
    
    /**
     *
     * @return string The path to the file, where a comment will be modified.
     *
     */
    public function getEditPath()
    {
        return PANEL_FILE."?action=edit&amp;id=".$this->id;
    }
    
    /**
     *
     * @return string The path to the file, where a comment will be deleted.
     *
     */
    public function getDeletePath()
    {
        return PANEL_FILE."?action=delete&amp;id=".$this->id;
    }

    /**
     *
     * Deletes the comment.
     *
     * @return boolean True or false.
     *
     */
    public function delete()
    {   
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
        $query = $fpdo->deleteFrom(COMMENTS_TABLE)->where('id', $this->id);
        
        if ($query->execute()) {
            return true;    
        } else {
            return false;    
        }
    }

    /**
     *
     * Takes data from the database and creates a self-object, using the id given as a parameter.
     *
     * @param integer $id The id of comment, which is wanted to create.
     *
     * @return Comment|false The Comment object or false.
     *
     */
    static public function create($id)
    {
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
        
        $query = $fpdo->from(COMMENTS_TABLE)->where('id', $id)->leftJoin(ADMINS_TABLE.' ON '.COMMENTS_TABLE.'.admin_id = '.ADMINS_TABLE.'.admin_id')->select(ADMINS_TABLE.'.admin_id')->select(ADMINS_TABLE.'.login')->select(ADMINS_TABLE.'.last_visit')->limit(1);        

        if ($result = $query->fetch()) {
            if (!empty($result['login'])) {
                $U = new User($result['admin_id'], $result['login']);
                
                return new AdminComment($result['id'], $result['group_name'], $U, $result['content'], Date::createFromFormat('Y-m-d H:i:s', $result['date']));
            } else {
                return new PlainComment($result['id'], $result['group_name'], $result['nick'], $result['email'], $result['www'], $result['content'], Date::createFromFormat('Y-m-d H:i:s', $result['date']));
            }        
        } else {
            return false;
        }
    }
}
