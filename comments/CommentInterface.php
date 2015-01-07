<?php

namespace rotmistrz\comments;

/********************************************************************
 *
 *  File's name: CommentInterface.php
 *  Script's author: Filip Markiewicz (www.filipmarkiewicz.pl)
 *
 *  Created: 22.11.2014r.
 *  Last modificated: 03.01.2015r.
 *
 ********************************************************************/

interface CommentInterface {
	public function edit();
	public function delete();
	public function getForm();
	static public function create($id);
}