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
$modul_id           = rex_request('module_id', 'int');
$file_id            = rex_request('file_id', 'int');

// STANDARD MODUL INSTALL
////////////////////////////////////////////////////////////////////////////////

$template1 = "";
$addonname = "Blogmode";

$modul_in  = 'rex_blogmode_artlist.modul.in.php';
$modul_out = 'rex_blogmode_artlist.modul.out.php';
$modul_uid = '### UID:rex_blogmode_liste ###';
$modul_name = 'Kategorieansicht mit Bildvorschau';


$modul_in2  = 'rex_blogmode_the_post.modul.in.php';
$modul_out2 = 'rex_blogmode_the_post.modul.out.php';
$modul_uid2 = '### UID:rex_blogmode_the_post ###';
$modul_name2 = 'Detailansicht';


  $sql = rex_sql::factory();
  
  $page = rex_request('page', 'string');
  $subpage = rex_request('subpage', 'string');
  $module_in = null;
  $module_out = null;
  $module_name = null;

  if(OOAddon::isAvailable($mypage)) {
?>
    <div class="rex-addon-content">
      <h2>Module installieren</h2>
      <ul style="list-style: none;line-height: 2.0em;">
        <li><a href="index.php?page=<?=$page?>&subpage=<?=$subpage?>&func=1"><?php echo $addonname." ".$modul_name; ?></a></li>
        <li><a href="index.php?page=<?=$page?>&subpage=<?=$subpage?>&func=2"><?php echo $addonname." ".$modul_name2; ?></a></li>
      </ul>
    </div>
<?php
  if(rex_get('func')) {
    global $REX;
    $func = rex_get('func');

    switch ($func) {
      case 1:
        $module_name = $addonname." ".$modul_name;
        $module_in = rex_get_file_contents($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_in);
        $module_out = rex_get_file_contents($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_out);
      break;      
      case 2:
        $module_name = $addonname." ".$modul_name2;
        $module_in = rex_get_file_contents($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_in2);
        $module_out = rex_get_file_contents($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$modul_out2);
      break;

      case 4:
        $template_name = $addonname." Empfehlungen Template";
        $template_code = rex_get_file_contents($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/modules/'.$template1);
      break;

    }



    if($func != 4) {

      //search if module exists then delete and write new
      $sql->setQuery('SELECT * FROM rex_module WHERE name LIKE "%'.$module_name.'%"');
      $array_size = intval(sizeof($sql->getArray()));
      
      if($array_size >= 1) {
        $sql->setTable('rex_module');
        
        $sql->setWhere('name = "'.$module_name.'"');
        $sql->setValue('eingabe', addslashes($module_in));
        $sql->setValue('ausgabe', addslashes($module_out));
        $sql->setValue('name', $module_name);
        $sql->update();
        
        if($sql->insert())
	        echo rex_info("Das Modul '".$module_name."' wurde erfolgreich aktualisiert!");
	    else 
	    	echo rex_warning("Ein Fehler beim Aktualisieren ist aufgetreten!");

        
      } else if(sizeof($sql->getArray()) == 0) {
  
        $sql->setTable('rex_module');
        $sql->setWhere('name = "'.$module_name.'"');
        $sql->setValue('eingabe', addslashes($module_in));
        $sql->setValue('ausgabe', addslashes($module_out));
        $sql->setValue('name', $module_name);
        krumo($sql);
        
        if($sql->insert())
	        echo rex_info("Das Modul '".$module_name."' wurde erfolgreich installiert!");
	    else 
	    	echo rex_warning("Ein Fehler beim Installieren ist aufgetreten!");

      }
    } else {
      //search if template exists than update else write a new module
      $sql->setQuery('SELECT * FROM rex_template WHERE name LIKE "%'.$template_name.'%"');
      $array_size = intval(sizeof($sql->getArray()));
    
      if($array_size >= 1) {
        $sql->setTable('rex_template');
        $sql->setWhere('name = "'.$template_name.'"');
        $sql->setValue('content', $template_code);
        $sql->setValue('updatedate', time());
        $sql->update();
        
        echo rex_info("Das Template '".$module_name."' wurde erfolgreich aktualisiert!");
        
      }else if(sizeof($sql->getArray()) == 0) {
        $sql->setTable('rex_template');
        $sql->setValue('name', $template_name);
        $sql->setValue('content', $template_code);
        $sql->setValue('active', '0');
        $sql->setValue('createuser','entwickler');
        $sql->setValue('updateuser','entwickler');
        $sql->setValue('createdate',time());
        $sql->setValue('updatedate', time());
        $sql->setValue('attributes', 'a:3:{s:10:"categories";a:1:{s:3:"all";s:1:"1";}s:5:"ctype";a:0:{}s:7:"modules";a:1:{i:1;a:1:{s:3:"all";s:1:"1";}}}');
        $sql->setValue('revision','0');
        $sql->insert();

        echo rex_info("Das Template '"."'wurde erfolgreich installiert!");

      }
    }
  }
}
?>