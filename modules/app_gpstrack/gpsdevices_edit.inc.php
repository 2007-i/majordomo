<?php

if ($this->owner->name=='panel')
{
   $out['CONTROLPANEL'] = 1;
}

$rec = $this->GetDeviceByID($id);

if ($this->mode == 'update')
{
   $ok = 1;

   global $title;
   $rec['DEVICE_NAME'] = $title;
   
   if ($rec['DEVICE_NAME'] == '')
   {
      $out['ERR_TITLE'] = 1;
      $ok = 0;
   }

   if (isset($this->user_id))
   {
      $rec['USER_ID'] = $this->user_id;
   }
   else
   {
      global $user_id;
      $rec['USER_ID'] = $user_id;
   }

   global $device_code;
   $rec['DEVICE_CODE'] = $device_code;
   if ($rec['DEVICE_CODE'] == '')
   {
      $out['ERR_DEVICE_CODE'] = 1;
      $ok = 0;
   }

   $rec['LM_DATE'] = date("Y-m-d H:i:s");
   
   if ($ok)
   {
      if (isset($rec['DEVICE_ID']) && is_numeric($rec['DEVICE_ID']))
      {
         $isUpdated = $this->UpdateGpsDevice($rec['DEVICE_ID'], $rec['DEVICE_NAME'], $rec['DEVICE_CODE'], $rec['USER_ID']);
      }
      else
      {
         $new_rec = 1;

         $rec['DEVICE_ID'] = $this->SetGpsDevice($rec);
      }

      $out['OK'] = 1;
   }
   else
   {
      $out['ERR'] = 1;
   }
}

//options for 'USER_ID' (select)
$sqlQuery = "SELECT ID, NAME
               FROM users
              ORDER BY NAME";

$tmp         = SQLSelect($sqlQuery);
$users_total = count($tmp);

for($users_i = 0; $users_i < $users_total; $users_i++)
{
   $user_id_opt[$tmp[$users_i]['ID']] = $tmp[$users_i]['NAME'];

   if ($rec['USER_ID'] == $tmp[$users_i]['ID'])
      $tmp[$users_i]['SELECTED'] = 1;
}

$out['USER_ID_OPTIONS'] = $tmp;

if (is_array($rec))
{
   foreach($rec as $k => $v)
   {
      if (!is_array($v))
      {
         $rec[$k] = htmlspecialchars($v);
      }
   }
}

outHash($rec, $out);
