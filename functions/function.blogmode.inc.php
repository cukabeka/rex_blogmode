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

// INCLUDE PARSER FUNCTION
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('add_slice_for_post'))
{
  function add_slice_for_post($params,$return = FALSE)
  {
  	krumo($params);
  	krumo($return);
  }
}

if (!function_exists('putArticleOnline')){
function putArticleOnline($params) {
 global $REX;

 $message = $params['subject'];
 $articleID = (int) $params['id'];
 $clangID = (int) $params['clang'];
 $article = $params['article'];

 $article->setValue('status', 1); // Hier wird der Status geändert.
 $article->setTable($REX['TABLE_PREFIX'].'article');
 $article->setWhere('id = '.$articleID.' AND clang = '.$clangID);
 $article->update();

 return $message;
 }
}