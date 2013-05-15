<?php
/**
* Addon_REX_Blogmode
*
* @author http://cukabeka.de
* @link   https://github.com/cukabeka/rex_blogmode
*
* @package redaxo4.4
* @version 0.0.1
*/



// ADDON PARAMETER AUS URL HOLEN
////////////////////////////////////////////////////////////////////////////////
$mypage    = rex_request('page'   , 'string');
$subpage   = rex_request('subpage', 'string');
$minorpage = rex_request('minorpage', 'string');
$func      = rex_request('func'   , 'string');


// ADDON SETTINGS AUS $REX IN EIGENE VAR REDUZIEREN
////////////////////////////////////////////////////////////////////////////////
$myREX = $REX['ADDON'][$mypage];

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// define vars
$category_id = rex_request('category_id', 'rex-category-id');
$article_id  = rex_request('article_id',  'rex-article-id');
$clang       = rex_request('clang',       'rex-clang-id', $REX['START_CLANG_ID']);
$ctype       = rex_request('ctype',       'rex-ctype-id');
$edit_id     = rex_request('edit_id',     'rex-category-id');
$function    = rex_request('function',    'string');

// development overrides
$function    = 'add';
$article_id = 35;
$category_id = 0;
$template_id = 1;
$module_id = 3;
$slice_id = 0;
$clang = 0;
$ctype = 1;
$mode = 'edit';
$slice_revision = 0;
$KATPERM = TRUE ;

krumo($_POST);


//provide $katpath
// --------------------------------------------- Mountpoints

$mountpoints = $REX["USER"]->getMountpoints();
if(count($mountpoints)==1 && $category_id == 0)
{
  // Nur ein Mointpoint -> Sprung in die Kategory
  $category_id = current($mountpoints);
}

// --------------------------------------------- Rechte prŸfen
require $REX['INCLUDE_PATH'].'/functions/function_rex_category.inc.php';
require $REX['INCLUDE_PATH'].'/functions/function_rex_content.inc.php';



// Process function first, then process below INPUT FORM
////////////////////////////////////////////////////////////////////////////////

if (rex_post('artadd_function', 'boolean') && $category_id !== '' && $KATPERM &&  !$REX['USER']->hasPerm('editContentOnly[]'))
{
  // --------------------- ARTIKEL ADD
  $data = array();
  $data['prior']       = rex_post('Position_New_Article', 'int');
  $data['name']        = rex_post('article_name', 'string');
  $data['template_id'] = $template_id;
  $data['category_id'] = $category_id;
  $data['path']        = $KATPATH;

  list($success, $message) = rex_addArticle($data);

  if($success)
    $info = $message;
  else
    $warning = $message;
}

// ARTICLE Input form
////////////////////////////////////////////////////////////////////////////////


//ARTICLE: FORM

  if ($REX['USER']->hasPerm('advancedMode[]'))
  {
    $add_head = '<th class="rex-small">'. $I18N->msg('header_id') .'</th>';
    $add_col  = '<col width="40" />';
  }

  if($function == 'add' || $function == 'edit_art')
  {

    $legend = $I18N->msg('article_add');
    if ($function == 'edit_art')
      $legend = $I18N->msg('article_edit');

    echo '
    <div class="rex-form" id="rex-form-structure-article">
    <form action="index.php" method="post">
    <input type="hidden" name="page" value="'.$mypage.'" />
    <input type="hidden" name="subpage" value="'.$subpage.'" />
    <input type="hidden" name="func" value="savesettings" />
      <fieldset>
        <legend><span>'.$legend .'</span></legend>';

    if ($article_id != "") echo '<input type="hidden" name="article_id" value="'. $article_id .'" />';
    echo '
        <input type="hidden" name="clang" value="'. $clang .'" />';
  }

