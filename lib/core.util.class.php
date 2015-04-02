<?php

/**
 * PHP Syntax check
 *
 *
 * @package framework
 * @author Serge Dzheigalo <jey@activeunit.com>
 * @copyright Serge J. 2012
 * @version 1.1
 */

function php_syntax_error($code)
{
   $code .= "\n echo 'zzz';";
   $code = '<?php' . $code . '?>';
   
   $filename = md5(time().rand(0, 10000)).'.php';
   $file = DOC_ROOT . '/cached/' . $filename;
   
   SaveFile($file, $code);
   
   $cmd = 'php -l ' . $file;
   
   if (IsWindowsOS())
      $cmd = DOC_ROOT . '/../server/php/php -l '. $file;
   
   exec($cmd, $out);
   
   unlink($file);
   
   if (preg_match('/no syntax errors detected/is', $out[0])) 
      return false;
   
   if (!trim(implode("\n", $out))) 
      return false;
   
   $res = implode("\n", $out);
   $res = preg_replace('/Errors parsing.+/is', '', $res);
   
   return trim($res)."\n";
}

/**
 * Email validation
 * @param mixed $email 
 * @return bool
 */
function IsValidEmail($email)
{
   $emailPattern = "/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/";
   $emailToCheck = strtolower($email);
   
   if (!preg_match($emailPattern, $emailToCheck))
      return false;
   
   return true;
}

/**
 * checking valid password field
 * @param mixed $password 
 * @return int
 */
function IsValidPassword($password)
{
   if (strlen($password) >=4) 
      return 1;
   
   return 0;
}

?>