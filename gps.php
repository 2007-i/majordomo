<?php

/**
 * Main project script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.1
 */

include_once("./config.php");
include_once("./lib/loader.php");

/*
Big Brother GPS format

time:         Client Time
latitude:     Latitude(decimal degree)
longitude:    Longitude(decimal degree)
accuracy:     Accuracy(m)
altitude:     Altitude(m)
provider:     Provider
bearing:      Bearing(degrees)
speed:        Speed(m/s)
battlevel:    percentage
charging:     Charging(0 or 1)
secret:       Secret (string)
deviceid:     Device ID
subscriberid: Subscriber ID

 */

// start calculation of execution time
startMeasure('TOTAL');

include_once(DIR_MODULES . "application.class.php");

$session = new session("prj");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

if (isset($_REQUEST['location']))
{
   $tmp = explode(',', $_REQUEST['location']);
   
   $_REQUEST['latitude']  = $tmp[0];
   $_REQUEST['longitude'] = $tmp[1];
}

$gps = new app_gpstrack();

$latitude  = $_REQUEST['latitude'];
$longitude = $_REQUEST['longitude'];


$isValidLocation = $gps->IsValidGpsLocation($_REQUEST['latitude'], $_REQUEST['longitude']);


if (!$gps->IsNullOrEmptyString($_REQUEST['op']))
{
   $op = $_REQUEST['op'];
   $ok = false;

   $jsonErrorMessage = json_encode(array('RESULT' => array('STATUS' => 'FAIL')));
   
   try
   {
      if ($op == 'zones')
      {
         $zones = $gps->SelectLocations(null, null);
         echo json_encode(array('RESULT' => array('ZONES' => $zones, 'STATUS' => 'OK')));
         $ok = true;
      }

      if ($op == 'add_zone' && $isValidLocation)
      {
         global $title;
         global $range;
         
         if (!$range)
            $range = $gps->GetDefaultGpsRange();

         $rec = array();

         $rec['POI_NAME']  = $title;
         $rec['POI_LAT']   = (float)$latitude;
         $rec['POI_LNG']   = (float)$longitude;
         $rec['POI_RANGE'] = (int)$range;
         
         $locationID = $gps->SetLocation($rec);

         echo json_encode(array('RESULT' => array('STATUS' => 'OK')));

         $ok = true;
      }

      if ($op == 'set_token' && isset($_REQUEST['token']) && !$gps->IsNullOrEmptyString($_REQUEST['deviceid']))
      {
         $deviceCode  = $_REQUEST['deviceid'];
         $deviceToken = $_REQUEST['token'];
         $deviceID    = $gps->GetDeviceIDByCode($deviceCode);
         $userID      = 0;
         
         if ($deviceID == -1)
         {
            $device = array();
            $device['DEVICE_NAME']  = $deviceCode;
            $device['DEVICE_CODE']  = $deviceCode;
            $device['DEVICE_TOKEN'] = $deviceToken;
            $device['USER_ID']      = $userID;

            $deviceID = $gps->SetGpsDevice($device);
         }
         else
         {
            $gps->UpdateDeviceToken($deviceID, $deviceToken);
         }

         $ok = true;
      }
   
      if ($ok == false)
         echo $jsonErrorMessage;
   }
   catch(Exception $ex)
   {
      DebMes($gps->GetExceptionMessage($ex));
      echo $jsonErrorMessage;
   }

   $db->Disconnect();
   exit;
}

if ($isValidLocation)
{
   $deviceBuf = $gps->PrepareGPSDeviceParam($_REQUEST);

   // check for device exists
   $deviceID = $gps->GetDeviceIDByCode($deviceBuf['DEVICE_CODE']);

   if ($deviceID != -1)
   {
      $curDevicePosition = $gps->GetGpsCurrentPosition($deviceID);
      $isUpdated = $gps->UpdateGpsDeviceInfo($deviceBuf);

      if ($isUpdated)
      {
         $device = $gps->GetDeviceByID($deviceID);

         $distance = $gps->GetDistanceBetweenPoints($device['LATITUDE'], $device['LONGITUDE'], $curDevicePosition['LATITUDE'], $curDevicePosition['LONGITUDE']);
         
         if ($distance > $gps->GetMinGpsCoordDistance())
         {
            if (!$gps->IsNullOrEmptyString($device['LINKED_OBJECT']))
            {
               setGlobal($device['LINKED_OBJECT'] . '.Coordinates', $device['LATITUDE'] . ',' . $device['LONGITUDE']);
               setGlobal($device['LINKED_OBJECT'] . '.CoordinatesUpdated', date('H:i'));
               setGlobal($device['LINKED_OBJECT'] . '.CoordinatesUpdatedTimestamp', time());
               setGlobal($device['LINKED_OBJECT'] . '.BattLevel', $deviceBuf['BATTERY_LEVEL']);
               setGlobal($device['LINKED_OBJECT'] . '.Charging', $deviceBuf['BATTERY_STATUS']);

               //we're moving
               $objectIsMoving = $device['LINKED_OBJECT'] . '.isMoving';

               setGlobal($objectIsMoving, 1);
               clearTimeOut($device['LINKED_OBJECT'] . '_moving');

               // stopped after 15 minutes of inactivity
               setTimeOut($device['LINKED_OBJECT'] . '_moving', "setGlobal('" . $objectIsMoving . "', 0);", 15 * 60);
            }
            checkLocation();
         }
      }
   }
   else
   {
      $gps->AddDeviceToBuf($deviceBuf);
   }
}

