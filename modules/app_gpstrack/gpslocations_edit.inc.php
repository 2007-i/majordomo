<?php

if ($this->owner->name == 'panel')
{
   $out['CONTROLPANEL'] = 1;
}

if (isset($id)) 
{
   DebMes("ddd: " . $id);
   $rec = $this->GetLocationByID($id);
}

if ($this->mode == 'update')
{
   $ok=1;
   //updating 'TITLE' (varchar, required)
   global $title;
   $rec['POI_NAME'] = $title;

   if ($rec['POI_NAME'] == '')
   {
      $out['ERR_TITLE'] = 1;
      
      $ok = 0;
   }

   //updating 'LAT' (float, required)
   global $lat;
   $rec['POI_LAT'] = (float)$lat;
   
   //updating 'LON' (float, required)
   global $lon;
   $rec['POI_LNG']=(float)$lon;

//updating 'RANGE' (float, required)
   global $range;
   $rec['POI_RANGE']=(float)$range;

   $rec['LM_DATE'] = date("Y-m-d H:i:s");

   //updating 'VIRTUAL_USER_ID' (int)
   //global $virtual_user_id;
   //$rec['VIRTUAL_USER_ID']=(int)$virtual_user_id;
   
   //UPDATING RECORD
   if ($ok)
   {
      if (isset($rec['POI_ID']))
      {
         $this->UpdateLocation($rec);
      }
      else
      {
         $new_rec = 1;
         $rec['POI_ID'] = $this->SetLocation($rec);
      }

      $out['OK'] = 1;
   }
   else
   {
      $out['ERR'] = 1;
   }
}

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