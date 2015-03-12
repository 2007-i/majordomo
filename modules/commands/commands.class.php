<?php
/**
* Commands 
*
* Commands
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.5 (wizard, 17:04:46 [Apr 09, 2009])
*/
//
//
class commands extends module {
/**
* commands
*
* Module class constructor
*
* @access private
*/
function commands() {
  $this->name="commands";
  $this->title="<#LANG_MODULE_CONTROL_MENU#>";
  $this->module_category="<#LANG_SECTION_OBJECTS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $data=array();
 if (IsSet($this->id)) {
  $data["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $data["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $data["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $data["tab"]=$this->tab;
 }
 if (IsSet($this->parent_item)) {
  $data["parent_item"]=$this->parent_item;
 }

 return parent::saveParams($data);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  startMeasure('menu_template');
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  endMeasure('menu_template');
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {

 global $ajax;
 if ($ajax) {

  global $op;
  global $item_id;


  if ($op=='get_details') {

   startMeasure('getDetails');
   global $labels;
   global $values;



   $res=array();

   //echo "Debug labels: $labels \nValues: $values\n";

   $res['LABELS']=array();
   $labels=explode(',', $labels);
   $total=count($labels);
   $seen=array();
   for($i=0;$i<$total;$i++) {
    $item_id=trim($labels[$i]);
    if (!$item_id || $seen[$item_id]) {
     continue;
    }
    $seen[$item_id]=1;
    $item=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$item_id."'");
    if ($item['ID']) {
     if ($item['TYPE']=='custom') {
      $item['DATA']=processTitle($item['DATA'], $this);
      $data=$item['DATA'];
     } else {
      $item['TITLE']=processTitle($item['TITLE'], $this);
      $data=$item['TITLE'];
     }
     if ($item['RENDER_DATA']!=$item['DATA'] || $item['RENDER_TITLE']!=$item['TITLE']) {
      $tmp=SQLSelectOne("SELECT * FROM commands WHERE ID='".$item['ID']."'");
      $tmp['RENDER_TITLE']=$item['TITLE'];
      $tmp['RENDER_DATA']=$item['DATA'];
      $tmp['RENDER_UPDATED']=date('Y-m-d H:i:s');
      SQLUpdate('commands', $tmp);
     }
     if (preg_match('/#[\w\d]{6}/is', $data, $m)) {
      $color=$m[0];
      $data=trim(str_replace($m[0], '<style>#item'.$item['ID'].' .ui-btn-active {background-color:'.$color.';border-color:'.$color.'}</style>', $data));
     }
     $res['LABELS'][]=array('ID'=>$item['ID'], 'DATA'=>$data);
    }
   }

   $res['VALUES']=array();
   $values=explode(',', $values);
   $total=count($values);
   $seen=array();
   for($i=0;$i<$total;$i++) {
    $item_id=trim($values[$i]);
    if (!$item_id || $seen[$item_id]) {
     continue;
    }
    $seen[$item_id]=1;
    $item=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$item_id."'");
    if ($item['ID']) {
     $data=$item['CUR_VALUE'];
     $res['VALUES'][]=array('ID'=>$item['ID'], 'DATA'=>$data);
    }
   }

   $res['LATEST_REQUEST']=time();
   echo json_encode($res);

   endMeasure('getDetails');
   exit;

  }

  if ($op=='get_label') {
   startMeasure('getLabel');
   $item=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$item_id."'");
   startMeasure('getLabel '.$item['TITLE'], 1);
   if ($item['ID']) {
    $res=array();
    if ($item['TYPE']=='custom') {
     $item['DATA']=processTitle($item['DATA'], $this);
     $res['DATA']=$item['DATA'];
    } else {
     $item['TITLE']=processTitle($item['TITLE'], $this);
     $res['DATA']=$item['TITLE'];
    }

    if ($item['RENDER_DATA']!=$item['DATA'] || $item['RENDER_TITLE']!=$item['TITLE']) {
     $tmp=SQLSelectOne("SELECT * FROM commands WHERE ID='".$item['ID']."'");
     $tmp['RENDER_TITLE']=$item['TITLE'];
     $tmp['RENDER_DATA']=$item['DATA'];
     $tmp['RENDER_UPDATED']=date('Y-m-d H:i:s');
     SQLUpdate('commands', $tmp);
    }
    echo json_encode($res);
   }
   endMeasure('getLabel '.$item['TITLE'], 1);
   endMeasure('getLabel',1);
   exit;   
  }

  if ($op=='get_value') {
   startMeasure('getValue');
   $item=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$item_id."'");
   if ($item['ID']) {
    $res=array();
    $res['DATA']=$item['CUR_VALUE'];
    echo json_encode($res);
   }
   endMeasure('getValue',1);
   exit;   
  }


  if ($op=='value_changed') {
   global $new_value;
   $item=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$item_id."'");
   if ($item['ID']) {
    $old_value=$item['CUR_VALUE'];
    $item['CUR_VALUE']=$new_value;
    SQLUpdate('commands', $item);
    if ($item['LINKED_PROPERTY']!='') {
     //$old_value=gg($item['LINKED_OBJECT'].'.'.$item['LINKED_PROPERTY']);
     sg($item['LINKED_OBJECT'].'.'.$item['LINKED_PROPERTY'], $item['CUR_VALUE'], array($this->name=>'ID!='.$item['ID']));
    }

    $params=array('VALUE'=>$item['CUR_VALUE'], 'OLD_VALUE'=>$old_value);

    if ($item['ONCHANGE_METHOD']!='') {
     if (!$item['LINKED_OBJECT']) {
      $item['LINKED_OBJECT']=$item['ONCHANGE_OBJECT'];
     }
     getObject($item['LINKED_OBJECT'])->callMethod($item['ONCHANGE_METHOD'], $params); //ONCHANGE_OBJECT
     //DebMes("calling method ".$item['ONCHANGE_OBJECT'].".".$item['ONCHANGE_METHOD']." with ".$item['CUR_VALUE']);
    }

    if ($item['SCRIPT_ID']) {
     //DebMes('Running on_change script #'.$item['SCRIPT_ID']);
     runScript($item['SCRIPT_ID'], $params);
    }
    if ($item['CODE']) {
     //DebMes("Running on_change code");
     
                  try {
                   $code=$item['CODE'];
                   $success=eval($code);
                   if ($success===false) {
                    DebMes("Error menu item code: ".$code);
                   }
                  } catch(Exception $e){
                   DebMes('Error: exception '.get_class($e).', '.$e->getMessage().'.');
                  }

    }

   }
   echo "OK";
  }
  exit;

 }



 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='commands' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_commands') {
   startMeasure('searchCommands');
   $this->search_commands($out);
   endMeasure('searchCommands', 1);
  }

  if ($this->view_mode=='moveup' && $this->id) {
   $this->reorder_items($this->id, 'up');
   $this->redirect("?");
  }
  if ($this->view_mode=='movedown' && $this->id) {
   $this->reorder_items($this->id, 'down');
   $this->redirect("?");
  }

  if ($this->view_mode=='edit_commands') {
   $this->edit_commands($out, $this->id);
  }

  if ($this->view_mode=='clone_commands') {
   $rec=SQLSelectOne("SELECT * FROM commands WHERE ID='".$this->id."'");
   unset($rec['ID']);
   $rec['TITLE']=$rec['TITLE'].' (copy)';
   $rec['ID']=SQLInsert('commands', $rec);
   $this->redirect("?id=".$rec['ID']."&view_mode=edit_commands");
  }

  if ($this->view_mode=='delete_commands') {
   $this->delete_commands($this->id);
   $this->redirect("?");
  }
 }
}

 function reorder_items($id, $direction='up') {
  $element=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$id."'");
  if ($element['PARENT_ID']) {
   $all_elements=SQLSelect("SELECT * FROM commands WHERE PARENT_ID=".$element['PARENT_ID']." ORDER BY PRIORITY DESC, TITLE");
  } else {
   $all_elements=SQLSelect("SELECT * FROM commands WHERE PARENT_ID=0 ORDER BY PRIORITY DESC, TITLE");
  }
  $total=count($all_elements);
  for($i=0;$i<$total;$i++) {
   if ($all_elements[$i]['ID']==$id && $i>0 && $direction=='up') {
    $tmp=$all_elements[$i-1];
    $all_elements[$i-1]=$all_elements[$i];
    $all_elements[$i]=$tmp;
    break;
   }
   if ($all_elements[$i]['ID']==$id && $i<($total-1) && $direction=='down') {
    $tmp=$all_elements[$i+1];
    $all_elements[$i+1]=$all_elements[$i];
    $all_elements[$i]=$tmp;
    break;
   }
  }
  $priority=($total)*10;
  for($i=0;$i<$total;$i++) {
   $all_elements[$i]['PRIORITY']=$priority;
   $priority-=10;
   SQLUpdate('commands', $all_elements[$i]);
  }
 }


