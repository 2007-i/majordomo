<?php

/**
 * voice short summary.
 */

 /**
 * voice short summary.
 *
 * @category Sound
 * @package MajorDoMo
 * @author L.D.V. <dev@silvergate.ru>
 * @license MIT http://opensource.org/licenses/MIT
 * Release: @package_version@
 * @access public
 * @link https://github.com/sergejey/majordomo/tree/master/lib/voice.class.php
 */
class Voice
{
   private $language;
   private $cachedVoiceDir;

   /**
    * Class constructor
    * @return void
    */
   public function __construct()
   {
      $this->language       = 'ru';
      $this->cachedVoiceDir = ROOT . 'cached/voice';

      self::InitializeVoiceFolder();
   }

   /**
    * Get current language
    * @return string
    */
   public function GetLanguage()
   {
      return $this->language;
   }

   /**
    * Set language
    * @param mixed $language Language
    * @return void
    */
   public function SetLanguage($language)
   {
      $this->language = $language;
   }

   /**
    * Check TTS Google param
    * @return bool
    */
   public function IsGoogleTtsEnabled()
   {
      if (defined('SETTINGS_TTS_GOOGLE') && SETTINGS_TTS_GOOGLE)
         return true;

      return false;
   }

   /**
    * Check for message exists on cache directory
    * @param mixed $message Message
    * @return bool
    */
   private function IsVoiceContentInCache($message)
   {
      $content   = md5($message) . '.mp3';
      $voiceFile = $this->cachedVoiceDir . '/' . $content;

      return file_exists($voiceFile);
   }

   /**
    * Save voice content to cache directory
    * @param mixed $message      Message to create voice file
    * @param mixed $voiceContent Voice content
    * @return int
    */
   private function SetGoogleTtsContentToCache($message, $voiceContent)
   {
      $content   = md5($message) . '.mp3';
      $voiceFile = $this->cachedVoiceDir . '/' . $content;

      $isSaved = SaveFile($voiceFile, $voiceContent);
      
      return $isSaved;
   }

   /**
    * Get voice file from cache
    * @param mixed $message Message
    * @return string
    */
   private function GetCachedGoogleTtsContent($message)
   {
      $content   = md5($message) . '.mp3';
      $voiceFile = $this->cachedVoiceDir . '/' . $content;

      touch($voiceFile);

      return $voiceFile;
   }

   /**
    * Get voice content from Google TTS by message
    * @param mixed $message Message
    * @return string
    */
   private function GetVoiceContentFromGoogleTts($message)
   {
      $url = 'http://translate.google.com/translate_tts?';

      $urlQuery = http_build_query(array('tl' => $this->language, 'ie' => 'UTF-8', 'q' => $message));
      
      $content = file_get_contents($url . $urlQuery);

      return $content;
   }

   /**
    * Initialize directory to store voice content
    * @return void
    */
   private function InitializeVoiceFolder()
   {
      if (!is_dir($this->cachedVoiceDir))
         mkdir($this->cachedVoiceDir, 0777);
   }

   /**
    * Get path to voice content from cache or Google TTS
    * @param mixed $message Message
    * @throws Exception     Exceptions
    * @return string
    */
   private function GetGoogleTtsFile($message)
   {
      if (self::IsVoiceContentInCache($message))
         return self::GetCachedGoogleTtsContent($message);
 
      $content = self::GetVoiceContentFromGoogleTts($message);

      if ($content === false)
         throw new Exception("Can't load content from Google");

      $isSaved = self::SetGoogleTtsContentToCache($message, $content);
      
      if ($isSaved === false)
         throw new Exception("Can't save voice file to cache directory: " . $this->cachedVoiceDir);

      if (!self::IsVoiceContentInCache($message))
         throw new Exception("Voice not found");

      return self::GetCachedGoogleTtsContent($message);
   }

   /**
    * Play voice content from cache or Google TTS
    * @param mixed $message    Message to play
    * @param mixed $exclusive  Play Exclusive
    * @param mixed $voiceLevel Voice Level
    * @return void
    */
   public function PlayGoogleTtsSound($message, $exclusive = 0, $voiceLevel = 0)
   {
      try
      {
         $voiceFile = self::GetGoogleTtsFile($message);
         
         playSound($voiceFile, $exclusive, $voiceLevel);
      }
      catch (Exception $ex)
      {
         DebMes("Error: " . $ex->getMessage());
      }
   }
}
