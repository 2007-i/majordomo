<?php
/**
 * GPS Track 
 *
 * App_gpstrack
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @author Denis Lutsenko <palacex@gmail.com>
 * @version 0.3 (wizard, 14:35:59 [Aug 07, 2015])
 */


class app_gpstrack extends module
{
   const GPS_DEVICE_TYPE = 'GPS';

   /**
    * Module class constructor
    */
   public function __construct()
   {
      $this->name            = "app_gpstrack";
      $this->title           = "<#LANG_APP_GPSTRACK#>";
      $this->module_category = "<#LANG_SECTION_APPLICATIONS#>";

      $this->checkInstalled();
   }

   /**
    * Saving module parameters
    * @param mixed $data params
    * @return string
    */
   function saveParams($data = 0)
   {
      $p = array();

      if (isset($this->id))
         $p["id"] = $this->id;

      if (isset($this->view_mode))
         $p["view_mode"] = $this->view_mode;
      
      if (isset($this->edit_mode))
         $p["edit_mode"] = $this->edit_mode;
      
      if (isset($this->data_source))
         $p["data_source"] = $this->data_source;
      
      if (isset($this->tab))
         $p["tab"] = $this->tab;
      
      return parent::saveParams($p);
   }
   
   /**
    * Getting module parameters from query string
    * @return void
    */
   function getParams()
   {
      global $id;
      global $mode;
      global $view_mode;
      global $edit_mode;
      global $data_source;
      global $tab;

      if (isset($id))
         $this->id = $id;

      if (isset($mode))
         $this->mode = $mode;

      if (isset($view_mode))
         $this->view_mode = $view_mode;

      if (isset($edit_mode))
         $this->edit_mode = $edit_mode;

      if (isset($data_source))
         $this->data_source = $data_source;

      if (isset($tab))
         $this->tab = $tab;
   }

   /**
    * Run
    * @return void
    */
   public function run()
   {
      global $session;

      $out = array();
      
      if ($this->action == 'admin')
      {
         $this->admin($out);
      }
      else
      {
         $this->usual($out);
      }

      if (isset($this->owner->action))
         $out['PARENT_ACTION'] = $this->owner->action;

      if (isset($this->owner->name))
         $out['PARENT_NAME'] = $this->owner->name;
      
      $out['VIEW_MODE']   = $this->view_mode;
      $out['EDIT_MODE']   = $this->edit_mode;
      $out['MODE']        = $this->mode;
      $out['ACTION']      = $this->action;
      $out['DATA_SOURCE'] = $this->data_source;
      $out['TAB']         = $this->tab;
      
      if (isset($this->device_id))
         $out['IS_SET_DEVICE_ID'] = 1;

      if (isset($this->location_id))
         $out['IS_SET_LOCATION_ID'] = 1;

      if (isset($this->user_id))
         $out['IS_SET_USER_ID'] = 1;

      if (IsSet($this->script_id))
         $out['IS_SET_SCRIPT_ID'] = 1;
      
      if ($this->single_rec)
         $out['SINGLE_REC'] = 1;
      
      $this->data = $out;
      
      $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
      
      $this->result = $p->result;
   }

