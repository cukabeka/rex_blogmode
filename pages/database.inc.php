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
$id        = rex_request('id', 'int');


// TABELLE IDENTIFIER
/////////////////////////////////////////////////////////////////////////////////
$AddonDBTable = $REX['TABLE_PREFIX'].'720_'.$mypage;


// TABELLE ANLEGEN
/////////////////////////////////////////////////////////////////////////////////
$query = 'SELECT * FROM '.$AddonDBTable;
$db_available = false;
$tbl = new rex_sql();
$tbl->setQuery($query);

if($tbl->getErrno()==1146 && $func!='setupdb')
{
  echo rex_info('Datenbank Tabelle <em>'.$AddonDBTable.'</em> ist nicht angelegt. <a href="index.php?page=addon_template&subpage=database&func=setupdb">Tabelle anlegen.</a>');
}
else
{
  $db_available = true;
}

if($tbl->getErrno()==1146 && $func=='setupdb')
{
  $query = 'CREATE TABLE `'.$AddonDBTable.'` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_1`  varchar(255) NOT NULL,
  `field_2`  varchar(255) NOT NULL,
  `field_3`  varchar(255) NOT NULL,
  `field_4`  varchar(255) NOT NULL,
  `field_5`  varchar(255) NOT NULL,
  `field_6`  varchar(255) NOT NULL,
  `field_7`  varchar(255) NOT NULL,
  `field_8`  varchar(255) NOT NULL,
  `field_9`  varchar(255) NOT NULL,
  `field_10` varchar(255) NOT NULL,
  KEY `id` (`id`)
)';
  $tbl->setQuery($query);
  $query = 'INSERT INTO `'.$AddonDBTable.'`
  VALUES
  (
  1,
  \'Erster..\',
  \'..Datensatz..\',
  \'evtl\',
  \'|1|blau|\',
  \'|1|\',
  0,
  \'\',
  \'\',
  \'\',
  \'\'
  );';
  $tbl->setQuery($query);

  echo rex_info('Datenbank Tabelle <em>'.$AddonDBTable.'</em> wurde angelegt');

  $db_available = true;
  $func = '';
}


// AUSGABE DER SEITE JE NACH $func
/////////////////////////////////////////////////////////////////////////////////
$pagination = $REX['ADDON'][$mypage]['rex_list_pagination'];

