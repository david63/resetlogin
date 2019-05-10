<?php
/**
*
* @package Reset User Login Attempts
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\resetlogin\acp;

class resetlogin_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\resetlogin\acp\resetlogin_module',
			'title'		=> 'RESET_LOGIN',
			'modes'		=> array(
				'main'		=> array('title' => 'RESET_LOGIN', 'auth' => 'ext_david63/resetlogin && acl_a_user', 'cat' => array('ACP_CAT_USERS')),
			),
		);
	}
}
