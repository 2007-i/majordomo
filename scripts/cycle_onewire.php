<?php

chdir(dirname(__FILE__).'/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 
 
include_once("./load_settings.php");
include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

if (!defined('ONEWIRE_SERVER'))
{
   $db->Disconnect();
   exit;
}

include_once(DIR_MODULES.'onewire/onewire.class.php');

$onw = new onewire();
$cycleName = basename(__FILE__);

echo date("H:i:s") . " running " . $cycleName . "\n";

while(1) 
{
   setGlobal((str_replace('.php', '', $cycleName)).'Run', time(), 1);

   // check all 1wire devices
   $onw->updateDevices(); 
   $onw->updateDisplays();
  
   if (file_exists('./reboot') || $_GET['onetime']) 
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . $cycleName);

?>