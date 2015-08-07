<?php

/**
 * Fill ListBox for POI
 * @param mixed $curPoiID Current POI
 * @return array
 */
function PreparePoiField($curPoiID)
{
   $appGpsTrack = new app_gpstrack();
   $poi      = $appGpsTrack->SelectLocations('1', "POI_NAME");
   $poiCount = count($poi);

   $selectBoxPoi = array();

   for($i = 0; $i < $poiCount; $i++)
   {
      $poiID   = $poi[$i]['POI_ID'];
      $poiName = $poi[$i]['POI_NAME'];
   
      $selectBoxPoi[$i]['POI_ID']   = $poiID;
      $selectBoxPoi[$i]['POI_NAME'] = $poiName;
      
      if ($curPoiID === $poiID)
         $selectBoxPoi[$i]['SELECTED'] = 1;
   }

   return $selectBoxPoi;
}

/**
 * Fill ListBox for Device
 * @param mixed $curDeviceID Current DeviceID
 * @return array
 */
function PrepareDeviceField($curDeviceID)
{
   $appGpsTrack  = new app_gpstrack();
   $devices      = $appGpsTrack->SelectGpsDevices();
   $devicesCount = count($devices);
   
   $selectBoxDevices = array();

   for($i = 0; $i < $devicesCount; $i++)
   {
      $deviceID   = $devices[$i]['DEVICE_ID'];
      $deviceName = $devices[$i]['DEVICE_NAME'];
      $userName   = $devices[$i]['USER_NAME'];

      $selectBoxDevices[$i]['DEVICE_ID']   = $deviceID;
      $selectBoxDevices[$i]['DEVICE_NAME'] = isset($userName) ? $deviceName . " (" . $userName . ")" : $deviceName;
      
      if ($curDeviceID === $deviceID)
         $selectBoxDevices[$i]['SELECTED'] = 1;
   }

   return $selectBoxDevices;
}

/**
 * Fill ListBox for GPS Actions
 * @param mixed $curActionTypeID Current GPS action
 * @return array
 */
function PrepareActionTypeField($curActionTypeID)
{
   $appGpsTrack      = new app_gpstrack();
   $actionTypes      = $appGpsTrack->SelectActionType();
   $actionTypesCount = count($actionTypes);

   $selectBoxActionType = array();
   
   for($i = 0; $i < $actionTypesCount; $i++)
   {
      $actionTypeID   = $actionTypes[$i]['TYPE_ID'];
      $actionTypeName = $actionTypes[$i]['TYPE_NAME'];

      $selectBoxActionType[$i]['TYPE_ID']   = $actionTypeID;
      $selectBoxActionType[$i]['TYPE_NAME'] = $actionTypeName;
      
      if ($curActionTypeID === $actionTypeID)
         $selectBoxActionType[$i]['SELECTED'] = 1;
   }

   return $selectBoxActionType;
}

/**
 * Fill ListBox for Action scripts
 * @param mixed $curScriptID 
 * @return array
 */
function PrepareScriptField($curScriptID)
{
   $sqlQuery = "SELECT ID, TITLE
                  FROM scripts
                 ORDER BY TITLE";

   $script = SQLSelect($sqlQuery);

   $scriptCount     = count($script);
   $selectBoxScript = array();

   for($i = 0; $i < $scriptCount; $i++)
   {
      $scriptID   = $script[$i]['ID'];
      $scriptName = $script[$i]['TITLE'];

      $selectBoxScript[$i]['SCRIPT_ID']   = $scriptID;
      $selectBoxScript[$i]['SCRIPT_NAME'] = $scriptName;

      if ($curScriptID === $scriptID)
         $selectBoxScript[$i]['SELECTED'] = 1;
   }

   return $selectBoxScript;
}
 


if ($this->owner->name == 'panel')
{
   $out['CONTROLPANEL'] = 1;
}

if (!$this->IsNullOrEmptyString($id))
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
   if (isset($this->device_id))
   {
      $rec['DEVICE_ID'] = $this->device_id;
   }
   else
   {
      global $device_id;
      $rec['DEVICE_ID'] = $device_id;
   }

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
      try
      {
         if (!$this->IsNullOrEmptyString($rec['ACTION_ID']))
         {
            $this->UpdateAction($rec);
         }
         else
         {
            $new_rec = 1;
            $gpsActID = $this->SetAction($rec);
            $rec['ACTION_ID'] = $gpsActID;
            $id = $gpsActID;
         }
         
         $out['OK'] = 1;
      }
      catch(Exception $ex)
      {
         $message = $this->GetExceptionMessage($ex);
         DebMes($message,'fatal');

         $out['ERR']         = true;
         $out['ERR_MESSAGE'] = $ex->getMessage();
      }
   }
   else
   {
      $out['ERR'] = 1;
   }
}

$out['POI_SELECT_BOX']         = PreparePoiField($rec['POI_ID']);
$out['DEVICE_SELECT_BOX']      = PrepareDeviceField($rec['DEVICE_ID']);
$out['ACTION_TYPE_SELECT_BOX'] = PrepareActionTypeField($rec['TYPE_ID']);
$out['SCRIPT_SELECT_BOX']      = PrepareScriptField($rec['SCRIPT_ID']);
$out['EXECUTED']               = $rec['EXECUTED'];

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

?>