/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* commands search
*
* @access public
*/
 function search_commands(&$out) {
  require(DIR_MODULES.$this->name.'/commands_search.inc.php');
 }
/**
* commands edit/add
*
* @access public
*/
 function edit_commands(&$out, $id) {
  require(DIR_MODULES.$this->name.'/commands_edit.inc.php');
 }

 function buildHTML($result) {
  require(DIR_MODULES.$this->name.'/commands_html.inc.php');
  return $res;
 }



/**
* commands delete record
*
* @access public
*/
 function delete_commands($id) {
  $rec=SQLSelectOne("SELECT * FROM commands WHERE ID='$id'");
  // some action for related tables
   $tmp=SQLSelectOne("SELECT ID FROM commands WHERE PARENT_ID=".$rec['ID']);
   if ($tmp['ID']) {
    return;
   }

  SQLExec("DELETE FROM commands WHERE ID='".$rec['ID']."'");
 }
/**
* commands build tree
*
* @access private
*/
 function buildTree_commands($res, $parent_id=0, $level=0) {
  $total=count($res);
  $res2=array();
  for($i=0;$i<$total;$i++) {
   if ($res[$i]['PARENT_ID']==$parent_id) {
    $res[$i]['LEVEL']=$level;
    $res[$i]['RESULT']=$this->buildTree_commands($res, $res[$i]['ID'], ($level+1));
    $res2[]=$res[$i];
   }
  }
  $total2=count($res2);
  if ($total2) {
   return $res2;
  }
 }
