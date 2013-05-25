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


// ADDON PARAMETER AUS URL HOLEN
////////////////////////////////////////////////////////////////////////////////
$mypage    = rex_request('page'   , 'string');
$subpage   = rex_request('subpage', 'string');
$minorpage = rex_request('minorpage', 'string');
$func      = rex_request('func'   , 'string');

// FORMULAR PARAMETER SPEICHERN
////////////////////////////////////////////////////////////////////////////////
if($func=='savesettings')
{
  // MERGE REQUEST & ADDON SETTINGS
  $params_cast = $REX['ADDON'][$mypage]['params_cast'];
  $myCONF = array_merge($REX['ADDON'][$mypage]['settings'],a720_cast($_POST,$params_cast));

  // SAVE SETTINGS
  if(a720_saveConf($myCONF))
  {
    echo rex_info('Einstellungen wurden gespeichert.');
  }
  else
  {
    echo rex_warning('Beim speichern der Einstellungen ist ein Problem aufgetreten.');
  }
}

// ADDON SETTINGS AUS $REX IN EIGENE VAR REDUZIEREN
////////////////////////////////////////////////////////////////////////////////
$myREX = $REX['ADDON'][$mypage];

// SELECT BOX
////////////////////////////////////////////////////////////////////////////////
$id = 'template_id';                                                      // ID dieser Select Box
$tmp = new rex_select();                                      // rex_select Objekt initialisieren
$tmp->setSize(1);                                             // 1 Zeilen = normale Selectbox
$tmp->setName('SELECT['.$id.']');
$tmp->setSelected($myREX['settings']['SELECT'][$id]);         // gespeicherte Werte einsetzen

  $hole = rex_sql::factory();
  $hole->setQuery("SELECT * FROM " . $REX['TABLE_PREFIX'] . "template WHERE active = 1");
  $arr=$hole->getArray();

  foreach ($arr as $opt) {
		$tmp->addOption($opt['name'],$opt['id']);
  }
$select_template = $tmp->get();                                        // HTML in Variable speichern


// SELECT BOX
////////////////////////////////////////////////////////////////////////////////
$id = 'module_id';                                                       // ID dieser Select Box
$tmp = new rex_select();                                      // rex_select Objekt initialisieren
$tmp->setSize(1);                                             // 1 Zeilen = normale Selectbox
$tmp->setName('SELECT['.$id.']');
$tmp->setSelected($myREX['settings']['SELECT'][$id]);         // gespeicherte Werte einsetzen

  $hole = rex_sql::factory();
  $hole->setQuery("SELECT * FROM " . $REX['TABLE_PREFIX'] . "module");
  $arr=$hole->getArray();

  foreach ($arr as $opt) {
		$tmp->addOption($opt['name'],$opt['id']);
  }
$select_modul = $tmp->get();                                        // HTML in Variable speichern

// MULTISELECT BOX
////////////////////////////////////////////////////////////////////////////////
$id = 'categories';                                                      // ID dieser MultiSelect Box

$tmp = new rex_category_select();
$tmp->setSize(15);
$tmp->setName('MULTISELECT['.$id.'][]');
$tmp->setMultiple(true);
$tmp->setAttribute('id', 'cat-select');


if(isset($myREX['settings']['MULTISELECT'][$id]))             // evtl. keine Werte -> prüfen ob was gespeichert
{
  $tmp->setSelected($myREX['settings']['MULTISELECT'][$id]);  // gespeicherte Werte einsetzen
}

$multiselect = $tmp->get();

// OUTPUT HTML
////////////////////////////////////////////////////////////////////////////////

echo '
<div class="rex-addon-output" id="subpage-'.$subpage.'">
  <div class="rex-form">

  <form action="index.php" method="POST" id="settings">
    <input type="hidden" name="page" value="'.$mypage.'" />
    <input type="hidden" name="subpage" value="'.$subpage.'" />
    <input type="hidden" name="func" value="savesettings" />


        <fieldset class="rex-form-col-1">
          <legend>Auswahl von Standard-Template / Modul und Kategorien</legend>
          <div class="rex-form-wrapper">

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-select">
                <label for="select">Modultyp wählen</label>
                '.$select_modul.'
              </p>
            </div><!-- .rex-form-row -->

          
            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-select">
                <label for="select">Template wählen</label>
                '.$select_template.'
              </p>
            </div><!-- .rex-form-row -->

          
          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-select">
              <label for="multiselect">Auswahl der Kategorien, in die gepostet werden darf</label>
                '.$multiselect.'
            </p>
          </div><!-- .rex-form-row -->

          </div><!-- .rex-form-wrapper -->
        </fieldset>

        <fieldset class="rex-form-col-1">
          <div class="rex-form-wrapper">
            <div class="rex-form-row rex-form-element-v2">
              <p class="rex-form-submit">
                <input class="rex-form-submit" type="submit" id="submit" name="submit" value="Einstellungen speichern" />
              </p>
            </div><!-- .rex-form-row -->

          </div><!-- .rex-form-wrapper -->
        </fieldset>

  </form>

  </div><!-- .rex-form -->
</div><!-- .rex-addon-output -->
';