// ARTICLE: SQL

  // READ DATA
  $sql = rex_sql::factory();
  // $sql->debugsql = true;
  $sql->setQuery('SELECT *
        FROM
          '.$REX['TABLE_PREFIX'].'article
        WHERE
          ((re_id='. $category_id .' AND startpage=0) OR (id='. $category_id .' AND startpage=1))
          AND clang='. $clang .'
        ORDER BY
          prior, name');

  echo '
      <table class="rex-table" summary="'. htmlspecialchars($I18N->msg('structure_articles_summary', $cat_name)) .'">
        <caption>'. htmlspecialchars($I18N->msg('structure_articles_caption', $cat_name)).'</caption>

        <thead>
          <tr>
            <th class="rex-icon">'. $art_add_link .'</th>
            '. $add_head .'
            <th>'.$I18N->msg('header_status').'</th>
            <th>'.$I18N->msg('header_article_name').'</th>
           <th>'.$I18N->msg('header_priority').'</th>
          </tr>
        </thead>
        ';

  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  if($sql->getRows() > 0 || $function == 'add')
  {
    echo '<tbody>
          ';
  }

// --------------------- ARTIKEL ADD FORM
  if ($function == 'add' && $KATPERM && !$REX['USER']->hasPerm('editContentOnly[]'))
  {
    if($REX['DEFAULT_TEMPLATE_ID'] > 0 && isset($TEMPLATE_NAME[$REX['DEFAULT_TEMPLATE_ID']]))
    {
      $template_select->setSelected($REX['DEFAULT_TEMPLATE_ID']);

    }else
    {
      // template_id vom Startartikel erben
      $sql2 = rex_sql::factory();
      $sql2->setQuery('SELECT template_id FROM '.$REX['TABLE_PREFIX'].'article WHERE id='. $category_id .' AND clang='. $clang .' AND startpage=1');
      if ($sql2->getRows() == 1)
        $template_select->setSelected($sql2->getValue('template_id'));
    }

    $add_td = '';
    if ($REX['USER']->hasPerm('advancedMode[]'))
      $add_td = '<td class="rex-small">-</td>';

    echo '<tr class="rex-table-row-activ">
            <td class="rex-icon"><span class="rex-i-element rex-i-article"><span class="rex-i-element-text">'.$I18N->msg('article_add') .'</span></span></td>
            '. $add_td .'
            <td><input type="text" class="rex-form-text" id="rex-form-field-name" name="article_name" /></td>
            <td><input type="text" class="rex-form-text" id="rex-form-field-prior" name="Position_New_Article" value="'.($sql->getRows()+1).'" disabled="disabled" /></td>
            '. #'<td>'. $template_select->get() .'</td>
             # <td>'. rex_formatter :: format(time(), 'strftime', 'date') .'</td>
            '
            <td><input type="submit" class="rex-form-submit" name="artadd_function" value="'.$I18N->msg('article_add') .'"'. rex_accesskey($I18N->msg('article_add'), $REX['ACKEY']['SAVE']) .' /></td>
          </tr>
          ';
    echo '<tr class="rex-table-row-activ">
            <td class="rex-icon" colspan ="5" >
            	'.$select.'
            </td>
          </tr>
          ';
          
          
}

// SLICE
////////////////


      // ------------------------------------------ START: MODULE EDITIEREN/ADDEN ETC.

      echo '
                  <!-- *** OUTPUT OF ARTICLE-CONTENT-EDIT-MODE - START *** -->
                  <div class="rex-content-editmode">
                  ';
      $CONT = new rex_article_editor();
      $CONT->getContentAsQuery();
      $CONT->info = $info;
      $CONT->warning = $warning;
      //$CONT->template_attributes = $template_attributes;
      $CONT->template_attributes = 'a:3:{s:10:"categories";a:1:{s:3:"all";s:1:"1";}s:5:"ctype";a:0:{}s:7:"modules";a:1:{i:1;a:1:{s:3:"all";s:1:"1";}}}';
      $CONT->setArticleId($article_id);
      $CONT->setSliceId($slice_id);
      $CONT->setMode($mode);
      $CONT->setCLang($clang);
      $CONT->setEval(TRUE);
      $CONT->setSliceRevision($slice_revision);
      $CONT->setFunction($function);
      krumo($CONT);

      echo $CONT->getArticle($ctype);

      echo '
                  </div>
                  <!-- *** OUTPUT OF ARTICLE-CONTENT-EDIT-MODE - END *** -->
                  ';

// SQL-Stuff
      $CM = rex_sql::factory();
      {
        // add
        $CM->setQuery('SELECT * FROM ' . $REX['TABLE_PREFIX'] . 'module WHERE id='.$module_id);
      }

      if ($CM->getRows() != 1)
      {
        // ------------- START: MODUL IST NICHT VORHANDEN
        $global_warning = $I18N->msg('module_not_found');
        // ------------- END: MODUL IST NICHT VORHANDEN
      }
      else
      {
        // ------------- MODUL IST VORHANDEN

        // ----- RECHTE AM MODUL ?
        if($function != 'delete' && !rex_template::hasModule($template_attributes,$ctype,$module_id))
        {
          $global_warning = $I18N->msg('no_rights_to_this_function');

        }elseif (!($REX['USER']->isAdmin() || $REX['USER']->hasPerm('module[' . $module_id . ']') || $REX['USER']->hasPerm('module[0]')))
        {
          // ----- RECHTE AM MODUL: NEIN
          $global_warning = $I18N->msg('no_rights_to_this_function');
        }else 
        {
          // ----- RECHTE AM MODUL: JA

          // ***********************  daten einlesen
          $REX_ACTION = array ();
          $REX_ACTION['SAVE'] = true;

          foreach ($REX['VARIABLES'] as $obj)
          {
            $REX_ACTION = $obj->getACRequestValues($REX_ACTION);
          }

          // ----- PRE SAVE ACTION [ADD/EDIT/DELETE]
          list($action_message, $REX_ACTION) = rex_execPreSaveAction($module_id, $function, $REX_ACTION);
          // ----- / PRE SAVE ACTION

          // Statusspeicherung für die rex_article Klasse
          $REX['ACTION'] = $REX_ACTION;

          // Werte werden aus den REX_ACTIONS übernommen wenn SAVE=true
          if (!$REX_ACTION['SAVE'])
          {
            // ----- DONT SAVE/UPDATE SLICE
            if ($action_message != '')
              $warning = $action_message;
            elseif ($function == 'delete')
              $warning = $I18N->msg('slice_deleted_error');
            else
              $warning = $I18N->msg('slice_saved_error');

          }
          else
          {
            // ----- SAVE/UPDATE SLICE
            if ($function == 'add' || $function == 'edit' || $function == 'add')
            {
              $newsql = rex_sql::factory();
              // $newsql->debugsql = true;
              $sliceTable = $REX['TABLE_PREFIX'] . 'article_slice';
              $newsql->setTable($sliceTable);

              if ($function == 'edit')
              {
                $newsql->setWhere('id=' . $slice_id);
              }
              elseif ($function == 'add' || $function == 'add')
              {
                $newsql->setValue($sliceTable .'.re_article_slice_id', $slice_id);
                $newsql->setValue($sliceTable .'.article_id', $article_id);
                $newsql->setValue($sliceTable .'.modultyp_id', $module_id);
                $newsql->setValue($sliceTable .'.clang', $clang);
                $newsql->setValue($sliceTable .'.ctype', $ctype);
                $newsql->setValue($sliceTable .'.revision', $slice_revision);
              }

              // ****************** SPEICHERN FALLS NOETIG
              foreach ($REX['VARIABLES'] as $obj)
              {
                $obj->setACValues($newsql, $REX_ACTION, true);
              }

              if ($function == 'edit')
              {
                $newsql->addGlobalUpdateFields();
                if ($newsql->update())
                {
                  $info = $action_message . $I18N->msg('block_updated');
                  
                  // ----- EXTENSION POINT
                  $info = rex_register_extension_point('SLICE_UPDATED', $info,
                    array(
                      'article_id' => $article_id,
                      'clang' => $clang,
                      'function' => $function,
                      'mode' => $mode,
                      'slice_id' => $slice_id,
                      'page' => 'content',
                      'ctype' => $ctype,
                      'category_id' => $category_id,
                      'module_id' => $module_id, 
                      'article_revision' => &$article_revision,
                      'slice_revision' => &$slice_revision,
                    )
                  );
                }
                else
                  $warning = $action_message . $newsql->getError();

              }
              elseif ($function == 'add'  || $function == 'add')
              {
                $newsql->addGlobalUpdateFields();
                $newsql->addGlobalCreateFields();
krumo($newsql);

                if ($newsql->insert())
                {
                  $last_id = $newsql->getLastId();
                  if ($newsql->setQuery('UPDATE ' . $REX['TABLE_PREFIX'] . 'article_slice SET re_article_slice_id=' . $last_id . ' WHERE re_article_slice_id=' . $slice_id . ' AND id<>' . $last_id . ' AND article_id=' . $article_id . ' AND clang=' . $clang .' AND revision='.$slice_revision))
                  {
                    $info = $action_message . $I18N->msg('block_added');
                    $slice_id = $last_id;
                    
                    
                    // ----- EXTENSION POINT
                    $info = rex_register_extension_point('SLICE_ADDED', $info,
                      array(
                        'article_id' => $article_id,
                        'clang' => $clang,
                        'function' => $function,
                        'mode' => $mode,
                        'slice_id' => $slice_id,
                        'page' => 'content',
                        'ctype' => $ctype,
                        'category_id' => $category_id,
                        'module_id' => $module_id, 
                        'article_revision' => &$article_revision,
                        'slice_revision' => &$slice_revision,
                      )
                    );
                  }
                  $function = "";
                }
                else
                {
                  $warning = $action_message . $newsql->getError();
                }
              }
            }
            else
            {
              // make delete
            }
            // ----- / SAVE SLICE

            // ----- artikel neu generieren
            $EA = rex_sql::factory();
            $EA->setTable($REX['TABLE_PREFIX'] . 'article');
            $EA->setWhere('id='. $article_id .' AND clang='. $clang);
            $EA->addGlobalUpdateFields();
            $EA->update();
            rex_deleteCacheArticle($article_id, $clang);

            rex_register_extension_point('ART_CONTENT_UPDATED', '',
              array (
                'id' => $article_id,
                'clang' => $clang
              )
            );

            // ----- POST SAVE ACTION [ADD/EDIT/DELETE]
            $info .= rex_execPostSaveAction($module_id, $function, $REX_ACTION);
            // ----- / POST SAVE ACTION

            // Update Button wurde gedrückt?
            // TODO: Workaround, da IE keine Button Namen beim
            // drücken der Entertaste übermittelt
            if (rex_post('btn_save', 'string'))
            {
              $function = '';
            }
          }
	}
}


// FOOTER
////////////////


  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  if($sql->getRows() > 0 || $function == 'add')
  {
    echo '</tbody>
          ';
  }
  
  //FORM close
  if($function == 'add' || $function == 'edit_art')
  {
    echo '
      <script type="text/javascript">
        <!--
        jQuery(function($){
          $("#rex-form-field-name").focus();
        });
        //-->
      </script>
    </fieldset>
  </form>
  </div></div>';
  }

          