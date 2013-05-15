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
$mypage             = rex_request('page', 'string');
$subpage            = rex_request('subpage', 'string');
$func               = rex_request('func', 'string');
$modul_id           = rex_request('demo_module_id', 'int');

// STANDARD MODUL INSTALL
////////////////////////////////////////////////////////////////////////////////
$modul_in  = 'a720_demo.modul.in.php';
$modul_out = 'a720_demo.modul.out.php';
$modul_uid = '### UID:m720demo ###';

$modul_id = 0;
$modul_name = '';

$gm = new rex_sql;
$gm->setQuery('select * from rex_module where ausgabe LIKE "%'.$modul_uid.'%"');

foreach($gm->getArray() as $module)
{
  $modul_id   = $module["id"];
  $modul_name = $module["name"];
}

if ($func == 'install_modul')
{
  $default_module_name = $mypage.' Demo Modul';

  // Daten einlesen
  $in  = rex_get_file_contents($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_in);
  $out = rex_get_file_contents($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_out);

  $mi = new rex_sql;
  // $mi->debugsql = 1;
  $mi->setTable("rex_module");
  $mi->setValue("eingabe",addslashes($in));
  $mi->setValue("ausgabe",addslashes($out));

  if (isset($_REQUEST['demo_module_id']) && $modul_id == $_REQUEST['demo_module_id'])
  {
    // altes Modul aktualisieren
    $mi->setWhere('id="'.$modul_id.'"');
    $mi->update();
    echo rex_info('Modul "'.$modul_name.'" wurde wiederhergestellt.');
  }
  else
  {
    // neues Modul installieren
    $mi->setValue('name',$default_module_name);
    $mi->insert();
    echo rex_info('Modul wurde angelegt als "'.$default_module_name.'"');
  }
  unset($mi);
}

// MAIN
////////////////////////////////////////////////////////////////////////////////
$standard_msg = array('','');
if($modul_id > 0)
{
  $standard_msg = array(
  'Weiteres ',
  ' oder vorhandenes Modul wiederherstellen: <a href="index.php?page='.$mypage.'&amp;subpage=modul&amp;func=install_modul&amp;demo_module_id='.$modul_id.'">['.$modul_id.'] '.htmlspecialchars($modul_name).'</a>'
  );
}

echo '
<div class="rex-addon-output" id="subpage-'.$subpage.'">
  <h2 class="rex-hl2" style="font-size: 1em;">Modul Installer</h2>

  <div class="rex-addon-content">
    <div class="addon_template">
      <ul>
        <li>'.$standard_msg[0].'<a href="index.php?page='.$mypage.'&amp;subpage=modul&amp;func=install_modul">Beispielmodul installieren</a>'.$standard_msg[1].'</li>
        <li><a id="standard_show">Modul Code anzeigen</a></li>
      </ul>
    </div><!-- /.addon_template -->

    <div class="addon_template" id="demo_modul" style="display:none;">
    <h4>'.$modul_in.':</h4>';
      $file = $REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_in;
      $fh = fopen($file, 'r');
      $contents = fread($fh, filesize($file));
      ini_set('highlight.comment', 'silver;font-size:10px;display:none;');
      echo rex_highlight_string($contents);

      echo '
      <h4>'.$modul_out.':</h4>';
      $file = $REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_out;
      $fh = fopen($file, 'r');
      $contents = fread($fh, filesize($file));
      echo rex_highlight_string($contents);
      echo '
    </div><!-- /.addon_template -->

    <div class="addon_template">
      <p>Die Dateien der Beispielmodule befinden sich im Addon Ordner: <cite>./addons/'.$mypage.'/modules/...</cite></p>
    </div><!-- /.addon_template -->

  </div><!-- /.rex-addon-content -->
</div><!-- /.rex-addon-output -->


<script type="text/javascript">
<!--
jQuery(function($) {

  $("#standard_show").click(function() {
    $("#demo_modul").slideToggle("slow");
  });

});
//-->
</script>

';
