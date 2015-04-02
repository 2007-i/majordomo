<?php
/*
 * @version 0.2
 */

/**
 * GoogleTTS
 *
 * @access public
 * @param string $message 
 * @param string $lang 
 * @return int|string
 */
function GoogleTTS($message, $lang='ru')
{
   $fileName = md5($message) . '.mp3';
   $dirName  = ROOT.'cached/voice';
   $filePath = $dirName . '/' . $fileName;
   
   if (!is_dir($dirName))
      @mkdir($dirName, 0777);

   if (file_exists($filePath))
   {
      @touch($filePath);
      return $filePath;
   }

   $base_url = 'http://translate.google.com/translate_tts?';
   $qs = http_build_query(array('tl' => $lang, 'ie' => 'UTF-8', 'q' => $message));
   
   try
   {
      $contents = file_get_contents($base_url . $qs);
      
      if (!$contents) return 0;
      
      SaveFile($filePath, $contents);
      return $filePath;
   }
   catch(Exception $e)
   {
      registerError('googletts', get_class($e).', '.$e->getMessage());
   }
   
   return 0;
}

?>