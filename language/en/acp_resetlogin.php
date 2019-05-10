<?php
/**
*
* @package Reset User Login Attempts
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'NO_LOGINS'				=> 'The selected user does not have any failed login attempts to reset',
	'NO_USER'				=> 'The selected user does not exist in the database',
	'NO_USER_SPECIFIED'		=> 'No user selected',

	'RESET_LOGIN_EXPLAIN'	=> 'Here you can reset a user’s failed login in attempts',

	'USER_EXPLAIN'			=> 'Select the required user',
	'USER_LOGIN_RESET'		=> 'Successfully reset %1$s failed login attempts for <strong>%2$s</strong>.',

	'VERSION'				=> 'Version',
));
