<?php
/**
*
* @package Reset User Login Attempts
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\resetlogin\controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use david63\resetlogin\ext;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\log */
	protected $log;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin controller
	*
	* @param \phpbb\config\config				$config		Config object
	* @param \phpbb\db\driver\driver_interface	$db
	* @param \phpbb\request\request				$request	Request object
	* @param \phpbb\template\template			$template	Template object
	* @param \phpbb\user						$user		User object
	* @param \phpbb\log\log						$log		phpBB log
	* @param string 							$root_path
	* @param string 							$php_ext
	* @param phpbb\language\language			$language
	*
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\log\log $log, $root_path, $php_ext, \phpbb\language\language $language)
	{
		$this->config		= $config;
		$this->db  			= $db;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->log			= $log;
		$this->root_path	= $root_path;
		$this->phpEx		= $php_ext;
		$this->language		= $language;
	}

	/**
	* Display the output for this extension
	*
	* @return null
	* @access public
	*/
	public function display_output()
	{
		// Add the language file
		$this->language->add_lang('acp_resetlogin', 'david63/resetlogin');

		$form_key = 'reset_login';
		add_form_key($form_key);

		$submit 		= ($this->request->is_set_post('submit')) ? true : false;
		$reset_username	= $this->request->variable('reset_username', '', true);

		$errors = array();

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID'));
			}

			if (!empty($reset_username))
			{
				$sql = 'SELECT user_id, user_login_attempts
					FROM ' . USERS_TABLE . "
					WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($reset_username)) . "'";
				$result = $this->db->sql_query($sql);

				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$user_id		= $row['user_id'];
				$login_attempts	= $row['user_login_attempts'];

				if (!$user_id)
				{
					$errors[] = $this->language->lang('NO_USER');
				}

				if (!$login_attempts)
				{
					$errors[] = $this->language->lang('NO_LOGINS');
				}
			}
			else
			{
				$errors[] = $this->language->lang('NO_USER_SPECIFIED');
			}

			if (empty($errors))
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_login_attempts = 0
					WHERE user_id = ' . (int) $user_id;
				$this->db->sql_query($sql);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_LOGIN_RESET',  time(), array($login_attempts, $reset_username));
				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_LOGIN_RESET', time(), array('reportee_id' => $this->user->data['username'], $login_attempts, $reset_username));
				trigger_error($this->language->lang('USER_LOGIN_RESET', $login_attempts, $reset_username) . adm_back_link($this->u_action));
			}
		}

		$this->template->assign_vars(array(
			'ERROR_MSG'						=> implode('<br />', $errors),
			'RESET_LOGIN_ATTEMPTS_VERSION'	=> ext::RESET_LOGIN_ATTEMPTS_VERSION,
			'RESET_USERNAME'				=> (!empty($user_id)) ? $reset_username : '',

			'S_ERROR'						=> (count($errors)) ? true : false,
			'U_ACTION'						=> $this->u_action,
			'U_RESET_USERNAME'				=> append_sid("{$this->root_path}memberlist.$this->phpEx", 'mode=searchuser&amp;form=resetlogin&amp;field=reset_username&amp;select_single=true'),
		));
	}
}
