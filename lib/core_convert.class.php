<?php

/**
 * Convert.
 *
 * @version 1.0
 * @author LDV
 */
class Core_Convert
{
   /**
    * Convert string from Cp1251 to Utf-8
    * @param string $inputString String in cp1251
    * @return string
    */
   public static function Cp1251ToUtf8($inputString)
   {
      return iconv('windows-1251', 'utf-8', $inputString);
   }
   
   /**
    * Convert string from Utf-8 to Cp1251
    * @param string $inputString String in cp1251
    * @return string
    */
   public static function Utf8ToCp1251($inputString)
   {
      return iconv('utf-8', 'windows-1251', $inputString);
   }
   
   /**
    * Convert string to Utf-8
    * @param string $codePage Source codepage
    * @param string $inputString 
    * @return string
    */
   public static function ToUtf8($codePage, $inputString)
   {
      return iconv($codePage,'utf-8', $inputString);
   }
   
   /**
    * Convert string to Cp1251
    * @param string $codePage Source codepage
    * @param string $inputString 
    * @return string
    */
   public static function ToCp1251($codePage, $inputString)
   {
      return iconv($codePage, 'windows-1251', $inputString);
   }
   
   /**
    * Convert pressure from hpa to mmhg
    * @param mixed $pressure pressure
    * @param mixed $precision round result to precision
    * @return double|null
    */
   public static function PressureHpaToMmhg($pressure, $precision = 2)
   {
      if (!is_numeric($pressure))
         return null;
      
      $pressure = (float) $pressure;
      
      $result = round($pressure * 0.75006375541921, $precision);
      
      return $result;
   }
   
   /**
    * Convert pressure from hpa to mmhg
    * @param mixed $pressure pressure
    * @param mixed $precision round result to precision
    * @return double|null
    */
   public static function PressureMmhgToHpa($pressure, $precision = 2)
   {
      if (!is_numeric($pressure))
         return null;
      
      $pressure = (float) $pressure;
      
      $result = round($pressure * 1.33322, $precision);
      
      return $result;
   }
   
   /**
    * Convert wind direction (degree) to Wind direction name aka south, nord east etc.
    * @param mixed $degree 
    * @return string
    */
   public static function WindDirectionToName($degree)
   {
      $windDirection = ['<#LANG_N#>', '<#LANG_NNE#>', '<#LANG_NE#>', '<#LANG_ENE#>', '<#LANG_E#>', '<#LANG_ESE#>', '<#LANG_SE#>', '<#LANG_SSE#>', '<#LANG_S#>', '<#LANG_SSW#>', '<#LANG_SW#>', '<#LANG_WSW#>', '<#LANG_W#>', '<#LANG_WNW#>', '<#LANG_NW#>', '<#LANG_NNW#>', '<#LANG_N#>'];
      
      return $windDirection[round($degree / 22.5)];
   }
}