if($func == "" && $db_available)
{
  /* LISTE ------------------------------------------------------------------ */
   echo '
   <div class="rex-addon-output" id="subpage-'.$subpage.'">
   <h2 class="rex-hl2">Übersicht <span style="color:silver;font-size:12px;">(DB Tabelle: '.$AddonDBTable.')</span></h2>';

  // alle Felder abfragen und anzeigen
  $query = 'SELECT * FROM '.$AddonDBTable;
  $list = new rex_list($query,$pagination,'data');

  // DEBUG SWITCH
  $list->debug = false;

  $imgHeader = '<a href="'. $list->getUrl(array('func' => 'add')) .'"><img src="media/metainfo_plus.gif" alt="add" title="add" /></a>';

  $list->setColumnSortable('id'      );
  $list->setColumnSortable('field_1' );
  //$list->setColumnSortable('field_2' );
  $list->setColumnSortable('field_3' );
  $list->setColumnSortable('field_4' );
  $list->setColumnSortable('field_5' );
  $list->setColumnSortable('field_6' );
  //$list->setColumnSortable('field_7' );
  //$list->setColumnSortable('field_8' );
  //$list->setColumnSortable('field_9' );
  //$list->setColumnSortable('field_10');


  $list->addColumn($imgHeader,'<img src="media/metainfo.gif" alt="field" title="field" />',0,array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
  $list->setColumnParams($imgHeader,array('func' => 'edit', 'id' => '###id###'));

  $list->setColumnLabel('id'       ,'ID');
  $list->setColumnLabel('field_1'  ,'Text');
  $list->setColumnLabel('field_2'  ,'Textarea');
  $list->setColumnLabel('field_3'  ,'Select');
  $list->setColumnLabel('field_4'  ,'Multiselect');
  $list->setColumnLabel('field_5'  ,'Check');
  $list->setColumnLabel('field_6'  ,'Radio');
  $list->setColumnLabel('field_7'  ,'Mediabutton');
  $list->setColumnLabel('field_8'  ,'MediaList');
  $list->setColumnLabel('field_9'  ,'Link');
  $list->setColumnLabel('field_10' ,'Linklist');


  //$list->setColumnParams('id'           ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_1'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_2'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_3'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_4'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_5'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_6'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_7'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_8'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_9'  ,array('func' => 'edit', 'id' => '###id###'));
  $list->setColumnParams('field_10' ,array('func' => 'edit', 'id' => '###id###'));
  $list->show();

  echo '</div>';
}

elseif (($func == 'edit' || $func == 'add') && $db_available)
{
  /* ADD/EDIT FORMULAR ------------------------------------------------------ */

  echo '<div class="rex-addon-output" id="subpage-'.$subpage.'">';

  // Pberschrift je nach Funktion ADD/EDIT
  if($func == 'edit')
  {
    echo '<h2 class="rex-hl2">Datensatz bearbeiten <span style="color:silver;font-size:12px;">(ID: '.$id.')</span></h2>';
  }
  else
  {
    echo '<h2 class="rex-hl2">Neuen Datensatz anlegen</h2>';
  }


  $form = new rex_form($AddonDBTable,'Texteingabe','id='.$id,'post',false);

  // Ein neues Fieldset
  $form->addFieldset('Texteingabe');

  // Textfeld
  $field = &$form->addTextField('field_1');
  $field->setLabel("Textfeld");

  // Textarea
  $field = &$form->addTextAreaField('field_2');
  $field->setLabel("Textarea");

  // Ein neues Fieldset
  $form->addFieldset('Auswahlfelder');

  // Starndard Selectbox
  $field =& $form->addSelectField('field_3');
  $field->setLabel("Selectbox");
  $select =& $field->getSelect();
  $select->setSize(1); /* 1 = eine Zeile = "normale Selectbox" */
  $select->addOption('Ja',1);
  $select->addOption('Nein',0);
  $select->addOption('Eventuell','evtl');

  // Multi Selectbox
  $field =& $form->addSelectField('field_4');
  $field->setAttribute('multiple','multiple');
  $field->setLabel("MultiSelectbox");
  $select =& $field->getSelect();
  $select->addOption('Rot',1);
  $select->addOption('Grün',0);
  $select->addOption('Blau','blau');

  // Checkbox
  $checkbox = &$form->addCheckboxField('field_5');
  $checkbox->setLabel("Checkbox");
  $checkbox->setAttribute('class','rex-form-checkbox rex-form-label-right floated');
  $checkbox->addOption('Ja','1');
  $checkbox->addOption('Nein','0'); /* "0" als string nicht als integer benutzen! */
  $checkbox->addOption('Eventuell','evtl');

  // Radiobutton
  $radio = &$form->addRadioField('field_6');
  $radio->setLabel("Radiobutton");
  $radio->setAttribute('class','rex-form-radio rex-form-label-right floated');
  $radio->addOption('Ja',1);
  $radio->addOption('Nein',0);
  $radio->addOption('Eventuell','evtl');

  // Ein neues Fieldset
  $form->addFieldset('Dateien aus Medienpool');

  // Einzelne Mediapool Datei
  $mb = &$form->addMediaField('field_7');
  $mb->setLabel("Mediabutton");

  // Mehrere Mediapool Dateien
  $ml = &$form->addMedialistField('field_8');
  $ml->setLabel("Medialist");

  // Ein weitere neues Fieldset
  $form->addFieldset('Interne Links');

  // Einzelner link
  $lm = &$form->addLinkmapField('field_9');
  $lm->setLabel("Linkmap");

  // Mehrere links
  $ll = &$form->addLinklistField('field_10');
  $ll->setLabel("Linklist");

  // Wenn editiert wird, braucht man die id des Datensatzes
  if($func == 'edit')
  {
    $form->addParam('id', $id);
  }

  $form->show();

  echo '</div>
<script>

  (function($){

    // REX_FORM FIELDSET TOGGLER
    $(".rex-form fieldset legend").click(function(){
      parent = $(this);
      target = parent.next(".rex-form-wrapper");
      target.toggle("fast",function(){
        if(target.css("display")=="none"){
          parent.addClass("closed");
        }else{
          parent.removeClass("closed");
        }
      });
    });

  })(jQuery);

</script>
  ';

}
