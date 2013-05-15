<?php


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

// request vars
$category_id = rex_request('category_id', 'rex-category-id');
$article_id  = rex_request('article_id',  'rex-article-id');
$clang       = rex_request('clang',       'rex-clang-id', $REX['START_CLANG_ID']);
$ctype       = rex_request('ctype',       'rex-ctype-id');
$edit_id     = rex_request('edit_id',     'rex-category-id');
$function    = rex_request('function',    'string');

// development overrides
$function    = 'add';
$category_id = 0;
$template_id = 1;
$module_id = 1;
$slice_id = 0;
$clang = 0;
$ctype = 1;
$mode = 'edit';
$slice_revision = 0;
$article = rex_sql::factory();
$article->setQuery("
    SELECT
      article.*, template.attributes as template_attributes
    FROM
      " . $REX['TABLE_PREFIX'] . "article as article
    LEFT JOIN " . $REX['TABLE_PREFIX'] . "template as template
      ON template.id=article.template_id
    WHERE
      article.id='$article_id'
      AND clang=$clang");

$template_attributes = $article->getValue('template_attributes');

$info = 'debug';
$warning = 'debug';

#&page=content&mode=edit&slice_id=0&function=add&clang=0&ctype=1&module_id=2


//provide $katpath

$KAT = rex_sql::factory();
// $KAT->debugsql = true;
$KAT->setQuery("SELECT * FROM ".$REX['TABLE_PREFIX']."article WHERE id=$category_id AND startpage=1 AND clang=$clang");
if ($KAT->getRows()!=1)
{
  // kategorie existiert nicht
  if($category_id != 0)
  {
    $category_id = 0;
    $article_id = 0;
  }
}
else
{
  // kategorie existiert
  $KPATH = explode('|',$KAT->getValue('path'));
  $KATebene = count($KPATH)-1;
  for ($ii=1;$ii<$KATebene;$ii++)
  {
    $SKAT = rex_sql::factory();
    $SKAT->setQuery('SELECT * FROM '. $REX['TABLE_PREFIX'] .'article WHERE id='. $KPATH[$ii] .' AND startpage=1 AND clang='. $clang);
    $catid = $SKAT->getValue('id');
    if ($SKAT->getRows()==1)
    {
      $KATPATH .= $KPATH[$ii]."|";
      if ($KATPERM || $REX['USER']->hasCategoryPerm($catid))
      {
      }
    }
  }
  if ($KATPERM || $REX['USER']->hasPerm('csw['. $category_id .']') /*|| $REX['USER']->hasPerm('csr['. $category_id .']')*/)
  {
    $KATPATH .= $category_id .'|';
  }
  else
  {
    $category_id = 0;
    $article_id = 0;
  }
}




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




// --------------------------------------------- TITLE

rex_title($I18N->msg('title_structure'), $KATout);

$sprachen_add = '&amp;category_id='. $category_id;
require $REX['INCLUDE_PATH'].'/functions/function_rex_languages.inc.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_categoryStatusTypes();
$artStatusTypes = rex_articleStatusTypes();


// --------------------------------------------- ARTIKEL FUNKTIONEN

if ($function == 'status_article' && $article_id != ''
    && ($REX['USER']->isAdmin() || $KATPERM && $REX['USER']->hasPerm('publishArticle[]')))
{
  // --------------------- ARTICLE STATUS
  list($success, $message) = rex_articleStatus($article_id, $clang);

  if($success)
    $info = $message;
  else
    $warning = $message;
}
// Hier mit !== vergleichen, da 0 auch einen gültige category_id ist (RootArtikel)
elseif (rex_post('artadd_function', 'boolean') && $category_id !== '' && $KATPERM &&  !$REX['USER']->hasPerm('editContentOnly[]'))
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
elseif (rex_post('artedit_function', 'boolean') && $article_id != '' && $KATPERM)
{
  // --------------------- ARTIKEL EDIT
  $data = array();
  $data['prior']       = rex_post('Position_Article', 'int');
  $data['name']        = rex_post('article_name', 'string');
  $data['template_id'] = rex_post('template_id', 'rex-template-id');
  $data['category_id'] = $category_id;
  $data['path']        = $KATPATH;

  list($success, $message) = rex_editArticle($article_id, $clang, $data);

  if($success)
    $info = $message;
  else
    $warning = $message;
}
elseif ($function == 'artdelete_function' && $article_id != '' && $KATPERM && !$REX['USER']->hasPerm('editContentOnly[]'))
{
  // --------------------- ARTIKEL DELETE
  list($success, $message) = rex_deleteArticleReorganized($article_id);

  if($success)
    $info = $message;
  else
    $warning = $message;
}

// --------------------------------------------- KATEGORIE LISTE

if ($warning != "")
  echo rex_warning($warning);

if ($info != "")
  echo rex_info($info);

$cat_name = 'Homepage';
$category = OOCategory::getCategoryById($category_id, $clang);
if($category)
  $cat_name = $category->getName();

$add_category = '';
if ($KATPERM && !$REX['USER']->hasPerm('editContentOnly[]'))
{
  $add_category = '<a class="rex-i-element rex-i-category-add" href="index.php?page=structure&amp;category_id='.$category_id.'&amp;function=add_cat&amp;clang='.$clang.'"'. rex_accesskey($I18N->msg('add_category'), $REX['ACKEY']['ADD']) .'><span class="rex-i-element-text">'.$I18N->msg("add_category").'</span></a>';
}

$add_header = '';
$add_col = '';
$data_colspan = 4;
if ($REX['USER']->hasPerm('advancedMode[]'))
{
  $add_header = '<th class="rex-small">'.$I18N->msg('header_id').'</th>';
  $add_col = '<col width="40" />';
  $data_colspan = 5;
}

echo rex_register_extension_point('PAGE_STRUCTURE_HEADER', '',
  array(
    'category_id' => $category_id,
    'clang' => $clang
  )
);

echo '
<!-- *** OUTPUT CATEGORIES - START *** -->';
// --------------------- KATEGORIE QUERY LIST

$KAT = rex_sql::factory();
// $KAT->debugsql = true;
if(count($mountpoints)>0 && $category_id == 0)
{
  $re_id = 'id='. implode(' OR id=', $mountpoints);
  $KAT->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE ('.$re_id.') AND startpage=1 AND clang='. $clang .' ORDER BY catname');
}else
{
  $KAT->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE re_id='. $category_id .' AND startpage=1 AND clang='. $clang .' ORDER BY catprior');
}



// --------------------- KATEGORIE LIST

for ($i = 0; $i < $KAT->getRows(); $i++)
{}

// Kategorie SELECT BOX
////////////////////////////////////////////////////////////////////////////////
$id = 1;                                                      // ID dieser Select Box
$tmp = new rex_select();                                      // rex_select Objekt initialisieren
$tmp->setSize(1);                                             // 1 Zeilen = normale Selectbox
$tmp->setName('SELECT['.$id.']');
$tmp->setSelected($myREX['settings']['SELECT'][$id]);         // gespeicherte Werte einsetzen

$select = $tmp->get();                                        // HTML in Variable speichern


echo '
<!-- *** OUTPUT CATEGORIES - END *** -->
';

// --------------------------------------------- ARTIKEL LISTE

echo '
<!-- *** OUTPUT ARTICLES - START *** -->';

// --------------------- READ TEMPLATES

if ($category_id > 0 || ($category_id == 0 && !$REX["USER"]->hasMountpoints()))
{

  $template_select = new rex_select;
  $template_select->setName('template_id');
  $template_select->setId('rex-form-template');
  $template_select->setSize(1);

  $templates = OOCategory::getTemplates($category_id);
  if(count($templates)>0)
  {
    foreach($templates as $t_id => $t_name)
    {
      $template_select->addOption(rex_translate($t_name, null, false), $t_id);
      $TEMPLATE_NAME[$t_id] = rex_translate($t_name);
    }
  }else
  {
    $template_select->addOption($I18N->msg('option_no_template'), '0');
    $TEMPLATE_NAME[0] = $I18N->msg('template_default_name');
  }

  // --------------------- ARTIKEL LIST
  $art_add_link = '';

  $add_head = '';
  $add_col  = '';
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
        <colgroup>
          <col width="40" />
          '. $add_col .'
          <col width="*" />
          <col width="40" />
          <col width="200" />
          <col width="115" />
          <col width="51" />
          <col width="50" />
          <col width="50" />
        </colgroup>
        <thead>
          <tr>
            <th class="rex-icon">'. $art_add_link .'</th>
            '. $add_head .'
            <th>'.$I18N->msg('header_article_name').'</th>
            <th>'.$I18N->msg('header_priority').'</th>
           <th>'.$I18N->msg('header_status').'</th>
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







    // ----- hat rechte an diesem artikel

    // ------------------------------------------ Slice add/edit/delete
    #if (rex_request('save', 'boolean') && in_array($function, array('add', 'edit', 'delete', 'add')))
    {
      // ----- check module


      $CM = rex_sql::factory();
      if ($function == 'edit' || $function == 'delete')
      {
        // edit/ delete
        $CM->setQuery("SELECT * FROM " . $REX['TABLE_PREFIX'] . "article_slice LEFT JOIN " . $REX['TABLE_PREFIX'] . "module ON " . $REX['TABLE_PREFIX'] . "article_slice.modultyp_id=" . $REX['TABLE_PREFIX'] . "module.id WHERE " . $REX['TABLE_PREFIX'] . "article_slice.id='$slice_id' AND clang=$clang");
        if ($CM->getRows() == 1)
          $module_id = $CM->getValue("" . $REX['TABLE_PREFIX'] . "article_slice.modultyp_id");
      }else
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
              if(rex_deleteSlice($slice_id))
              {
                $global_info = $I18N->msg('block_deleted');
                  
                // ----- EXTENSION POINT
                $global_info = rex_register_extension_point('SLICE_DELETED', $global_info,
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
              {
                $global_warning = $I18N->msg('block_not_deleted');
              }
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
    }




    $listElements = array();

    if ($mode == 'edit')
    {
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=edit&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '" class="rex-active"'. rex_tabindex() .'>' . $I18N->msg('edit_mode') . '</a>';
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=meta&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '"'. rex_tabindex() .'>' . $I18N->msg('metadata') . '</a>';
    }
    else
    {
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=edit&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '"'. rex_tabindex() .'>' . $I18N->msg('edit_mode') . '</a>';
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=meta&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '" class="rex-active"'. rex_tabindex() .'>' . $I18N->msg('metadata') . '</a>';
    }

    $listElements[] = '<a href="../' . rex_getUrl($article_id,$clang) . '" onclick="window.open(this.href); return false;" '. rex_tabindex() .'>' . $I18N->msg('show') . '</a>';

    // ----- EXTENSION POINT
    $listElements = rex_register_extension_point('PAGE_CONTENT_MENU', $listElements,
      array(
        'article_id' => $article_id,
        'clang' => $clang,
        'function' => $function,
        'mode' => $mode,
        'slice_id' => $slice_id
      )
    );

    $menu = "\n".'<ul class="rex-navi-content">';
    $num_elements = count($listElements);
    $menu_first = true;
    for($i = 0; $i < $num_elements; $i++)
    {
      $class = '';
      if($menu_first)
        $class = ' class="rex-navi-first"';

      $menu .= '<li'.$class.'>'. $listElements[$i] .'</li>';

      $menu_first = false;
    }
    $menu .= '</ul>';

    // ------------------------------------------ END: CONTENT HEAD MENUE

    // ------------------------------------------ START: AUSGABE
    echo '
            <!-- *** OUTPUT OF ARTICLE-CONTENT - START *** -->
            <div class="rex-content-header">
            <div class="rex-content-header-2">
              ' . $menu . '
              <div class="rex-clearer"></div>
            </div>
            </div>
            ';

    // ------------------------------------------ WARNING
    if($global_warning != '')
    {
      echo rex_warning($global_warning);
    }
    if($global_info != '')
    {
      echo rex_info($global_info);
    }
    if ($mode != 'edit')
    {
      if($warning != '')
      {
        echo rex_warning($warning);
      }
      if($info != '')
      {
        echo rex_info($info);
      }
    }

    echo '
            <div class="rex-content-body">
            <div class="rex-content-body-2">
            ';


    if ($mode == 'edit'  || $function == 'add')
    {
      // ------------------------------------------ START: MODULE EDITIEREN/ADDEN ETC.

// aus structure.inc / #106
if (1==0 AND rex_post('artadd_function', 'boolean') && $category_id !== '' && $KATPERM &&  !$REX['USER']->hasPerm('editContentOnly[]'))
{
  // --------------------- ARTIKEL ADD
  $data = array();
  $data['prior']       = rex_post('Position_New_Article', 'int');
  $data['name']        = rex_post('article_name', 'string');
  $data['template_id'] = rex_post('template_id', 'rex-template-id');
  $data['category_id'] = $category_id;
  $data['path']        = $KATPATH;

  list($success, $message) = rex_addArticle($data);

  if($success)
    $info = $message;
    
    }
// erstmal nur zum merken, sollte mit EP funktionieren

//EP-Ansatz

//erstmal data-array anlegen
  $data = array();
  $data['prior']       = 1;
  $data['name']        = "TEST";#rex_post('article_name', 'string');
  $data['template_id'] = $template_id;# rex_post('template_id', 'rex-template-id');
  $data['category_id'] = $category_id;
  $data['path']        = $KATPATH;

  list($success, $message) = rex_addArticle($data);
// jetzt die daten aus dem editor hinterherschieben
rex_register_extension('ART_UPDATED', 'putArticleOnline');


      echo '
                  <!-- *** OUTPUT OF ARTICLE-CONTENT-EDIT-MODE - START *** -->
                  <div class="rex-content-editmode">
                  ';
      $CONT = new rex_article_editor();
      $CONT->getContentAsQuery();
      $CONT->info = $info;
      $CONT->warning = $warning;
      $CONT->template_attributes = $template_attributes;
      $CONT->setArticleId($article_id);
      $CONT->setSliceId($slice_id);
      $CONT->setMode($mode);
      $CONT->setCLang($clang);
      $CONT->setEval(TRUE);
      $CONT->setSliceRevision($slice_revision);
      $CONT->setFunction('add');
      #echo $CONT->getArticle($ctype);
krumo($CONT);
      echo '
                  </div>
                  <!-- *** OUTPUT OF ARTICLE-CONTENT-EDIT-MODE - END *** -->
                  ';
      // ------------------------------------------ END: MODULE EDITIEREN/ADDEN ETC.

    }




  }

  // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
  if($sql->getRows() > 0 || $function == 'add')
  {
    echo '
        </tbody>';
  }

  echo '
      </table>';

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
  </div>';
  }
}


echo '
<!-- *** OUTPUT ARTICLES - END *** -->
';