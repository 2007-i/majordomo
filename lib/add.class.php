<?php
/*
 * @version 0.1 (auto-set)
 */

function win2utf($in)
{
   return Core_Convert::Cp1251ToUtf8($in);
}

function utf2win($in)
{
   return Core_Convert::Utf8ToCp1251($in);
}


function mysort_array($ar, $field = "TITLE")
{
   $k = 1;
   while ($k>0)
   {
      $k = 0;
      for ($i = 1; $i < count($ar); $i++)
      {
         if (strcmp($ar[$i-1][$field], $ar[$i][$field]) == 1)
         {
            $temp = array();
            $temp = $ar[$i-1];
            $ar[$i-1] = $ar[$i];
            $ar[$i] = $temp;
            $k++;
         }
      }
   }
 
   return $ar;
}

?>