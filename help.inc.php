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

// ADDON IDENTIFIER AUS ORDNERNAMEN ABLEITEN
////////////////////////////////////////////////////////////////////////////////
$mypage = explode('/redaxo/include/addons/',str_replace(DIRECTORY_SEPARATOR, '/' ,__FILE__));
$mypage = explode('/',$mypage[1]);
$mypage = $mypage[0];
$myroot = $REX['INCLUDE_PATH'].'/addons/'.$mypage.'/';

// LOCAL INCLUDES
////////////////////////////////////////////////////////////////////////////////
require_once $myroot.'functions/function.a720_incparse.inc.php';

// HELP CONTENT
////////////////////////////////////////////////////////////////////////////////
$help_includes = array
(
  'Hilfe'     => array('README.textile'    ,'textile'),
  'Changelog' => array('_changelog.textile' ,'textile')
);

// MAIN
////////////////////////////////////////////////////////////////////////////////
foreach($help_includes as $k => $v)
{
  if(file_exists($myroot.$v[0]))
  {
    echo '
    <div class="rex-addon-output" style="overflow:auto">
      <h2 class="rex-hl2" style="font-size:1em">'.$k.' <span style="color: gray; font-style: normal; font-weight: normal;">( '.$v[0].' )</span></h2>
      <div class="rex-addon-content">
        <div class="'.$mypage.'">
          '.a720_incparse($myroot,$v[0],$v[1],true).'
        </div>
      </div>
    </div>';
  }
}
