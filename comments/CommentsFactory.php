<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: CommentsFactory.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 23.11.2014r.
 *  Last modificated: 01.01.2015r.
 *
 ********************************************************************/

class CommentsFactory
{
    protected $query;
    protected $ids;
    protected $group;
    protected $limit;
    protected $offset;
    protected $order;
    
    public function __construct()
    {
        $pdo = DataBase::getInstance();
        $fpdo = new \FLuentPDO($pdo);    
        
        $this->query = $fpdo->from(COMMENTS_TABLE)->select(ADMINS_TABLE.'.admin_id')->select(ADMINS_TABLE.'.login')->select(ADMINS_TABLE.'.last_visit')->leftJoin(ADMINS_TABLE.' ON '.COMMENTS_TABLE.'.admin_id = '.ADMINS_TABLE.'.admin_id');
        $this->order = true;
    }
    
    /**
     *
     * Sets identifiers of comments, which have to be loaded.
     *
     * @param integer[] $ids An array of numbers.
     *
     * @throws CommentException
     *
     */
    public function setIds($ids)
    {
        if(!is_array($ids) || !IsIntegerArray($ids)) {
            throw new CommentException('@param $ids musi być tablicą liczb całkowitych!');
        }
    
        $this->ids = $ids;
    }
    
    /**
     *
     * @return integer[] The array with numbers.
     *
     */
    public function getIds()
    {
        return (is_array($this->ids)) ? $this->ids : false;    
    }
    
    /**
     *
     * Sets the name of the group of comments, which they have to be loaded from.
     *
     * @param string $group The name of the comments' group.
     *
     */
    public function setGroup($group)
    {
        $this->group = $group;    
    }
    
    /**
     *
     * @return string The name of the comments' group.
     *
     */
    public function getGroup()
    {
        return (!is_null($this->group)) ? $this->group : false;    
    }

    /**
     *
     * Sets the limit parameter.
     *
     * @param integer $limit Maximum quantity of loading rows.
     *
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    /**
     *
     * @return integer The maximum number of loading rows.
     *
     */
    public function getLimit()
    {
        return (!empty($this->limit)) ? $this->limit : false;
    }
    
    /**
     *
     * Sets the offset parameter.
     *
     * @param integer $offset Quantity of rows to skip.
     *
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;        
    }
    
    /**
     *
     * @return The number of rows to skip.
     *
     */
    public function getOffset()
    {
        return (!empty($this->offset)) ? $this->offset : false;   
    }
    
    /**
     *
     * Sets the type of records' ordering.
     *
     * @param boolean $order True, when it has to be growing, false when improving.
     *
     * @throws CommentException
     *
     */
    public function setOrder($order)
    {
        if (!is_bool($order)) {
            throw new CommentException('@param $order musi być wartością logiczną.');
        }
        
        $this->order = $order;    
    }
    
    /**
     *
     * @return string The type of ordering as a string, ready to put into a query.
     *
     */
    public function getOrder()
    {
        return ($this->order) ? "ASC" : "DESC";    
    }
    
    /**
     *
     * Loads comments, considering set paremeters.
     *
     * @return CommentsPrinter|false
     *
     */
    public function get()
    {      
        if ($group = $this->getGroup()) {
            $this->query->where('group_name', $group);
        }
    
        if ($ids = $this->getIds()) {
            $this->query->where('id', $ids); 
        }
    
        $this->query->orderBy('id '.$this->getOrder());    
    
        if ($limit = $this->getLimit()) {
            $this->query->limit($limit);    
        }
   
        if ($offset = $this->getOffset()) {
            $this->query->offset($offset);   
        }      
        
        if ($this->query->count() > 0) {
            $comments = array();            
            
            foreach ($this->query as $comment) {
                if ($comment['admin_id'] !== null) {
                    $User = new User($comment['admin_id'], $comment['login'], Date::createFromFormat('Y-m-d H:i:s', $comment['last_visit']));
                    $comments[] = new AdminComment($comment['id'], $comment['group_name'], $User, $comment['content'], Date::createFromFormat('Y-m-d H:i:s', $comment['date']));    
                } else {
                    $comments[] = new PlainComment($comment['id'], $comment['group_name'], $comment['nick'], $comment['email'], $comment['www'], $comment['content'], Date::createFromFormat('Y-m-d H:i:s', $comment['date']));
                }
            }
        
            return new CommentsPrinter($comments);
        } else {
            return false;    
        }
    }
    
    /**
     *
     * Select the correct class to load comment(s).
     *
     * @param integer $id Comment's identifier or array of comments' identifiers to load.
     *
     * @return Comment|CommentsPrinter
     *
     */
    public static function create($id)
    {
        return (!isIntegerArray($id)) ? Comment::create($id) : CommentsPrinter::create($id);
    }
} 