/**
* commands update tree
*
* @access private
*/
 function updateTree_commands($parent_id=0, $parent_list='') {
  $table='commands';
  if (!is_array($parent_list)) {
   $parent_list=array();
  }
  $sub_list=array();
  $res=SQLSelect("SELECT * FROM $table WHERE PARENT_ID='$parent_id'");
  $total=count($res);
  for($i=0;$i<$total;$i++) {
   if ($parent_list[0]) {
    $res[$i]['PARENT_LIST']=implode(',', $parent_list);
   } else {
    $res[$i]['PARENT_LIST']='0';
   }
   $sub_list[]=$res[$i]['ID'];
   $tmp_parent=$parent_list;
   $tmp_parent[]=$res[$i]['ID'];
   $sub_this=$this->updateTree_commands($res[$i]['ID'], $tmp_parent);
   if ($sub_this[0]) {
    $res[$i]['SUB_LIST']=implode(',', $sub_this);
   } else {
    $res[$i]['SUB_LIST']=$res[$i]['ID'];
   }
   SQLUpdate($table, $res[$i]);
   $sub_list=array_merge($sub_list, $sub_this);
  }
  return $sub_list;
 }


  function processMenuElements(&$res) {

   startMeasure('processMenuElements');

   startMeasure('processMenuElements '.$_SERVER['REQUEST_URI']);

   if ($this->action!='admin') {
    $total=count($res);
    $res2=array();
    for($i=0;$i<$total;$i++) {
     if (checkAccess('menu', $res[$i]['ID'])) {
      $res2[]=$res[$i];
     }
    }
    $res=$res2;
    unset($res2);
   }

   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   if ($res[$i+1]['INLINE']) {
    $res[$i]['INLINE']=1;
   }



   $item=$res[$i];
   if ($item['VISIBLE_DELAY']) {
    $out['VISIBLE_DELAYS']++;
   }

   if ($item['EXT_ID'] && $this->action!='admin') {
    $visible_delay=$item['VISIBLE_DELAY'];
    $tmp=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$item['EXT_ID']."'");
    if ($tmp['ID']) {
     $item=$tmp;
     $item['VISIBLE_DELAY']=$visible_delay;
     $res[$i]=$item;
    }
   } elseif ($item['EXT_ID'] && $this->action=='admin') {
    $tmp=SQLSelectOne("SELECT * FROM commands WHERE ID='".(int)$item['EXT_ID']."'");
    if ($tmp['ID']) {
     $item['TITLE']=$item['TITLE'].' ('.$tmp['TITLE'].')';
     $res[$i]=$item;
    }
   }

   if ($item['LINKED_PROPERTY']!='') {
    $lprop=getGlobal($item['LINKED_OBJECT'].'.'.$item['LINKED_PROPERTY']);
    if ($item['TYPE']=='custom') {
     $field='DATA';
    } else {
     $field='CUR_VALUE';
    }
    if ($lprop!=$item[$field]) {
     $item[$field]=$lprop;
     SQLUpdate('commands', $item);
     $res[$i]=$item;
    }
   }

   if ($item['TYPE']=='timebox') {

    $tmp=explode(':', $item['CUR_VALUE']);
    $value1=(int)$tmp[0];
    $value2=(int)$tmp[1];

    for($h=0;$h<=23;$h++) {
     $v=$h;
     if ($v<10) {
      $v='0'.$v;
     }
     $selected=0;
     if ($h==$value1) {
      $selected=1;
     }
     $item['OPTIONS1'][]=array('VALUE'=>$v, 'SELECTED'=>$selected);
    }
    for($h=0;$h<=59;$h++) {
     $v=$h;
     if ($v<10) {
      $v='0'.$v;
     }
     $selected=0;
     if ($h==$value2) {
      $selected=1;
     }
     $item['OPTIONS2'][]=array('VALUE'=>$v, 'SELECTED'=>$selected);
    }
    $res[$i]=$item;
   }

   if ($item['TYPE']=='switch') {
    if (trim($item['DATA'])) {
     $data=explode("\n", str_replace("\r", "", $item['DATA']));
     $item['OFF_VALUE']=trim($data[0]);
     $item['ON_VALUE']=trim($data[1]);
    } else {
     $item['OFF_VALUE']=0;
     $item['ON_VALUE']=1;
    }
    $res[$i]=$item;
   }

   if ($item['TYPE']=='selectbox' || $item['TYPE']=='radiobox') {
    $data=explode("\n", str_replace("\r", "", $item['DATA']));
    $item['OPTIONS']=array();
    $num=1;
    foreach($data as $line) {
     $line=trim($line);
     if ($line!='') {
      $option=array();
      if (preg_match('/=/', $line)) {
       $tmp=explode('=', $line);
      } else {
       $tmp=explode('|', $line);
      }
      $option['VALUE']=$tmp[0];
      if ($tmp[1]!='') {
       $option['TITLE']=$tmp[1];
      } else {
       $option['TITLE']=$option['VALUE'];
      }
      if ($option['VALUE']==$item['CUR_VALUE']) {
       $option['SELECTED']=1;
      }
      $option['NUM']=$num;
      $num++;
      $item['OPTIONS'][]=$option;
     }
    }
    $res[$i]=$item;
   }

   if ($this->owner->name!='panel') {
    $res[$i]['TITLE']=processTitle($res[$i]['TITLE'], $this);
    if ($res[$i]['TYPE']=='custom') {
     $res[$i]['DATA']=processTitle($res[$i]['DATA'], $this);
    }

     if (preg_match('/#[\w\d]{6}/is', $res[$i]['TITLE'], $m)) {
      $color=$m[0];
      $res[$i]['TITLE']=trim(str_replace($m[0], '<style>#item'.$res[$i]['ID'].' .ui-btn-active {background-color:'.$color.';border-color:'.$color.'}</style>', $res[$i]['TITLE']));
     }



    if ($res[$i]['RENDER_TITLE']!=$res[$i]['TITLE'] || $res[$i]['RENDER_DATA']!=$res[$i]['DATA']) {
     $tmp=SQLSelectOne("SELECT * FROM commands WHERE ID='".$res[$i]['ID']."'");
     $tmp['RENDER_TITLE']=$res[$i]['TITLE'];
     $tmp['RENDER_DATA']=$res[$i]['DATA'];
     $tmp['RENDER_UPDATED']=date('Y-m-d H:i:s');
     SQLUpdate('commands', $tmp);
    }


   }

    if (preg_match('/<script/is', $res[$i]['DATA']) && $res[$i]['AUTO_UPDATE']) {
     $res[$i]['AUTO_UPDATE']=0;
    }

    $res[$i]['TITLE_SAFE']=htmlspecialchars($res[$i]['TITLE']);

    /*
    foreach($res[$i] as $k=>$v) {
     if (!is_array($res[$i][$k]) && $k!='DATA') {
      $res[$i][$k]=addslashes($v);
     }
    }
    */

    $tmp=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM commands WHERE PARENT_ID='".$res[$i]['ID']."'");
    if ($tmp['TOTAL']) {
     $res[$i]['RESULT_TOTAL']=$tmp['TOTAL'];
    }


    if ($res[$i]['SUB_PRELOAD'] && $this->action!='admin') {
     $children=SQLSelect("SELECT * FROM commands WHERE PARENT_ID='".$res[$i]['ID']."' ORDER BY PRIORITY DESC, TITLE");
     if ($children[0]['ID']) {
      $this->processMenuElements($children);
      if ($children[0]['ID']) {
       $res[$i]['RESULT']=$children;
      }
     }
    }


   }
   endMeasure('processMenuElements '.$_SERVER['REQUEST_URI'], 1);
   endMeasure('processMenuElements', 1);

  }


