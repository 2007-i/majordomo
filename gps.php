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

$isValidLocation = $gps->IsValidGpsLocation($_REQUEST['latitude'], $_REQUEST['latitude']);


if (!$gps->IsNullOrEmptyString($_REQUEST['op']))
{
   $op = $_REQUEST['op'];
   
   $ok = false;
   
   if ($op == 'zones')
   {
      $zones = $gps->SelectLocations(null, null);
      echo json_encode(array('RESULT' => array('ZONES' => $zones, 'STATUS' => 'OK')));
      $ok = true;
   }

   if ($op == 'add_zone' && $isValidLocation)
   {
      try
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
      catch(Exception $ex)
      {
         DebMes($gps->GetExceptionMessage($ex));
      }
   }

   if ($op == 'set_token' && isset($_REQUEST['token']) && !$gps->IsNullOrEmptyString($_REQUEST['deviceid']))
   {
      try
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
      catch(Exception $ex)
      {
         $message = $gps->GetExceptionMessage($ex);
         DebMes($message);
      }
   }
   
   if ($ok == false)
      echo json_encode(array('RESULT' => array('STATUS' => 'FAIL')));
   
   $db->Disconnect();
   exit;
}

if ($isValidLocation)
{
   $latitude  = (float)$_REQUEST['latitude'];
   $longitude = (float)$_REQUEST['longitude'];

   //DebMes("GPS DATA RECEIVED: \n".serialize($_REQUEST));
   if (!$gps->IsNullOrEmptyString($_REQUEST['deviceid']))
   {
      $isDeviceExist   = $gps->IsDeviceByCode($_REQUEST['deviceid']);
      
      if (!$isDeviceExist)
      {
         $device = array();

         $device['DEVICEID'] = $_REQUEST['deviceid'];
         $device['TITLE']    = 'New GPS Device';

         if (!$gps->IsNullOrEmptyString($_REQUEST['token']))
            $device['TOKEN'] = $_REQUEST['token'];
         
         $device['ID'] = SQLInsert('gpsdevices', $device);
         
         $sqlQuery = "UPDATE gpslog
                         SET DEVICE_ID = '" . $device['ID'] . "'
                       WHERE DEVICEID = '" . DBSafe($_REQUEST['deviceid']) . "'";
         
         SQLExec($sqlQuery);
      }
      
      $device['LAT']     = $latitude;
      $device['LON']     = $longitude;
      $device['UPDATED'] = date('Y-m-d H:i:s');
      
      SQLUpdate('gpsdevices', $device);
   }

   $rec = array();
   
   $rec['ADDED']     = ($time) ? $time : date('Y-m-d H:i:s');
   $rec['LAT']       = $latitude;
   $rec['LON']       = $longitude;
   $rec['ALT']       = round($_REQUEST['altitude'], 2);
   $rec['PROVIDER']  = $_REQUEST['provider'];
   $rec['SPEED']     = round($_REQUEST['speed'], 2);
   $rec['BATTLEVEL'] = $_REQUEST['battlevel'];
   $rec['CHARGING']  = (int)$_REQUEST['charging'];
   $rec['DEVICEID']  = $_REQUEST['deviceid'];
   $rec['ACCURACY']  = isset($_REQUEST['accuracy']) ? $_REQUEST['accuracy'] : 0;

   if ($device['ID'])
      $rec['DEVICE_ID'] = $device['ID'];
   
   $rec['ID'] = SQLInsert('gpslog', $rec);

   if ($device['USER_ID'])
   {
      $sqlQuery = "SELECT *
                     FROM users
                    WHERE ID = '" . $device['USER_ID'] . "'";
      
      $user = SQLSelectOne($sqlQuery);

      if ($user['LINKED_OBJECT'])
      {
         setGlobal($user['LINKED_OBJECT'] . '.Coordinates', $rec['LAT'] . ',' . $rec['LON']);
         setGlobal($user['LINKED_OBJECT'] . '.CoordinatesUpdated', date('H:i'));
         setGlobal($user['LINKED_OBJECT'] . '.CoordinatesUpdatedTimestamp', time());
         setGlobal($user['LINKED_OBJECT'] . '.BattLevel', $rec['BATTLEVEL']);
         setGlobal($user['LINKED_OBJECT'] . '.Charging', $rec['CHARGING']);
         
         $sqlQuery = "SELECT *
                        FROM gpslog
                       WHERE ID        != '" . $rec['ID'] . "'
                         AND DEVICE_ID = '" . $device['ID'] . "'
                       ORDER BY ID DESC
                       LIMIT 1";

         $prev_log = SQLSelectOne($sqlQuery);

         if ($prev_log['ID'])
         {
            $distance = $gps->GetDistanceBetweenPoints($rec['LAT'], $rec['LON'], $prev_log['LAT'], $prev_log['LON']);
            
            if ($distance > 100)
            {
               //we're moving
               $objectIsMoving = $user['LINKED_OBJECT'] . '.isMoving';

               setGlobal($objectIsMoving, 1);
               clearTimeOut($user['LINKED_OBJECT'] . '_moving');
               
               // stopped after 15 minutes of inactivity
               setTimeOut($user['LINKED_OBJECT'] . '_moving', "setGlobal('" . $objectIsMoving . "', 0);", 15 * 60);
            }
         }
      }
   }

   // checking locations
   $locations = SQLSelect("SELECT * FROM gpslocations");
   $total     = count($locations);

   $location_found = 0;
   
   for ($i = 0; $i < $total; $i++)
   {
      if (!$locations[$i]['RANGE'])
         $locations[$i]['RANGE'] = $gps->GetDefaultGpsRange();
      
      $distance = $gps->GetDistanceBetweenPoints($latitude, $longitude, $locations[$i]['LAT'], $locations[$i]['LON']);
      
      //echo ' (' . $locations[$i]['LAT'] . ' : ' . $locations[$i]['LON'] . ') ' . $distance . ' m';
      if ($distance <= $locations[$i]['RANGE'])
      {
         //Debmes("Device (" . $device['TITLE'] . ") NEAR location " . $locations[$i]['TITLE']);
         $location_found = 1;
         
         if ($user['LINKED_OBJECT'])
            setGlobal($user['LINKED_OBJECT'] . '.seenAt', $locations[$i]['TITLE']);
         
         // we are at location
         $rec['LOCATION_ID'] = $locations[$i]['ID'];
         
         SQLUpdate('gpslog', $rec);

         $sqlQuery = "SELECT *
                        FROM gpslog
                       WHERE DEVICE_ID = '" . $device['ID'] . "'
                         AND ID        != '" . $rec['ID'] . "'
                       ORDER BY ADDED DESC
                       LIMIT 1";

         $tmp = SQLSelectOne($sqlQuery);
         
         if ($tmp['LOCATION_ID'] != $locations[$i]['ID'])
         {
            //Debmes("Device (" . $device['TITLE'] . ") ENTERED location " . $locations[$i]['TITLE']);
            
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

$sqlQuery = "SELECT *, DATE_FORMAT(ADDED, '%H:%i') as DAT
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

