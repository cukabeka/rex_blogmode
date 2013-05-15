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

// ERROR_REPORTING
////////////////////////////////////////////////////////////////////////////////
/*ini_set('error_reporting', 'E_ALL');
ini_set('display_errors', 'On');*/


// ADDON IDENTIFIER AUS ORDNERNAMEN ABLEITEN
////////////////////////////////////////////////////////////////////////////////
$mypage = explode('/redaxo/include/addons/',str_replace(DIRECTORY_SEPARATOR, '/' ,__FILE__));
$mypage = explode('/',$mypage[1]);
$mypage = $mypage[0];
$myroot = $REX['INCLUDE_PATH'].'/addons/'.$mypage.'/';


// ADDON REX COMMONS
////////////////////////////////////////////////////////////////////////////////
$REX['ADDON']['rxid'][$mypage] = 'blogmode';
$REX['ADDON']['page'][$mypage] = $mypage;
$REX['ADDON']['name'][$mypage] = $mypage;
$Revision = '';
$REX['ADDON'][$mypage]['VERSION'] = array
(
'VERSION'      => 0,
'MINORVERSION' => 2,
'SUBVERSION'   => 1,
);
$REX['ADDON']['version'][$mypage]     = implode('.', $REX['ADDON'][$mypage]['VERSION']);
$REX['ADDON']['author'][$mypage]      = 'cukabeka';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['ADDON']['perm'][$mypage]        = $mypage.'[]';
$REX['PERM'][]                        = $mypage.'[]';


// STATIC ADDON SETTINGS
////////////////////////////////////////////////////////////////////////////////
$REX['ADDON'][$mypage]['rex_list_pagination'] = 20;
$REX['ADDON'][$mypage]['params_cast'] = array (
  'page'        => 'unset',
  'subpage'     => 'unset',
  'minorpage'   => 'unset',
  'func'        => 'unset',
  'submit'      => 'unset',
  'sendit'      => 'unset',
  'PHPSESSID'   => 'unset',
  );

// DYNAMISCHE SETTINGS
////////////////////////////////////////////////////////////////////////////////
// --- DYN
$REX["ADDON"]["rex_blogmode"]["settings"] = NULL;
// --- /DYN


// AUTO INCLUDE FUNCTIONS & CLASSES
////////////////////////////////////////////////////////////////////////////////
if ($REX['REDAXO'])
{
  $pattern = $myroot.'functions/function.*.inc.php';
  $include_files = glob($pattern);
  if(is_array($include_files) && count($include_files) > 0){
     foreach ($include_files as $include)
     {
       require_once $include;
     }
  }

  $pattern = $myroot.'classes/class.*.inc.php';
  $include_files = glob($pattern);

  if(is_array($include_files) && count($include_files) > 0){
     foreach ($include_files as $include)
     {
       require_once $include;
     }
  }
}

// SUBPAGES
//////////////////////////////////////////////////////////////////////////////
$REX['ADDON'][$mypage]['SUBPAGES'] = array (
  //     subpage    ,label                         ,perm   ,params               ,attributes
  array (''         ,'Einstellungen'               ,''     ,''                   ,''),
  array ('database' ,'Datenbank'                   ,''     ,''                   ,''),
  array ('addpost'  ,'Add Post'                    ,''     ,''                   ,''),
  array ('modul'    ,'Modul'                       ,''     ,''                   ,''),
  array ('includes' ,'Include Beispiele'           ,''     ,''                   ,''),
  array ('connector','Connector (faceless subpage)',''     ,array('faceless'=>1) ,'' /*array('class'=>'blafasel') can't di: rex_title bug*/),
  array ('help'     ,'Hilfe'                       ,''     ,''                   ,''),
);