   /**
    * Module backend
    * @param mixed $out array
    * @return void
    */
   function admin(&$out)
   {
      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source'])
      {
         $out['SET_DATASOURCE'] = 1;
      }

      if ($this->data_source == 'gpslog' || $this->data_source == '')
      {
         if ($this->view_mode == '' || $this->view_mode == 'search_gpslog')
         {
            $this->search_gpslog($out);
         }
         
         if ($this->view_mode == 'edit_gpslog')
         {
            $this->edit_gpslog($out, $this->id);
         }

         if ($this->view_mode == 'delete_gpslog')
         {
            $this->delete_gpslog($this->id);
            $this->redirect("?data_source=gpslog");
         }
      }

      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source'])
      {
         $out['SET_DATASOURCE'] = 1;
      }

      if ($this->data_source == 'gpslocations')
      {
         if ($this->view_mode == '' || $this->view_mode == 'search_gpslocations')
         {
            $this->search_gpslocations($out);
         }

         if ($this->view_mode == 'edit_gpslocations')
         {
            $this->edit_gpslocations($out, $this->id);
         }

         if ($this->view_mode == 'delete_gpslocations')
         {
            $this->delete_gpslocations($this->id);
            $this->redirect("?data_source=gpslocations");
         }
      }

      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source'])
      {
         $out['SET_DATASOURCE'] = 1;
      }

      if ($this->data_source == 'gpsdevices')
      {
         if ($this->view_mode == '' || $this->view_mode == 'search_gpsdevices')
         {
            $this->search_gpsdevices($out);
         }

         if ($this->view_mode == 'edit_gpsdevices')
         {
            $this->edit_gpsdevices($out, $this->id);
         }

         if ($this->view_mode == 'delete_gpsdevices')
         {
            $this->delete_gpsdevices($this->id);
            $this->redirect("?data_source=gpsdevices");
         }
      }

      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source'])
      {
         $out['SET_DATASOURCE'] = 1;
      }

      if ($this->data_source == 'gpsactions')
      {
         if ($this->view_mode == '' || $this->view_mode == 'search_gpsactions')
         {
            $this->search_gpsactions($out);
         }

         if ($this->view_mode == 'edit_gpsactions')
         {
            $this->edit_gpsactions($out, $this->id);
         }

         if ($this->view_mode == 'delete_gpsactions')
         {
            $this->delete_gpsactions($this->id);
            $this->redirect("?data_source=gpsactions");
         }
      }
   }

   /**
    * usual
    * @param mixed $out data
    * @return void
    */
   public function usual(&$out)
   {
      require(DIR_MODULES . $this->name . '/usual.inc.php');
   }

   /**
    * gpslog search
    * @param mixed $out data
    * @return void
    */
   public function search_gpslog(&$out)
   {
      require(DIR_MODULES  .$this->name . '/gpslog_search.inc.php');
   }

   /**
    * gpslog edit/add
    * @param mixed $out data
    * @param mixed $id  log id
    * @return void
    */
   function edit_gpslog(&$out, $id)
   {
      require(DIR_MODULES . $this->name . '/gpslog_edit.inc.php');
   }

   /**
    * gpslog delete record
    * @param mixed $id Log ID
    * @return void
    */
   public function delete_gpslog($id)
   {
      $gpsLog   = $this->SelectGpsHistoryByID($id);
      $gpsLogID = $gpsLog['REC_ID']; 

      if (!$this->IsNullOrEmptyString($gpsLogID))
      {
         $sqlQuery = "delete
                        from GPS_HISTORY
                       where REC_ID = " . $gpsLogID;

         SQLExec($sqlQuery);
      }
   }

   /**
    * gpslocations search
    * @param mixed $out array
    */
   function search_gpslocations(&$out)
   {
      require(DIR_MODULES . $this->name . '/gpslocations_search.inc.php');
   }
   
   /**
    * gpslocations edit/add
    * @param mixed $out Array
    * @param mixed $id Location ID
    */
   public function edit_gpslocations(&$out, $id)
   {
      require(DIR_MODULES . $this->name . '/gpslocations_edit.inc.php');
   }
   
   /**
    * gpslocations delete record
    * @param mixed $id Location ID
    */
   public function delete_gpslocations($id)
   {
      if (!$this->IsNullOrEmptyString($id))
      {
         $poi = $this->GetLocationByID($id);
         $poiID = $poi['POI_ID'];

         if (!$this->IsNullOrEmptyString($poiID))
         {
            $sqlQuery = "delete
                           from GPS_ACTION
                          where POI_ID = " . $poiID;
            SQLExec($sqlQuery);

            $sqlQuery = "delete
                           from GPS_LOCATION
                          where POI_ID = " . $poiID;

            SQLExec($sqlQuery);
         }
      }
   }
   
   /**
    * gpsdevices search
    * @param mixed $out array
    */
   public function search_gpsdevices(&$out)
   {
      require(DIR_MODULES . $this->name . '/gpsdevices_search.inc.php');
   }

