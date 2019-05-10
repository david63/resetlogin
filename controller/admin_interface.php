<?php
/**
*
* @package Reset User Login Attempts
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\resetlogin\controller;

/**
* Interface for our admin controller
*
* This describes all of the methods we'll use for the admin of this extension
*/
interface admin_interface
{
	/**
	* Display the output for this extension
	*
	* @return null
	* @access public
	*/
	public function display_output();
}
