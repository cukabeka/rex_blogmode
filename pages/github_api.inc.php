<?php
/**
* Addon_Template
*
* @author http://rexdev.de
* @link   https://github.com/jdlx/addon_template
*
* @package redaxo4.3
* @version 0.2.1
*/

global $REX;

// GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$mypage  = rex_request('page',    'string');
$subpage = rex_request('subpage', 'string');
$chapter = rex_request('chapter', 'string');
$func    = rex_request('func',    'string');

$myroot = $REX['INCLUDE_PATH'].'/addons/'.$mypage;

// INCLUDES
////////////////////////////////////////////////////////////////////////////////
require_once($myroot.'/classes/class.rex_socket.inc.php');
require_once($myroot.'/classes/class.github_connect.inc.php');


// CONNECT GITHUB API
////////////////////////////////////////////////////////////////////////////////
$gc = new a720_github_connect('jdlx','addon_template');
echo $gc->getList(rex_request('chapter', 'string'));