   /**
    * gpsdevices edit/add
    * @param mixed $out array
    * @param mixed $id Device ID
    */
   function edit_gpsdevices(&$out, $id)
   {
      require(DIR_MODULES . $this->name . '/gpsdevices_edit.inc.php');
   }
   
   /**
    * set flag delete to device
    * @param mixed $id DeviceID
    */
   function delete_gpsdevices($id)
   {
      $sqlQuery = "select *
                     from DEVICE
                    where DEVICE_ID = " . $id;
      $rec = SQLSelectOne($sqlQuery);

      if (!$this->IsNullOrEmptyString($rec['DEVICE_ID']))
      {
         $sqlQuery = "update DEVICE
                         set FLAG_DEL = 'Y',
                             LM_DATE  = NOW()
                       where DEVICE_ID = " . $id;
         SQLExec($sqlQuery);
      }
   }

   /**
    * search_gpsactions
    * @param mixed $out array
    */
   public function search_gpsactions(&$out)
   {
      require(DIR_MODULES.$this->name . '/gpsactions_search.inc.php');
   }
   
   /**
    * Add/Edit form
    * @param mixed $out array
    * @param mixed $id Action ID
    */
   public function edit_gpsactions(&$out, $id)
   {
      require(DIR_MODULES . $this->name . '/gpsactions_edit.inc.php');
   }

   /**
    * Delete gps action
    * @param mixed $id Action ID
    */
   public function delete_gpsactions($id)
   {
      $action = $this->GetActionByID($id);
      $actionID = $action['ACTION_ID'];

      if (!$this->IsNullOrEmptyString($actionID))
      {
         $sqlQuery = "delete
                        from GPS_ACTION
                       where ACTION_ID = " . $actionID;

         SQLExec($sqlQuery);
      }
   }
   /**
    * Install
    *
    * Module installation routine
    *
    * @access private
    */
   function install($data='') {
      parent::install();
   }
   /**
    * Uninstall
    *
    * Module uninstall routine
    *
    * @access public
    */
   function uninstall() {
      SQLExec('DROP TABLE IF EXISTS gpslog');
      SQLExec('DROP TABLE IF EXISTS gpslocations');
      SQLExec('DROP TABLE IF EXISTS gpsdevices');
      SQLExec('DROP TABLE IF EXISTS gpsactions');
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
      gpslog - Log
      gpslocations - Locations
      gpsdevices - Devices
      gpsactions - Actions
       */
      $data = <<<EOD
 gpslog: ID int(10) unsigned NOT NULL auto_increment
 gpslog: ADDED datetime
 gpslog: LAT float DEFAULT '0' NOT NULL
 gpslog: LON float DEFAULT '0' NOT NULL
 gpslog: ALT float DEFAULT '0' NOT NULL
 gpslog: PROVIDER varchar(30) NOT NULL DEFAULT ''
 gpslog: SPEED float DEFAULT '0' NOT NULL
 gpslog: BATTLEVEL int(3) NOT NULL DEFAULT '0'
 gpslog: CHARGING int(3) NOT NULL DEFAULT '0'
 gpslog: DEVICEID varchar(255) NOT NULL DEFAULT ''
 gpslog: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 gpslog: LOCATION_ID int(10) NOT NULL DEFAULT '0'
 gpslog: ACCURACY float DEFAULT '0' NOT NULL
 gpslog: INDEX (DEVICE_ID)
 gpslog: INDEX (LOCATION_ID)

 gpslocations: ID int(10) unsigned NOT NULL auto_increment
 gpslocations: TITLE varchar(255) NOT NULL DEFAULT ''
 gpslocations: LAT float DEFAULT '0' NOT NULL
 gpslocations: LON float DEFAULT '0' NOT NULL
 gpslocations: RANGE float DEFAULT '0' NOT NULL
 gpslocations: VIRTUAL_USER_ID int(10) NOT NULL DEFAULT '0'

 gpsdevices: ID int(10) unsigned NOT NULL auto_increment
 gpsdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 gpsdevices: USER_ID int(10) NOT NULL DEFAULT '0'
 gpsdevices: LAT varchar(255) NOT NULL DEFAULT ''
 gpsdevices: LON varchar(255) NOT NULL DEFAULT ''
 gpsdevices: UPDATED datetime
 gpsdevices: DEVICEID varchar(255) NOT NULL DEFAULT ''
 gpsdevices: TOKEN varchar(255) NOT NULL DEFAULT ''
 gpsdevices: INDEX (USER_ID)

 gpsactions: ID int(10) unsigned NOT NULL auto_increment
 gpsactions: LOCATION_ID int(10) NOT NULL DEFAULT '0'
 gpsactions: USER_ID int(10) NOT NULL DEFAULT '0'
 gpsactions: ACTION_TYPE int(255) NOT NULL DEFAULT '0'
 gpsactions: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 gpsactions: CODE text
 gpsactions: LOG text
 gpsactions: EXECUTED datetime
 gpsactions: INDEX (LOCATION_ID)
 gpsactions: INDEX (USER_ID)
EOD;
      parent::dbInstall($data);
   }