function checkLocation()
{
   // checking locations
   $locations = $gps->SelectLocations(null,null);
   $total     = count($locations);

   $location_found = 0;
   
   for ($i = 0; $i < $total; $i++)
   {
      if (!$locations[$i]['POI_RANGE'])
         $locations[$i]['POI_RANGE'] = $gps->GetDefaultGpsRange();
      
      $distance = $gps->GetDistanceBetweenPoints($latitude, $longitude, $locations[$i]['POI_LAT'], $locations[$i]['POI_LNG']);
      
      if ($distance <= $locations[$i]['POI_RANGE'])
      {
         $location_found = 1;
         
         if ($user['LINKED_OBJECT'])
            setGlobal($user['LINKED_OBJECT'] . '.seenAt', $locations[$i]['POI_NAME']);
         
         // we are at location
         $rec['LOCATION_ID'] = $locations[$i]['POI_ID'];
         
         SQLUpdate('gpslog', $rec);

         $sqlQuery = "SELECT *
                        FROM gpslog
                       WHERE DEVICE_ID = '" . $device['ID'] . "'
                         AND ID        != '" . $rec['ID'] . "'
                       ORDER BY ADDED DESC
                       LIMIT 1";

         $tmp = SQLSelectOne($sqlQuery);
         
         if ($tmp['POI_ID'] != $locations[$i]['POI_ID'])
         {
            // entered location
            $sqlQuery = "SELECT *
                           FROM gpsactions
                          WHERE LOCATION_ID = '" . $locations[$i]['ID'] . "'
                            AND ACTION_TYPE = 1
                            AND USER_ID     = '" . $device['USER_ID'] . "'";

            $gpsaction = SQLSelectOne($sqlQuery);
            
            if ($gpsaction['ID'])
            {
               $gpsaction['EXECUTED'] = date('Y-m-d H:i:s');
               $gpsaction['LOG']      = $gpsaction['EXECUTED'] . " Executed\n" . $gpsaction['LOG'];
               
               SQLUpdate('gpsactions', $gpsaction);
               
               if ($gpsaction['SCRIPT_ID'])
               {
                  runScript($gpsaction['SCRIPT_ID']);
               }
               elseif ($gpsaction['CODE'])
               {
                  try
                  {
                     $code    = $gpsaction['CODE'];
                     $success = eval($code);

                     if ($success === false)
                     {
                        DebMes("Error in GPS action code: " . $code);
                        registerError('gps_action', "Code execution error: " . $code);
                     }
                  }
                  catch (Exception $e)
                  {
                     DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
                     registerError('gps_action', get_class($e) . ', ' . $e->getMessage());
                  }
               }
            }
         }
      }
      else
      {
         $sqlQuery = "SELECT *
                        FROM gpslog
                       WHERE DEVICE_ID = '" . $device['ID'] . "'
                         AND ID        != '" . $rec['ID'] . "'
                       ORDER BY ADDED DESC
                       LIMIT 1";

         $tmp = SQLSelectOne($sqlQuery);
         
         if ($tmp['LOCATION_ID'] == $locations[$i]['ID'])
         {
            //Debmes("Device (" . $device['TITLE'] . ") LEFT location " . $locations[$i]['TITLE']);
            
            // left location
            $sqlQuery = "SELECT *
                           FROM gpsactions
                          WHERE LOCATION_ID = '" . $locations[$i]['ID'] . "'
                            AND ACTION_TYPE = 0
                            AND USER_ID     = '" . $device['USER_ID'] . "'";
            
            $gpsaction = SQLSelectOne($sqlQuery);
            
            if ($gpsaction['ID'])
            {
               $gpsaction['EXECUTED'] = date('Y-m-d H:i:s');
               $gpsaction['LOG']      = $gpsaction['EXECUTED'] . " Executed\n" . $gpsaction['LOG'];
               
               SQLUpdate('gpsactions', $gpsaction);
               
               if ($gpsaction['SCRIPT_ID'])
               {
                  runScript($gpsaction['SCRIPT_ID']);
               }
               elseif ($gpsaction['CODE'])
               {
                  try
                  {
                     $code    = $gpsaction['CODE'];
                     $success = eval($code);
                     
                     if ($success === false)
                        DebMes("Error in GPS action code: " . $code);
                  }
                  catch (Exception $e)
                  {
                     DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
                  }
               }
            }
         }
      }
   }

}

if ($user['LINKED_OBJECT'] && !$location_found)
   setGlobal($user['LINKED_OBJECT'] . '.seenAt', '');

$sqlQuery = "SELECT MESSAGE, DATE_FORMAT(ADDED, '%H:%i') as DAT
               FROM shouts
              ORDER BY ADDED DESC
              LIMIT 1";

$tmp = SQLSelectOne($sqlQuery);

if (!headers_sent())
{
   header("HTTP/1.0: 200 OK\n");
   header('Content-Type: text/html; charset=utf-8');
}

if (defined('BTRACED'))
{
   echo "OK";
}
elseif ($tmp['MESSAGE'] != '')
{
   echo ' ' . $tmp['DAT'] . ' ' . transliterate($tmp['MESSAGE']);
}

// closing database connection
$db->Disconnect();

endMeasure('TOTAL'); // end calculation of execution time

