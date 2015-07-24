<?php
/**
 * GPS Track 
 *
 * App_gpstrack
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.2 (wizard, 14:07:59 [Jul 25, 2011])
 */
Define('DEF_ACTION_TYPE_OPTIONS', '1=Entering|0=Leaving'); // options for 'ACTION_TYPE'

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
   function delete_gpslog($id)
   {
      $sqlQuery = "SELECT *
                     FROM gpslog
                    WHERE ID = '$id'";

      $rec = SQLSelectOne($sqlQuery);

      // some action for related tables
      SQLExec("DELETE FROM gpslog WHERE ID='" . $rec['ID'] . "'");
   }

   /**
    * gpslocations search
    *
    * @access public
    */
   function search_gpslocations(&$out) {
      require(DIR_MODULES.$this->name.'/gpslocations_search.inc.php');
   }
   /**
    * gpslocations edit/add
    *
    * @access public
    */
   function edit_gpslocations(&$out, $id) {
      require(DIR_MODULES.$this->name.'/gpslocations_edit.inc.php');
   }
   /**
    * gpslocations delete record
    *
    * @access public
    */
   function delete_gpslocations($id) {
      $rec=SQLSelectOne("SELECT * FROM gpslocations WHERE ID='$id'");
      // some action for related tables
      SQLExec("DELETE FROM gpslocations WHERE ID='".$rec['ID']."'");
   }
   /**
    * gpsdevices search
    *
    * @access public
    */
   function search_gpsdevices(&$out) {
      require(DIR_MODULES.$this->name.'/gpsdevices_search.inc.php');
   }
   /**
    * gpsdevices edit/add
    *
    * @access public
    */
   function edit_gpsdevices(&$out, $id) {
      require(DIR_MODULES.$this->name.'/gpsdevices_edit.inc.php');
   }
   /**
    * gpsdevices delete record
    *
    * @access public
    */
   function delete_gpsdevices($id) {
      $rec=SQLSelectOne("SELECT * FROM gpsdevices WHERE ID='$id'");
      // some action for related tables
      SQLExec("DELETE FROM gpslog WHERE DEVICE_ID='".$rec['ID']."'");
      SQLExec("DELETE FROM gpsdevices WHERE ID='".$rec['ID']."'");
   }
   /**
    * gpsactions search
    *
    * @access public
    */
   function search_gpsactions(&$out) {
      require(DIR_MODULES.$this->name.'/gpsactions_search.inc.php');
   }
   /**
    * gpsactions edit/add
    *
    * @access public
    */
   function edit_gpsactions(&$out, $id) {
      require(DIR_MODULES.$this->name.'/gpsactions_edit.inc.php');
   }
   /**
    * gpsactions delete record
    *
    * @access public
    */
   function delete_gpsactions($id) {
      $rec=SQLSelectOne("SELECT * FROM gpsactions WHERE ID='$id'");
      // some action for related tables
      SQLExec("DELETE FROM gpsactions WHERE ID='".$rec['ID']."'");
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
      $locationID = SQLInsert('GPS_LOCATION', $object);

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

      DebMes($object);
      $actitonID = SQLInsert('GPS_ACTION', $object);
      DebMes("set: " . $actitonID);
      return $actitonID;
   }

   /**
    * Update action
    * @param mixed $object Data array
    */
   public function UpdateAction($object)
   {
      SQLUpdate('GPS_ACTION', $object, 'ACTION_ID');
   }

   /**
    * Select action by ID
    * @param mixed $locationID Location ID
    * @return array
    */
   public function GetActionByID($actionID)
   {
      $sqlQuery = "select POI_ID, POI_NAME, POI_LAT, POI_LNG, LM_DATE, POI_RANGE
                     from GPS_LOCATION
                    where POI_ID = " . $actionID;
      
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
      if (!isset($deviceID) || empty($deviceID))
         return false;

      $sqlQuery = "select d.DEVICE_ID, d.TYPE_ID, d.DEVICE_NAME, d.DEVICE_CODE, d.USER_ID, g.LATITUDE, g.LONGITUDE, g.LM_DATE
                     from DEVICE d
                     left join GPS_DEVICE g on (g.DEVICE_ID = d.DEVICE_ID)
                    where d.DEVICE_ID = " . $deviceID;
      
      $device = SQLSelectOne($sqlQuery);

      return $device;
   }


   public function SetGpsDevice($rec)
   {
      $deviceTypeID = $this->GetDeviceType();
      DebMes("type: " . $deviceTypeID);
      $rec['TYPE_ID'] = $deviceTypeID;
      
      $deviceID = SQLInsert('DEVICE', $rec);

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
      $sqlQuery = "select d.DEVICE_ID, d.TYPE_ID, d.DEVICE_NAME, d.DEVICE_CODE, d.USER_ID, g.LATITUDE, g.LONGITUDE, g.LM_DATE
                     from DEVICE d
                     left join GPS_DEVICE g on (g.DEVICE_ID = d.DEVICE_ID)
                    order by DEVICE_NAME desc";
      
      $device = SQLSelect($sqlQuery);

      return $device;
   }
   

}
/*
 *
 * TW9kdWxlIGNyZWF0ZWQgSnVsIDI1LCAyMDExIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
 *
 */
?>