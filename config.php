<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: config.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 08.11.2014r.
 *  Last modificated: 07.01.2015r.
 *
 *  You should create similar file with declaration of
 *  each constans data in this namespace rotmistrz\comments.
 *
 ********************************************************************/

// a database's host name
const DB_HOST = 'localhost';

// a database's name
const DB_NAME = 'comments';

// a database's user
const DB_USERNAME = 'root';

// a password of database's user
const DB_PASSWORD = '';

// a name of the table cointaining comments
const COMMENTS_TABLE = 'comments';

// a name of the table containing ip addresses
const IP_TABLE = 'ip_addresses';

// a name of the table containing admins
const ADMINS_TABLE = 'admins';

// a path to directory, where the twig templates are
const TEMPLATES_DIR = 'templates';

// a time limit between adding comments using the same ip address in the same place (seconds)
const TIME_LIMIT = 60;