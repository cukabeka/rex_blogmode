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
$article_id  = rex_request('article_id',  'rex-article-id');
$clang       = rex_request('clang',       'rex-clang-id', $REX['START_CLANG_ID']);
$ctype       = rex_request('ctype',       'rex-ctype-id');
$edit_id     = rex_request('edit_id',     'rex-category-id');
$function    = rex_request('function',    'string');
$cat_name = "";
$art_add_link = "";
$select = "";
$info = "";
$warning = "";
$output = "";
$outputA = "";
$add_head = "";
$class = "";

// development overrides
$function    = 'add';
$categories  = $myREX['settings']['MULTISELECT']['categories'];
$category_id = $myREX['settings']['MULTISELECT']['categories'][0];
$template_id = $myREX['settings']['SELECT']['template_id'];
$module_id = $myREX['settings']['SELECT']['module_id'];
$slice_id = 0;
$clang = 0;
$ctype = 1;
$mode = 'edit';
$slice_revision = 0;
$KATPERM = TRUE ;
$_REQUEST['module_id'] = $module_id;

// register extensionpointz
rex_register_extension('ART_ADDED'  , 'get_id_for_post');
rex_register_extension('SLICE_ADDED', 'putArticleOnline');



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

if (#rex_post('artadd_function', 'boolean') && 
!rex_request('btn_save') &&
$category_id !== '' && $KATPERM &&  !$REX['USER']->hasPerm('editContentOnly[]'))
{
  // --------------------- ARTIKEL ADD
  $data = array();
  $data['prior']       = rex_post('Position_New_Article', 'int');
  $data['name']        = "blogmode_temp_".time();
  $data['template_id'] = $template_id;
  $data['category_id'] = $category_id;
  $data['path']        = $KATPATH;

  list($success, $message) = rex_addArticle($data);


}

// ARTICLE Input form
////////////////////////////////////////////////////////////////////////////////


