<?php
/**
* Addon_REX_Blogmode
*
* @author http://cukabeka.de
* @link   https://github.com/cukabeka/rex_blogmode
*
* @package redaxo4.3
* @version 0.2.1
*/

// ADDON PARAMETER AUS URL HOLEN
////////////////////////////////////////////////////////////////////////////////
$mypage    = rex_request('page'   , 'string');

// ADDON SETTINGS AUS $REX IN EIGENE VAR REDUZIEREN
////////////////////////////////////////////////////////////////////////////////
if ($mypage = "rex_blogmode") $myREX = $REX['ADDON'][$mypage];


// INCLUDE PARSER FUNCTION
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('get_id_for_post'))
{
  function get_id_for_post($params,$return = FALSE)
  {
  
  global $REX;
  global $myREX;
  
  $mypage   = rex_request('page' , 'string');
  $myREX 	= $REX['ADDON'][$mypage];
  
  $myREX['temp']['article_id'] = $params['id'];
  

  }
}

if (!function_exists('add_slice_for_post'))
{
  function add_slice_for_post($params,$return = FALSE)
  {
  	//return (int) $params['id'];
  }
}


if (!function_exists('putArticleOnline')){
function putArticleOnline($params) {
 global $REX;

// vorerst direkt in addpost.inc

 //return $message;
 }
}