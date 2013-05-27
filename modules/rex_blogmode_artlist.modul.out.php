<?php

$cat = OOCategory::getCategoryById($this->getValue("category_id"));
$article = $cat->getArticles(true);

if (is_array($article)) 
{
echo "<ul>";
  foreach ($article as $var) 
  {
    $articleId = $var->getId();
    $articleName = $var->getName();
	$articleDate = strftime("%d.%m.%Y",$var->getCreateDate());
	$articleAuthor = $var->getCreateUser();

	$articleDescription = $var->getDescription();
    if (!$var->isStartpage()) 
    {
echo "<li>";
	  echo ' <a href="'.rex_getUrl($articleId).'" class="faq">'.$articleName.'</a><br />'
		.$articleDate
		.' von '.$articleAuthor;
	  $img = OOArticleSlice::getSlicesForArticleOfType($var->getId(),2);
	  if(is_object($img)) if ($img->getMedia(1)!="") echo '<img src="index.php?rex_img_type=rex_mediapool_preview&rex_img_file='.$img->getMedia(1).'" />';
echo "</li>";
    }
  }
echo "</ul>";
}

?>