   /**
    * Select all gps action types
    * @return array
    */
   public function SelectActionType()
   {
      $sqlQuery = "select TYPE_ID, TYPE_NAME, LM_DATE, TYPE_DESC
                     from GPS_ACTION_TYPE";

      $actTypes = SQLSelect($sqlQuery);

      return $actTypes;
   }

   /**
    * Select gps device type
    * @return mixed
    */
   public function GetDeviceType()
   {
      $sqlQuery = "select TYPE_ID
                     from DEVICE_TYPE
                    where TYPE_NAME = '" . self::GPS_DEVICE_TYPE . "'";

      $deviceType = SQLSelectOne($sqlQuery);
      
      return $deviceType['TYPE_ID'];
   }

   /**
    * Select locations by custom query 
    * @param mixed $query   query
    * @param mixed $orderBy sort options
    * @return array
    */
   public function SelectLocations($query, $orderBy)
   {
      $sqlQuery = "select POI_ID, POI_NAME, POI_LAT, POI_LNG, LM_DATE, POI_RANGE
                     from GPS_LOCATION
                    where " . $query . "
                    order by " . $orderBy;

      $locations = SQLSelect($sqlQuery);

      return $locations;
   }

   /**
    * Select location by ID
    * @param mixed $locationID Location ID
    * @return array
    */
   public function GetLocationByID($locationID)
   {
      $sqlQuery = "select POI_ID, POI_NAME, POI_LAT, POI_LNG, LM_DATE, POI_RANGE
                     from GPS_LOCATION
                    where POI_ID = " . $locationID;
      
      $location = SQLSelectOne($sqlQuery);

      return $location;
   }

   /**
    * Add new location
    * @param mixed $object Data array
    * @return int
    */
   public function SetLocation($object)
   {
      $poiID    = $this->GetGpsLocationMaxID() + 1;
      $poiName  = $object['POI_NAME'];
      $poiLat   = $object['POI_LAT'];
      $poiLng   = $object['POI_LNG'];
      $poiRange = $object['POI_RANGE'];

      if ($this->IsNullOrEmptyString($poiName))
         throw new Exception('Location name (POI_NAME) is null');

      if ($this->IsNullOrEmptyString($poiLat))
         throw new Exception('Location latitude (POI_LAT) is null');

      if (!is_float($poiLat))
         throw new Exception('Location latitude (POI_LAT) is not float value');

      if ($this->IsNullOrEmptyString($poiLng))
         throw new Exception('Location longitude (POI_LNG) is null');

      if (!is_float($poiLng))
         throw new Exception('Location longitude (POI_LNG) is not float value');

      if (!$this->IsNullOrEmptyString($poiRange) && !is_numeric($poiRange))
         throw new Exception('Wrong range (POI_RANGE)');


      $location["POI_ID"]    = $poiID;
      $location["POI_NAME"]  = $poiName;
      $location['POI_LAT']   = $poiLat;
      $location['POI_LNG']   = $poiLng;
      $location["LM_DATE"]   = date("Y-m-d H:i:s");
      $location["POI_RANGE"] = $poiRange;
      


      $locationID = SQLInsert('GPS_LOCATION', $location);

      return $locationID;
   }

