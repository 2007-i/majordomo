<?php

if ($this->owner->name == 'panel')
{
   $out['CONTROLPANEL']=1;
}

if (isset($id) && !empty($id)) 
{
   $rec = $this->GetActionByID($id);
}

if ($this->mode == 'update')
{
   $ok = 1;

   //updating 'LOCATION_ID' (select)
   if (isset($this->location_id))
   {
      $rec['POI_ID'] = $this->location_id;
   }
   else
   {
      global $location_id;
      $rec['POI_ID'] = $location_id;
   }

   //updating 'USER_ID' (select)
   //if (isset($this->user_id))
   //{
   //   $rec['USER_ID'] = $this->user_id;
   //}
   //else
   //{
   //   global $user_id;
   //   $rec['USER_ID'] = $user_id;
   //}

   //updating 'ACTION_TYPE' (select)
   global $action_type;
   $rec['TYPE_ID'] = $action_type;
   
   //updating 'SCRIPT_ID' (select)
   if (isset($this->script_id))
   {
      $rec['SCRIPT_ID'] = $this->script_id;
   }
   else
   {
      global $script_id;
      $rec['SCRIPT_ID'] = $script_id;
   }

   //updating 'CODE' (text)
   global $code;
   $rec['CODE'] = $code;
   
   //UPDATING RECORD
   if ($ok)
   {
      if (isset($rec['ACTION_ID']))
      {
         $this->UpdateAction($rec);
      }
      else
      {
         DebMes("1. SET");
         $new_rec = 1;
         $rec['ACTION_ID'] = $this->SetAction($rec);
      }
      $out['OK'] = 1;
   }
   else
   {
      $out['ERR']=1;
   }
}


//options for 'LOCATION_ID' (select)
$tmp = $this->SelectLocations('1', "POI_NAME");
//$tmp=SQLSelect("SELECT ID, TITLE FROM gpslocations ORDER BY TITLE");
$gpslocations_total = count($tmp);
for($gpslocations_i = 0; $gpslocations_i < $gpslocations_total; $gpslocations_i++)
{
   $location_id_opt[$tmp[$gpslocations_i]['POI_ID']] = $tmp[$gpslocations_i]['POI_NAME'];
}

for($i = 0; $i < count($tmp); $i++)
{
   if ($rec['LOCATION_ID'] == $tmp[$i]['POI_ID'])
      $tmp[$i]['SELECTED'] = 1;
}

$out['LOCATION_ID_OPTIONS'] = $tmp;


//options for 'USER_ID' (select)
$tmp = SQLSelect("SELECT ID, NAME FROM users ORDER BY NAME");
$users_total = count($tmp);
for($users_i = 0; $users_i < $users_total; $users_i++)
{
   $user_id_opt[$tmp[$users_i]['ID']] = $tmp[$users_i]['NAME'];
}

for($i = 0; $i < count($tmp); $i++)
{
   if ($rec['USER_ID'] == $tmp[$i]['ID'])
      $tmp[$i]['SELECTED'] = 1;
}

$out['USER_ID_OPTIONS']=$tmp;


//options for 'ACTION_TYPE' (select)
$out['ACTION_TYPE_OPTIONS'] = $this->SelectActionType();

$actTypeCnt = count($out['ACTION_TYPE_OPTIONS']);

for($i = 0; $i < $actTypeCnt; $i++)
{
   $action_type_opt[$out['ACTION_TYPE_OPTIONS'][$i]['TYPE_ID']] = $out['ACTION_TYPE_OPTIONS'][$i]['TYPE_NAME'];

   if ($out['ACTION_TYPE_OPTIONS'][$i]['TYPE_NAME'] == $rec['ACTION_TYPE'])
   {
      $out['ACTION_TYPE_OPTIONS'][$i]['SELECTED'] = 1;
      
      $out['ACTION_TYPE'] = $out['ACTION_TYPE_OPTIONS'][$i]['TYPE_NAME'];
      $rec['ACTION_TYPE'] = $out['ACTION_TYPE_OPTIONS'][$i]['TYPE_NAME'];
   }
}



//options for 'SCRIPT_ID' (select)
$tmp = SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
$scripts_total=count($tmp);
for($scripts_i=0;$scripts_i<$scripts_total;$scripts_i++) {
   $script_id_opt[$tmp[$scripts_i]['ID']]=$tmp[$scripts_i]['TITLE'];
}
for($i=0;$i<count($tmp);$i++) {
   if ($rec['SCRIPT_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
}
$out['SCRIPT_ID_OPTIONS']=$tmp;
if ($rec['EXECUTED']!='') {
   $tmp=explode(' ', $rec['EXECUTED']);
   $out['EXECUTED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $executed_hours=$tmp2[0];
   $executed_minutes=$tmp2[1];
}
for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$executed_minutes) {
      $out['EXECUTED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
      $out['EXECUTED_MINUTES'][]=array('TITLE'=>$title);
   }
}
for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$executed_hours) {
      $out['EXECUTED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
      $out['EXECUTED_HOURS'][]=array('TITLE'=>$title);
   }
}
if (is_array($rec)) {
   foreach($rec as $k=>$v) {
      if (!is_array($v)) {
         $rec[$k]=htmlspecialchars($v);
      }
   }
}
outHash($rec, $out);
?>