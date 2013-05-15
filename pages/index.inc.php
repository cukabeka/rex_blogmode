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
$mypage   = rex_request('page', 'string');
$subpage  = rex_request('subpage', 'string');
$faceless = rex_request('faceless', 'string');

if($faceless != 1)
{

  // BACKEND CSS
  //////////////////////////////////////////////////////////////////////////////
  $header =
    PHP_EOL.'<!-- '.$mypage.' -->'.
    PHP_EOL.'  <link rel="stylesheet" type="text/css" href="../files/addons/'.$mypage.'/backend.css" media="screen, projection, print" />'.
    PHP_EOL.'<!-- /'.$mypage.' -->'.PHP_EOL;
  $header_func = 'return $params[\'subject\'].\''.$header.'\';';

  rex_register_extension('PAGE_HEADER', create_function('$params',$header_func));


  // REX BACKEND LAYOUT TOP
  //////////////////////////////////////////////////////////////////////////////
  require $REX['INCLUDE_PATH'] . '/layout/top.php';

  // TITLE & SUBPAGE NAVIGATION
  //////////////////////////////////////////////////////////////////////////////
  rex_title($REX['ADDON']['name'][$mypage].' <span class="addonversion">'.$REX['ADDON']['version'][$mypage].'</span>', $REX['ADDON'][$mypage]['SUBPAGES']);

  // INCLUDE REQUESTED SUBPAGE
  //////////////////////////////////////////////////////////////////////////////
  if(!$subpage)
  {
    $subpage = 'settings';  /* DEFAULT SUBPAGE */
  }
  require $REX['INCLUDE_PATH'] . '/addons/'.$mypage.'/pages/'.$subpage.'.inc.php';

  // JS SCRIPT FÜR LINKS IN NEUEN FENSTERN (per <a class="jsopenwin">)
  ////////////////////////////////////////////////////////////////////////////////
  echo '
  <script type="text/javascript">
  // onload
  window.onload = externalLinks;

  // http://www.sitepoint.com/article/standards-compliant-world
  function externalLinks()
  {
   if (!document.getElementsByTagName) return;
   var anchors = document.getElementsByTagName("a");
   for (var i=0; i<anchors.length; i++)
   {
     var anchor = anchors[i];
     if (anchor.getAttribute("href"))
     {
       if (anchor.getAttribute("class") == "jsopenwin")
       {
       anchor.target = "_blank";
       }
     }
   }
  }
  </script>
  ';

  // REX BACKEND LAYOUT BOTTOM
  //////////////////////////////////////////////////////////////////////////////
  require $REX['INCLUDE_PATH'] . '/layout/bottom.php';
}
else
{
  require $REX['INCLUDE_PATH'] . '/addons/'.$mypage.'/pages/'.$subpage.'.inc.php';
}