   /**
    * Update location
    * @param mixed $object Data array
    */
   public function UpdateLocation($object)
   {
      SQLUpdate('GPS_LOCATION', $object, 'POI_ID');
   }

   /**
    * Add new action
    * @param mixed $object Data array
    * @return int
    */
   public function SetAction($object)
   {
      $actionID     = $this->GetGpsActionMaxID() + 1;
      $poiID        = $object['POI_ID'];
      $deviceID     = $object['DEVICE_ID'];
      $actionTypeID = $object['TYPE_ID'];

      if ($this->IsNullOrEmptyString($poiID))
         throw new Exception('Location (POI_ID) is null');

      if ($this->IsNullOrEmptyString($deviceID))
         throw new Exception('Device ID (DEVICE_ID) is null');

      if ($this->IsNullOrEmptyString($actionTypeID))
         throw new Exception('Action type (TYPE_ID) is null');

      $action['ACTION_ID'] = $actionID;
      $action['POI_ID']    = $poiID;
      $action['DEVICE_ID'] = $deviceID;
      $action['TYPE_ID']   = $actionTypeID;
      $action['SCRIPT_ID'] = $object['SCRIPT_ID'];
      $action['CODE']      = $object['CODE'];
      $action['LOG']       = $object['LOG'];
      $action['EXECUTED']  = $object['EXECUTED'];

      SQLInsert('GPS_ACTION', $action);
      
      return $actionID;
   }

   /**
    * Update action
    * @param mixed $object Data array
    */
   public function UpdateAction($object)
   {
      if ($this->IsNullOrEmptyString($object['ACTION_ID']))
         throw new Exception('ACTION_ID is null');
      
      $action['ACTION_ID'] = $object['ACTION_ID'];
      
      if ($this->IsNullOrEmptyString($object['POI_ID']))
         throw new Exception('POI_ID is null');

      $action['POI_ID'] = $object['POI_ID'];

      if ($this->IsNullOrEmptyString($object['DEVICE_ID']))
         throw new Exception('DEVICE_ID is null');

      $action['DEVICE_ID'] = $object['DEVICE_ID'];

      if ($this->IsNullOrEmptyString($object['TYPE_ID']))
         throw new Exception('TYPE_ID is null');

      $action['TYPE_ID']   = $object['TYPE_ID'];
      $action['SCRIPT_ID'] = $object['SCRIPT_ID'];
      $action['CODE']      = $object['CODE'];
      $action['LOG']       = $object['LOG'];
      $action['EXECUTED']  = $object['EXECUTED'];

      SQLUpdate('GPS_ACTION', $action, 'ACTION_ID');
   }

   /**
    * Select action by ID
    * @param mixed $locationID Location ID
    * @return array
    */
   public function GetActionByID($actionID)
   {
      $sqlQuery = "select a.ACTION_ID, a.POI_ID, l.POI_NAME, d.DEVICE_ID, d.DEVICE_NAME, a.TYPE_ID, t.TYPE_NAME, a.SCRIPT_ID, a.CODE, a.LOG, a.EXECUTED,
                          d.USER_ID, (select USERNAME
                                        from users u
                                       where u.ID = d.USER_ID
                                     ) USER_NAME
                     from GPS_ACTION a, GPS_LOCATION l, DEVICE d, GPS_ACTION_TYPE t
                    where a.POI_ID    = l.POI_ID
                      and a.DEVICE_ID = d.DEVICE_ID
                      and a.TYPE_ID   = t.TYPE_ID
                      and a.ACTION_ID = " . $actionID;

      $action = SQLSelectOne($sqlQuery);

      return $action;
   }