//ARTICLE: FORM

  if ($REX['USER']->hasPerm('advancedMode[]'))
  {
    $add_head = '<th class="rex-small">'. $I18N->msg('header_id') .'</th>';
    $add_col  = '<col width="40" />';
  }

  if($function == 'add' || $function == 'edit')
  {

    $legend = $I18N->msg('article_add');
    if ($function == 'edit_art')
      $legend = $I18N->msg('article_edit');

    $outputA .= '
    <div class="rex-form" id="rex-form-structure-article">
    <input type="hidden" name="page" value="'.$mypage.'" />
    <input type="hidden" name="subpage" value="'.$subpage.'" />
    <input type="hidden" name="func" value="savesettings" />
';

    if ($article_id != "") $output .= '<input type="hidden" name="article_id" value="'. $article_id .'" />';
    $outputA .= '
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

  $outputA  .= '
      <table class="rex-table" summary="'. htmlspecialchars($I18N->msg('structure_articles_summary', $cat_name)) .'">
        <caption>'. htmlspecialchars($I18N->msg('structure_articles_caption', $cat_name)).'</caption>

        <thead>
          <tr>
            <th class="rex-icon">'. $art_add_link .'</th>
            '. $add_head .'
            <th>'.$I18N->msg('header_article_name').'</th>
            <th>'.$I18N->msg('header_category').'</th>
          </tr>
        </thead>
        ';

  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  #if($sql->getRows() > 0 || $function == 'add')
  {
    $outputA .= '<tbody>
          ';
  }


// ---------------------- Category select box


$id = 'categories';                                                       // ID dieser Select Box

$tmp = new rex_select();
$tmp->setSize(1);
$tmp->setName('category_id');
$tmp->setMultiple(false);
$tmp->setAttribute('id', 'cat-select');

  foreach ($categories as $opt) {
  		if($opt == 0) $tmp->addOption("Homepage",$opt);
		else $tmp->addOption(OOCategory::getCategoryById($opt)->getName(),$opt);
  }
$select = '
		  '.$tmp->get().'
          '; 



// --------------------- ARTIKEL ADD FORM
  if ($function == 'add' && $KATPERM && !$REX['USER']->hasPerm('editContentOnly[]'))
  {
    $add_td = '';
    if ($REX['USER']->hasPerm('advancedMode[]'))
      $add_td = '<td class="rex-small">-</td>';

    $outputA .= '<tr class="rex-table-row-activ">
            <td class="rex-icon"><span class="rex-i-element rex-i-article"><span class="rex-i-element-text">'.$I18N->msg('article_add') .'</span></span></td>
            '. $add_td .'
            <td><input type="text" class="rex-form-text" id="rex-form-field-name" name="article_name" /></td>
            '.
            # <td><input type="hidden" class="rex-form-text" id="rex-form-field-prior" name="Position_New_Article" value="'.($sql->getRows()+1).'" disabled="disabled" /></td>
             #'<td>'. $template_select->get() .'</td>
             # <td>'. rex_formatter :: format(time(), 'strftime', 'date') .'</td>
            #'
            #<td>'
            #<input type="submit" class="rex-form-submit" name="artadd_function" value="'.$I18N->msg('article_add') .'"'. rex_accesskey($I18N->msg('article_add'), $REX['ACKEY']['SAVE']) .' />
            # </td>
            #.
            '
          ';
          
    $outputA .= '
            <td class="rex-form-select">
            	'.$select.'
            </td>
          </tr>
          ';
          
          
}



  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  #if($sql->getRows() > 0 || $function == 'add' || $function == 'edit')
  {
    $outputA .= '</tbody>
          ';
	$outputA .= '
                  </table>';
  }
  
  //FORM close
  if($function == 'add' || $function == 'edit')
  {
    $outputA .= '
      <script type="text/javascript">
        <!--
        jQuery(function($){
          $("#rex-form-field-name").focus();
        });
        //-->
      </script>
  </div>
  ';
  }

// UPDATE VARS AFTER EP
//////////////////////////////////////////////

if(isset($myREX['temp']['article_id'])) $article_id = $myREX['temp']['article_id'];
#if(isset($myREX['temp']['category_id'])) $category_id = $myREX['temp']['category_id'];
$category_id = rex_request('category_id');
$_REQUEST['module_id'] = $module_id;

// SLICE
////////////////


      // ------------------------------------------ START: MODULE EDITIEREN/ADDEN ETC.

      $output .= '
                  <!-- *** OUTPUT OF ARTICLE-CONTENT-EDIT-MODE - START *** -->
                  <div class="rex-content-editmode">
                  ';
      $CONT = new rex_article_editor();
      $CONT->getContentAsQuery();
      $CONT->info = $info;
      $CONT->warning = $warning;
      $template_attributes = 'a:3:{s:10:"categories";a:1:{s:3:"all";s:1:"1";}s:5:"ctype";a:0:{}s:7:"modules";a:1:{i:1;a:1:{s:3:"all";s:1:"1";}}}';
      $CONT->template_attributes =  $template_attributes;
      $CONT->setArticleId($article_id);
      $CONT->setSliceId($slice_id);
      $CONT->setMode($mode);
      $CONT->setCLang($clang);
      $CONT->setEval(TRUE);
      $CONT->setSliceRevision($slice_revision);
      $CONT->setFunction('add');
      $output .= $CONT->getArticle($ctype);

// correct send/return-page to addon subpage
$output = str_replace(
			'<input type="hidden" name="page" value="content" />',
			'<input type="hidden" name="page" value="'.$mypage.'" />
			<input type="hidden" name="subpage" value="'.$subpage.'" />
			'
			,$output);


      $output .= '
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
        $global_warning = $I18N->msg('module_not_found')." ";
        // ------------- END: MODUL IST NICHT VORHANDEN
      }
      else
      {
        // ------------- MODUL IST VORHANDEN

        // ----- RECHTE AM MODUL ?
        if($function != 'delete' && !rex_template::hasModule($template_attributes,$ctype,$module_id))
        {
          $global_warning = $I18N->msg('no_rights_to_this_function')." ";

        } elseif (!($REX['USER']->isAdmin() || $REX['USER']->hasPerm('module[' . $module_id . ']') || $REX['USER']->hasPerm('module[0]')))
        {
          // ----- RECHTE AM MODUL: NEIN
          $global_warning = $I18N->msg('no_rights_to_this_function')." ";
        } else 
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
              $warning = $I18N->msg('slice_deleted_error')." ";
            else
              $warning = $I18N->msg('slice_saved_error')." ";

          }
          else
          {
            // ----- SAVE/UPDATE SLICE
            if ($function == 'add' || $function == 'edit')
            {
              $newsql = rex_sql::factory();
              // $newsql->debugsql = true;
              $sliceTable = $REX['TABLE_PREFIX'] . 'article_slice';
              $newsql->setTable($sliceTable);

              if ($function == 'add' || $function == 'edit')
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
                  $info = $action_message . $I18N->msg('block_updated')." ";
                  
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
              elseif ($function == 'add'  || $function == 'edit')
              {
                $newsql->addGlobalUpdateFields();
                $newsql->addGlobalCreateFields();

                if ($newsql->insert())
                {
                  $last_id = $newsql->getLastId();
                  if ($newsql->setQuery('UPDATE ' . $REX['TABLE_PREFIX'] . 'article_slice SET re_article_slice_id=' . $last_id . ' WHERE re_article_slice_id=' . $slice_id . ' AND id<>' . $last_id . ' AND article_id=' . $article_id . ' AND clang=' . $clang .' AND revision='.$slice_revision))
                  {
                    $info = $action_message . $I18N->msg('block_added')." ";
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

// correct send/return-page to addon subpage
$output = str_replace('<div class="rex-content-editmode-module-name">',
			'<div class="rex-content-editmode-module-name">
			'.$outputA,
			$output);

// RENAME TEMP ARTICLE
//////////////////////////


if(!rex_request('btn_save')) 
	echo $output;
else {
      $article_name = rex_request('article_name', 'string');
      $article_id = (int) rex_request('article_id', 'string');

      $meta_sql = rex_sql::factory();
      $meta_sql->setTable($REX['TABLE_PREFIX'] . "article");
      // $meta_sql->debugsql = 1;
      $meta_sql->setWhere("id='$article_id' AND clang=$clang");
      $meta_sql->setValue('name', $article_name);
      $meta_sql->addGlobalUpdateFields();

      if($meta_sql->update())
      {
        $info .= $I18N->msg("article_updated")." ";
        rex_deleteCacheArticle($article_id, $clang);
      }
      else
      {
        $warning .= $meta_sql->getError();
      }

// MOVE TEMP ARTICLE
//////////////////////////
    if ($category_id != $article_id)
    {
      $cur_category_id = OOArticle::getArticleById($article_id)->getCategoryId();
      $category_id_new = $category_id;
      if ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('moveArticle[]') && $REX['USER']->hasCategoryPerm($category_id_new)))
      {
        if (rex_moveArticle($article_id, $cur_category_id, $category_id_new))
        {
          $info .= $I18N->msg('content_articlemoved')." ";
          #ob_end_clean();
          #header('Location: index.php?page=content&article_id=' . $article_id . '&mode=meta&clang=' . $clang . '&ctype=' . $ctype . '&info=' . urlencode($info));
          #exit;
        }
        elseif($cur_category_id == $category_id_new) 
        {}
        else
        {
          $warning .= $I18N->msg('content_errormovearticle')." ";
        }
      }
      else
      {
        $warning .= $I18N->msg('no_rights_to_this_function')." ";
      }
    }

// PUT ONLINE
//////////////////////////

      $meta_sql = rex_sql::factory();
      $meta_sql->setTable($REX['TABLE_PREFIX'] . "article");
      $meta_sql->setWhere("id='$article_id' AND clang=$clang");
      $meta_sql->setValue('status', 1);
      $meta_sql->addGlobalUpdateFields();

      if($meta_sql->update())
      {
        $info .= $I18N->msg("article_status_updated")." ";
        rex_deleteCacheArticle($article_id, $clang);
      }
      else
      {
        $warning .= $meta_sql->getError();
      }




// OUTPUT STUFF
//////////////////////////

if ($info != "") echo rex_info ($info);
if ($warning != "") echo rex_warning ($warning);

$out_slice = OOArticleSlice::getSlicesForArticleOfType($article_id,$module_id);
if(is_object($out_slice)){
	foreach ($out_slice as $s) {
		echo $s->getHtml();
	}
}
echo '<div class="rex-form-row">';
echo '<a class="rex-button" href="index.php?page=content&article_id='.$article_id.'&mode=edit&clang=0&ctype=1"><span><span>Editieren</span></span></a>';
echo '<a class="rex-button" href="index.php?page=structure&article_id='.$article_id.'&function=status_article&category_id='.$category_id_new.'&clang=0"><span><span>Offline setzen</span></span></a>';
echo '<a class="rex-button" href="../'.rex_getUrl($article_id).'" target="_blank"><span><span>Anzeigen</span></span></a>';
echo '<a class="rex-button" href="index.php?page='.$mypage.'"><span><span>Weiterer Artikel</span></span></a>';
echo '</div>';
http://192.168.56.101/naschdwor/redaxo/index.php?page=structure&article_id=45&function=status_article&category_id=0&clang=0
}