/**
* Title
*
* Description
*
* @access public
*/
 function propertySetHandle($object, $property, $value) {
   $commands=SQLSelect("SELECT * FROM commands WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($commands);
   for($i=0;$i<$total;$i++) {
    $commands[$i]['CUR_VALUE']=$value;
    SQLUpdate('commands', $commands[$i]);
   }  
 }

 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function getParents($parent_id) {

   if (!$parent_id) {
    return array();
   }

   $res=array();

   $rec=SQLSelectOne("SELECT * FROM commands WHERE ID='".$parent_id."'");

   if ($rec['PARENT_ID']) {
    $parents=$this->getParents($rec['PARENT_ID']);
    foreach($parents as $v) {
     $res[]=$v;
    }
   }

   $res[]=$rec;

   return $res;
  }

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  parent::install($parent_name);
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS commands');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
commands - Commands
*/
  $data = <<<EOD
 commands: ID int(10) unsigned NOT NULL auto_increment
 commands: TITLE varchar(255) NOT NULL DEFAULT ''
 commands: SYSTEM varchar(255) NOT NULL DEFAULT ''
 commands: COMMAND varchar(255) NOT NULL DEFAULT ''
 commands: URL varchar(255) NOT NULL DEFAULT ''
 commands: TYPE char(50) NOT NULL DEFAULT ''
 commands: WINDOW varchar(255) NOT NULL DEFAULT ''
 commands: WIDTH int(10) NOT NULL DEFAULT '0'
 commands: HEIGHT int(10) NOT NULL DEFAULT '0'
 commands: PARENT_ID int(10) NOT NULL DEFAULT '0'
 commands: PRIORITY int(10) NOT NULL DEFAULT '0'
 commands: MIN_VALUE float(10) NOT NULL DEFAULT '0'
 commands: MAX_VALUE float(10) NOT NULL DEFAULT '0'
 commands: CUR_VALUE varchar(255) NOT NULL DEFAULT '0'
 commands: STEP_VALUE float(10) NOT NULL DEFAULT '1'
 commands: DATA text
 commands: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 commands: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 commands: EXT_ID int(10) NOT NULL DEFAULT '0'
 commands: VISIBLE_DELAY int(10) NOT NULL DEFAULT '0'
 commands: INLINE int(3) NOT NULL DEFAULT '0'
 commands: SUB_PRELOAD int(3) NOT NULL DEFAULT '0'
 commands: RENDER_TITLE varchar(255) NOT NULL DEFAULT ''
 commands: RENDER_DATA text
 commands: RENDER_UPDATED datetime

 commands: ONCHANGE_OBJECT varchar(255) NOT NULL DEFAULT ''
 commands: ONCHANGE_METHOD varchar(255) NOT NULL DEFAULT ''
 commands: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 commands: ICON varchar(50) NOT NULL DEFAULT ''
 commands: CODE text


 commands: SUB_LIST text
 commands: PARENT_LIST text
 commands: AUTOSTART int(3) NOT NULL DEFAULT '0'
 commands: AUTO_UPDATE int(10) NOT NULL DEFAULT '0'
EOD;
  parent::dbInstall($data);

  SQLExec("ALTER TABLE `commands` CHANGE `MIN_VALUE` `MIN_VALUE` FLOAT( 10 ) NOT NULL DEFAULT '0'");
  SQLExec("ALTER TABLE `commands` CHANGE `MAX_VALUE` `MAX_VALUE` FLOAT( 10 ) NOT NULL DEFAULT '0'");
  SQLExec("ALTER TABLE `commands` CHANGE `STEP_VALUE` `STEP_VALUE` FLOAT( 10 ) NOT NULL DEFAULT '0'");

 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDA5LCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>