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

// ADDON IDENTIFIER AUS GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$mypage = rex_request('addonname','string');

// INSTALL CONDITIONS
////////////////////////////////////////////////////////////////////////////////
$requiered_REX = '4.3.1';
$requiered_PHP = 5;
$requiered_addons = array('textile');
$do_install = true;

// CHECK REDAXO VERSION
////////////////////////////////////////////////////////////////////////////////
$this_REX = $REX['VERSION'].'.'.$REX['SUBVERSION'].'.'.$REX['MINORVERSION'] = "1";
if(version_compare($this_REX, $requiered_REX, '<'))
{
	$REX['ADDON']['installmsg'][$mypage] = 'Dieses Addon ben&ouml;tigt Redaxo Version '.$requiered_REX.' oder h&ouml;her.';
	$REX['ADDON']['install'][$mypage] = 0;
	$do_install = false;
}

// CHECK PHP VERSION
////////////////////////////////////////////////////////////////////////////////
if (intval(PHP_VERSION) < $requiered_PHP)
{
	$REX['ADDON']['installmsg'][$mypage] = 'Dieses Addon ben&ouml;tigt mind. PHP '.$requiered_PHP.'!';
	$REX['ADDON']['install'][$mypage] = 0;
	$do_install = false;
}

// CHECK REQUIERED ADDONS
////////////////////////////////////////////////////////////////////////////////
foreach($requiered_addons as $a)
{
  if (!OOAddon::isInstalled($a))
  {
    $REX['ADDON']['installmsg'][$mypage] = '<br />Addon "'.$a.'" ist nicht installiert.  >>> <a href="index.php?page=addon&addonname='.$a.'&install=1">jetzt installieren</a> <<<';
    $do_install = false;
  }
  else
  {
    if (!OOAddon::isAvailable($a))
    {
      $REX['ADDON']['installmsg'][$mypage] = '<br />Addon "'.$a.'" ist nicht aktiviert.  >>> <a href="index.php?page=addon&addonname='.$a.'&activate=1">jetzt aktivieren</a> <<<';
      $do_install = false;
    }

  }
}

// DO INSTALL
////////////////////////////////////////////////////////////////////////////////
if ($do_install)
{
	$REX['ADDON']['install'][$mypage] = 1;
}
