<?php

/**
 * Default language file for PostOffice application
 *
 * @package App_quote
 * @author Lutsenko D.V. <palacex@gmail.com> http://silvergate.ru/
 * @version 1.0
 */

$dictionary = array(
   'APP_QUOTE'=> 'Quotes',
   'APP_QUOTE_QUOTE' => 'Quote'
   );

foreach ($dictionary as $k=>$v)
{
   if (!defined('LANG_' . $k))
      define('LANG_' . $k, $v);
}
?>