   /**
    * Select Device Info by ID
    * @param mixed $deviceID Device ID
    * @return array|bool
    */
   public function GetDeviceByID($deviceID)
   {
      if ($this->IsNullOrEmptyString($deviceID))
         return false;

      $sqlQuery = "select d.DEVICE_ID, d.TYPE_ID, d.DEVICE_NAME, d.DEVICE_CODE, d.USER_ID, g.LATITUDE, g.LONGITUDE, g.LM_DATE
                     from DEVICE d
                     left join GPS_DEVICE g on (g.DEVICE_ID = d.DEVICE_ID)
                    where d.DEVICE_ID = " . $deviceID;

      $device = SQLSelectOne($sqlQuery);

      return $device;
   }

   private function GetDeviceMaxID()
   {
      $sqlQuery = "select max(DEVICE_ID) DEVICE_ID
                     from DEVICE";

      $device = SQLSelectOne($sqlQuery);

      return $device['DEVICE_ID'];
   }

   /**
    * Get max action id
    * @return mixed
    */
   private function GetGpsActionMaxID()
   {
      $sqlQuery = "select max(ACTION_ID) ACTION_ID
                     from GPS_ACTION";

      $action = SQLSelectOne($sqlQuery);

      return $action['ACTION_ID'];
   }

   /**
    * Get max location ID
    * @return mixed
    */
   private function GetGpsLocationMaxID()
   {
      $sqlQuery = "select max(POI_ID) POI_ID
                     from GPS_LOCATION";

      $action = SQLSelectOne($sqlQuery);

      return $action['POI_ID'];
   }
   
   public function SetGpsDevice($rec)
   {
      $deviceID     = $this->GetDeviceMaxID() + 1;
      $deviceTypeID = $this->GetDeviceType();
      $deviceName   = $rec['DEVICE_NAME'];
      $deviceCode   = $rec['DEVICE_CODE'];
      $userID       = $rec['USER_ID'];
      $recDate      = date("Y-m-d H:i:s");
      
      if ($this->IsNullOrEmptyString($deviceTypeID))
         throw new Exception('Device Type (TYPE_ID) is null');
      
      if ($this->IsNullOrEmptyString($deviceName))
         throw new Exception('Device Name (DEVICE_NAME) is null');

      if ($this->IsNullOrEmptyString($deviceCode))
         throw new Exception('Device CODE (DEVICE_CODE) is null');

      if ($this->IsNullOrEmptyString($userID))
         throw new Exception('User ID (USER_ID) is null');

      if ($this->IsDeviceByCode($deviceCode))
      {
         throw new Exception('Device (' . $deviceCode . ') already exist');
      }

      $device['TYPE_ID']     = $deviceTypeID;
      $device['DEVICE_NAME'] = $deviceName;
      $device['DEVICE_CODE'] = $deviceCode;
      $device['USER_ID']     = $userID;
      $device['LM_DATE']     = $recDate;
      $device['FLAG_DEL']    = 'N';
      $device['FLAG_GPS']    = 'Y';
      $device['DEVICE_ID']   = $deviceID;

      $deviceID = SQLInsert('DEVICE', $device);

      return $deviceID;
   }

   /**
    * Update Gps device
    * @param mixed $deviceID    Device ID
    * @param mixed $deviceName  Device Name
    * @param mixed $userID      User ID
    * @return int
    */
   public function UpdateGpsDevice($deviceID, $deviceName, $deviceCode, $userID)
   {
      $deviceTypeID = $this->GetDeviceType();

      if ($this->IsNullOrEmptyString($deviceID))
         throw new Exception('Device ID (DEVICE_ID) is null');

      if ($this->IsNullOrEmptyString($deviceTypeID))
         throw new Exception('Device Type (TYPE_ID) is null');
      
      if ($this->IsNullOrEmptyString($deviceName))
         throw new Exception('Device Name (DEVICE_NAME) is null');

      if ($this->IsNullOrEmptyString($deviceCode))
         throw new Exception('Device CODE (DEVICE_CODE) is null');

      if ($this->IsNullOrEmptyString($userID))
         throw new Exception('User ID (USER_ID) is null');

      $obj["TYPE_ID"]     = $deviceTypeID;
      $obj["DEVICE_NAME"] = $deviceName;
      $obj["USER_ID"]     = $userID;
      $obj["DEVICE_ID"]   = $deviceID;
      $obj["LM_DATE"]     = date("Y-m-d H:i:s");

      $isUpdated = SQLUpdate("DEVICE", $obj, "DEVICE_ID");

      return $isUpdated;
   }

