<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: CommentsPrinter.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 11.11.2014r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/
 
class CommentsPrinter implements CommentInterface
{
    protected $comments = array();
    
    /**
     *
     * @param Comment[] $comments
     *
     * @throws CommentException If $comments isn't an array.
     *
     */
    public function __construct($comments = array())
    {
        if (!is_array($comments)) {
            throw new CommentException('@param $comments musi być tablicą.');        
        }
    
        foreach ($comments as $Comment) {
            $this->addComment($Comment);    
        }
    }
    
    /**
     *
     * Adds the another comment to the object.
     *
     * @param Comment $Comment
     *
     */
    public function addComment(Comment $Comment)
    {
        $this->comments[] = $Comment;    
    }
    
    /**
     *
     * @return integer The quantity of elements (comments).
     *
     */
    public function getQuantity()
    {
        return count($this->comments);    
    }

    /**
     *
     * Modifies all the comments in the object, using their own methods to do it.
     *
     * @return integer The quantity of modified comments.
     *
     */
    public function edit()
    {
        $count = 0;
        
        foreach ($this->comments as $Comment) {
            $count += $Comment->edit();
        }
    
        return $count;
    }
    
    /**
     *
     * Deletes all the comments in the object.
     *
     * @return integer The quantity of deleted comments.
     *
     */
    public function delete()
    {
        $ids = array();
        
        foreach ($this->comments as $Comment) {
            $ids[] = $Comment->getId();
        }    
    
    
        $pdo = DataBase::getInstance();
        $fpdo = new \FluentPDO($pdo);
        
        $query = $fpdo->deleteFrom(COMMENTS_TABLE)->where('id', $ids);       
        
        return $query->execute();
    }

    /**
     *
     * Creates a form with all the comments. It can be used to edit them.
     *
     * @return string The string with HTML code of the form.
     *
     */
    public function getForm()
    {
        $Current = Link::create();
        $comments = array();
        
        foreach ($this->comments as $Comment) {
            if ($Comment instanceof AdminComment) {
                $admin_id = $Comment->getUser()->getId();
                $email = false;
                $www = false;
            } else {
                $admin_id = false;
                $email = $Comment->getEmail();
                $www = $Comment->getWww();   
            }
       
            $comments[] = array(
                               'id' =>       $Comment->getId(),
                               'group' =>    $Comment->getGroup(),
                               'admin_id' => $admin_id,
                               'nick' =>     $Comment->getNick(),
                               'email' =>    $email,
                               'webpage' =>  $www,
                               'content' =>  $Comment->getContent(),
                               );
        }        
        
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TEMPLATES_DIR));
        
        $form = $twig->render(
                            'comments_form.twig.html',
                            array(
                            'path' =>        $Current->get(),
                            'return_link' => $_SESSION['return'],
                            'comments' =>    $comments
                            ));
        
        return $form;
    }
    
    /**
     *
     * Prepares the list of comments.
     *
     * @param boolean $u If the admin is loged in - true, in the other case - false.
     *
     * @return string The HTML code of the list, which presents the comments.
     *
    **/
    public function get($u = false)
    {
        $comments = array();        
        
        foreach ($this->comments as $Comment) {
            $Comment->setAdminsFlag($u);            
            
            if ($Comment instanceof AdminComment) {
                $class = 'rt_admin_comment';
                $email = false;
                $www = false;
                $admin_id = $Comment->getUser()->getId();
            } else {
                $class = false;
                $email = $Comment->getEmail();
                $www = $Comment->getWww();
                $admin_id = false;
            }
            
            $comments[] = array(
                        'id' =>           $Comment->getId(),
                        'admin_id' =>     $admin_id,
                        'admin_status' => $Comment->isAdminLoged(),
                        'edit_path' =>    $Comment->getEditPath(),
                        'delete_path' =>  $Comment->getDeletePath(),
                        'class' =>        $class,
                        'nick' =>         $Comment->getNick(),
                        'email' =>        $email,
                        'webpage' =>      $www,
                        'date' =>         $Comment->getDate()->format('d f Y\r., H:i'),
                        'content' =>      $Comment->getContent()
                        );
        }   
        
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TEMPLATES_DIR));
        $list = $twig->render(
                            'comments_list.twig.html',
                            array(
                            'comments' => $comments
                            ));        
        
        return $list;
    }
    
    /**
     *
     * @return string A form in the shape of a table. It is possible to choose the comments to edit or delete them.
     *
     */
    public function getAsTable()
    {
        $comments = array();
        
        foreach ($this->comments as $Comment) {
            $admin_id = ($Comment instanceof AdminComment) ? $Comment->getUser()->getId() : false;
            
            $date = $Comment->getDate()->format('d.m.Y\r., H:i');            
            
            $comments[] = array(
                                'id' =>       $Comment->getId(),
                                'admin_id' => $admin_id,
                                'group' =>    $Comment->getGroup(),
                                'nick' =>     $Comment->getNick(),
                                'date' =>     $date,
                                'content' =>  $Comment->getContent()
                                );
        }

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TEMPLATES_DIR));
        
        $table = $twig->render(
                            'comments_table_form.twig.html',
                            array(
                            'path' => PANEL_FILE,
                            'comments' => $comments
                            ));        

        return $table;
    }
    
    /**
     *
     * Loads the comments with identifiers given in the argument.
     *
     * @param integer[] $id The array with identifiers of comments to load.
     *
     * @return CommentsPrinter Itself own object with these comments.
     *
     */
    static public function create($id)
    {
        $Factory = new CommentsFactory();
        $Factory->setIds($id);
        $Factory->setOrder(false);
        
        return $Factory->get();
    }
    
    /**
     *
     * Loads the comments belonging to this group.
     *
     * @param string $group The name of the group of comments to load.
     *
     * @return CommentsPrinter Itself own object with these comments.
     *
     */
    static public function createByGroup($group)
    {
        $Factory = new CommentsFactory();
        $Factory->setGroup($group);
        $Factory->setOrder(false);
        
        return $Factory->get();
    }
}
