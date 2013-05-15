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

// GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$mypage  = rex_request('page',    'string');
$subpage = rex_request('subpage', 'string');
$chapter = rex_request('chapter', 'string');
$func    = rex_request('func',    'string');

// SUBNAVIGATION ITEMS
////////////////////////////////////////////////////////////////////////////////
$chapterpages = array (
''                => array('<em>TEXTILE include</em>'      ,'pages/_textile_include_example.textile'      ,'textile'),
'text_demo'       => array('<em>TXT include</em>'          ,'pages/_txt_include_example.txt'              ,'txt'),
'php_demo'        => array('<em>PHP include</em>'          ,'pages/_php_include_example.php'              ,'php'),
'iframelink_demo' => array('<em>Link (iframe)</em>'        ,'http://rexdev.de/addons/addon-template.html' ,'iframe'),
'newwinlink_demo' => array('<em>Link (neues Fenster)</em>' ,'http://rexdev.de/addons/addon-template.html' ,'jsopenwin')
);

// BUILD CHAPTER NAVIGATION
////////////////////////////////////////////////////////////////////////////////
$chapternav = '';
foreach ($chapterpages as $chapterparam => $chapterprops)
{
  if ($chapter != $chapterparam) {
    $chapternav .= ' | <a href="?page='.$mypage.'&subpage='.$subpage.'&chapter='.$chapterparam.'" class="chapter '.$chapterparam.' '.$chapterprops[2].'">'.$chapterprops[0].'</a>';
  } else {
    $chapternav .= ' | <span class="chapter '.$chapterparam.' '.$chapterprops[2].'">'.$chapterprops[0].'</span>';
  }
}
$chapternav = ltrim($chapternav, " | ");

// BUILD CHAPTER OUTPUT
////////////////////////////////////////////////////////////////////////////////
$addonroot = $REX['INCLUDE_PATH']. '/addons/'.$mypage.'/';
$source    = $chapterpages[$chapter][1];
$parse     = $chapterpages[$chapter][2];

$html = a720_incparse($addonroot,$source,$parse,true);


// OUTPUT
////////////////////////////////////////////////////////////////////////////////
echo '
<div class="rex-addon-output" id="subpage-'.$subpage.'">
  <h2 class="rex-hl2" style="font-size:1em">'.$chapternav.'</h2>
  <div class="rex-addon-content">
    <div class= "addon-template">
    '.$html.'
    </div>
  </div>
</div>';