   public function SelectGpsDevices()
   {
      $sqlQuery = "select d.DEVICE_ID, d.TYPE_ID, d.DEVICE_NAME, d.DEVICE_CODE, d.USER_ID, u.NAME USER_NAME, g.LATITUDE, g.LONGITUDE, g.LM_DATE
                     from DEVICE d
                     left join GPS_DEVICE g on (g.DEVICE_ID = d.DEVICE_ID)
                     left join users u on (d.USER_ID = u.ID)
                    where d.FLAG_DEL = 'N'
                      and d.FLAG_GPS = 'Y'
                    order by DEVICE_NAME desc";
      
      $device = SQLSelect($sqlQuery);

      return $device;
   }

   /**
    * List of gps actions
    */
   public function SelectGpsActions()
   {
      $sqlQuery = "select a.ACTION_ID, a.POI_ID, l.POI_NAME, d.DEVICE_ID, d.DEVICE_NAME, a.TYPE_ID, t.TYPE_NAME, a.SCRIPT_ID, a.CODE, a.LOG, a.EXECUTED,
                          d.USER_ID, (select USERNAME
                                        from users u
                                       where u.ID = d.USER_ID
                                     ) USER_NAME
                     from GPS_ACTION a, GPS_LOCATION l, DEVICE d, GPS_ACTION_TYPE t
                    where a.POI_ID    = l.POI_ID
                      and a.DEVICE_ID = d.DEVICE_ID
                      and a.TYPE_ID   = t.TYPE_ID
                    order by EXECUTED desc";

      $actions = SQLSelect($sqlQuery);

      return $actions;
   }

   /**
    * Check for device code exists
    * @param mixed $deviceCode Device Code
    * @return bool
    */
   public function IsDeviceByCode($deviceCode)
   {
      $sqlQuery = "select DEVICE_ID
                     from DEVICE
                    where DEVICE_CODE = '" . $deviceCode . "'";
      
      $device = SQLSelectOne($sqlQuery);
      
      if (!$this->IsNullOrEmptyString($device['DEVICE_ID']))
         return true;
      
      return false;
   }

   /**
    * Summary of SelectGpsHistory
    * @param mixed $recID GPS History ID
    * @return array
    */
   public function SelectGpsHistoryByID($recID)
   {
      if ($this->IsNullOrEmptyString($recID))
         return array();

      $sqlQuery = "select h.REC_ID, h.REC_DATE, h.DEVICE_ID, d.DEVICE_NAME, h.LATITUDE, h.LONGITUDE, h.LM_DATE, h.ALTITUDE, h.PROVIDER, 
                          h.SPEED, h.BATTERY_LEVEL, h.BATTERY_STATUS, h.ACCURACY
                     from GPS_HISTORY h
                     left join GPS_DEVICE d on (d.DEVICE_ID = h.DEVICE_ID)
                    where h.REC_ID = " . $recID;

      $history = SQLSelectOne($sqlQuery);

      return $history;
   }

   /**
    * Delete all GPS history
    */
   public function DeleteGpsHistory()
   {
      $sqlQuery = "delete from GPS_HISTORY";
      SQLExec($sqlQuery);
   }


   public function IsNullOrEmptyString($value)
   {
      return (!isset($value) || trim($value) === '');
   }

   /**
    * Return error message
    * @param mixed $ex Exception
    * @return string
    */
   public function GetExceptionMessage($ex)
   {
      return 'Exception code: ' . $ex->getCode() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine() . ' Message: ' . $ex->getMessage();
   }

   /**
    * Check for valid gps coordinates
    * @param mixed $latitude  Latitude
    * @param mixed $longitude Longitude
    * @return bool
    */
   public function IsValidGpsLocation($latitude, $longitude)
   {
      if($this->IsNullOrEmptyString($latitude) || $this->IsNullOrEmptyString($longitude))
         return false;

      if(!is_float($latitude) || !is_float($longitude))
         return false;

      return true;
   }

}
