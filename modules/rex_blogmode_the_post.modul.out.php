<?php
/**
 * Addon_Template - BLOGMODE LIST MODUL OUT
 * ### UID:rex_blogmode_liste ###
 *
 * @package redaxo4.3
 * @version Addon 0.2.1
 * @version Modul 1.0
 */

//MODULAUSGABE
////////////////////////////////////////////////////////////////////////////////


$cat = OOCategory::getCategoryById($this->getValue("category_id"));
$article = $cat->getArticles();

if (is_array($article)) 
{
  foreach ($article as $var) 
  {
    $articleId = $var->getId();
    $articleName = $var->getName();
    $articleDescription = $var->getDescription();
    if (!$var->isStartpage()) 
    {
      echo '<a href="'.rex_getUrl($articleId).'" class="faq">'.$articleName.'</a><br />';
    }
  }
}

?>