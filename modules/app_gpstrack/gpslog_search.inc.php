<?php
/*
* @version 0.2 (wizard)
*/

global $clear_log;
if ($clear_log)
{
   $this->DeleteGpsHistory();
   $this->redirect("?");
}

global $optimize_log;

if ($optimize_log)
   exit;

global $session;

if ($this->owner->name == 'panel')
{
   $out['CONTROLPANEL'] = 1;
}
  
$qry = "1";

// search filters
if (isset($this->device_id))
{
   $device_id = $this->device_id;
   $qry .= " AND DEVICE_ID = '" . $this->device_id . "'";
}
else
{
   global $device_id;
}

if (isset($this->location_id))
{
   $location_id = $this->location_id;
   $qry .= " AND LOCATION_ID = '" . $this->location_id . "'";
}
else
{
   global $location_id;
}

// QUERY READY
global $save_qry;

if ($save_qry)
{
   $qry=$session->data['gpslog_qry'];
}
else
{
   $session->data['gpslog_qry'] = $qry;
}

if (!$qry)
   $qry="1";

// FIELDS ORDER
global $sortby_gpslog;

if (!$sortby_gpslog)
{
   $sortby_gpslog = $session->data['gpslog_sort'];
}
else
{
   if ($session->data['gpslog_sort'] == $sortby_gpslog)
   {
      if (is_integer(strpos($sortby_gpslog, ' DESC')))
      {
         $sortby_gpslog = str_replace(' DESC', '', $sortby_gpslog);
      }
      else
      {
         $sortby_gpslog = $sortby_gpslog . " DESC";
      }
   }
   
   $session->data['gpslog_sort'] = $sortby_gpslog;
}

if (!$sortby_gpslog)
   $sortby_gpslog="gpslog.ID DESC";

$out['SORTBY'] = $sortby_gpslog;

// SEARCH RESULTS
$res = SQLSelect("SELECT gpslog.*, gpsdevices.TITLE as DEVICE_TITLE, gpslocations.TITLE as LOCATION_TITLE
                    FROM gpslog
                    LEFT JOIN gpsdevices ON gpsdevices.ID = gpslog.DEVICE_ID
                    LEFT JOIN gpslocations ON gpslocations.ID = gpslog.LOCATION_ID
                   WHERE $qry
                   ORDER BY " . $sortby_gpslog);

if ($res[0]['ID'])
{
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   
   $total = count($res);
   
   for($i = 0; $i < $total; $i++)
   {
      // some action for every record if required
      $tmp = explode(' ', $res[$i]['ADDED']);
      $res[$i]['ADDED'] = fromDBDate($tmp[0]) . " " . $tmp[1];
   }
   
   $out['RESULT'] = $res;
}

?>