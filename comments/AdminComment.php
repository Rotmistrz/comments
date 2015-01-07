<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: AdminComment.php
 *  @author Filip Markiewicz <www.filipmarkiewicz.pl>
 *
 *  Created: 09.11.2014r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

class AdminComment extends Comment
{
    protected $user;
    
    /**
     *
     * @param integer $id The identifier of the comment.
     * @param string $group The name of comments' group, which this comment belongs to.
     * @param User $user The User object with informations about the admin.
     * @param string $content The content of the comment.
     * @param DateTime $date The date of comment's adding.
     *
     *
     */
    public function __construct($id, $group, User $user, $content, \DateTime $date)
    {
        parent::__construct($id, $group, $content, $date);        
        
        $this->user = $user;
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
                    'comment.twig.html',
                    array(
                    'id' =>           $this->id,
                    'admin_status' => $this->isAdminLoged(),
                    'edit_path' =>    $this->getEditPath(),
                    'delete_path' =>  $this->getDeletePath(),
                    'class' =>        'rt_admin_comment',
                    'nick' =>         $this->getNick(),
                    'date' =>         $this->date->format('d f Y\r., H:i'),
                    'content' =>      $this->content
                    ));
                    
        return $body;
    }
    
    public function valid() {
        return true;
    }  
    
    /**
     *
     * @return string User's login.
     *
     */
    public function getNick()
    {
        return $this->user->getLogin();    
    }    
    
    /**
     *
     * @return User An object representing the admin.
     *
     */
    public function getUser()
    {
        return $this->user;    
    }    
    
    /**
     *
     * Adds this comment to the database.
     *
     * @return boolean True or false.
     *
     */
    public function add()
    {
        $values = array('id' => null, 'group_name' => $this->group, 'nick' => null, 'email' => null, 'www' => null, 'content' => $this->content, 'date' => date('Y-m-d H:i:s'), 'admin_id' => $this->user->getId());
        
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
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
     * @return True or false.
     *
     */
    public function edit()
    {   
        $values = array('content' => $this->content);
        
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
        
        $query = $fpdo->update(COMMENTS_TABLE)->set($values)->where('id', $this->id);
        
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
     * @return The HTML code of the form.
     *
     */
    public function getForm()
    {
        $editing = (!empty($this->id) && $this->id > 0) ? true : false;
        
        $path = ($editing) ? $this->getEditPath() : $this->getAddPath();        
        
        \Twig_Autoloader::register();        
        
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TEMPLATES_DIR));
        
        $body = $twig->render(
                            'admin_comment_form.twig.html',
                            array(
                            'path' =>        $path,
                            'editing' =>     $editing,
                            'id' =>          $this->id,
                            'group' =>       $this->group,
                            'admin_id' =>    $this->user->getId(),
                            'nick' =>        $this->getNick(),
                            'content' =>     $this->content,
                            'return_link' => $_SESSION['return']
                            ));
        
        return $body;
    }
}
