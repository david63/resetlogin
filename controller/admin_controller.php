<?php
/**
*
* @package Reset User Login Attempts
* @copyright (c) 2014 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\resetlogin\controller;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\log\log;
use phpbb\language\language;
use david63\resetlogin\core\functions;

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

	/** @var \david63\resetlogin\core\functions */
	protected $functions;

	/** @var string phpBB tables */
	protected $tables;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin controller
	*
	* @param \phpbb\config\config					$config		Config object
	* @param \phpbb\db\driver\driver_interface		$db			The db connection
	* @param \phpbb\request\request					$request	Request object
	* @param \phpbb\template\template				$template	Template object
	* @param \phpbb\user							$user		User object
	* @param \phpbb\log\log							$log		phpBB log
	* @param string 								$root_path	phpBB root path
	* @param string 								$php_ext	phpBB ext
	* @param \phpbb\language\language				$language	Language object
	* @param \david63\resetlogin\core\functions		functions	Functions for the extension
	* @param array									$tables		phpBB db tables
	*
	* @access public
	*/
	public function __construct(config $config, driver_interface $db, request $request, template $template, user $user, log $log, $root_path, $php_ext, language $language, functions $functions, $tables)
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
		$this->functions	= $functions;
		$this->tables		= $tables;
	}

	/**
	* Display the output for this extension
	*
	* @return null
	* @access public
	*/
	public function display_output()
	{
		// Add the language files
		$this->language->add_lang(array('acp_resetlogin', 'acp_common'), $this->functions->get_ext_namespace());

		$form_key = 'reset_login';
		add_form_key($form_key);

		$back = false;

		$submit 		= ($this->request->is_set_post('submit')) ? true : false;
		$reset_username	= $this->request->variable('reset_username', '', true);

		$errors = [];

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID'));
			}

			if (!empty($reset_username))
			{
				$sql = 'SELECT user_id, user_login_attempts
					FROM ' . $this->tables['users'] . "
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
				$sql = 'UPDATE ' . $this->tables['users'] . '
					SET user_login_attempts = 0
					WHERE user_id = ' . (int) $user_id;
				$this->db->sql_query($sql);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_LOGIN_RESET',  time(), array($login_attempts, $reset_username));
				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_LOGIN_RESET', time(), array('reportee_id' => $this->user->data['username'], $login_attempts, $reset_username));
				trigger_error($this->language->lang('USER_LOGIN_RESET', $login_attempts, $reset_username) . adm_back_link($this->u_action));
			}
		}

		// Template vars for header panel
		$version_data	= $this->functions->version_check();

		$this->template->assign_vars(array(
			'DOWNLOAD'			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'HEAD_TITLE'		=> $this->language->lang('RESET_LOGIN'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('RESET_LOGIN_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER'	=> $this->functions->get_meta('version'),
		));

		$this->template->assign_vars(array(
			'ERROR_DESCRIPTION'	=> implode('<br>', $errors),
			'RESET_USERNAME'	=> (!empty($user_id)) ? $reset_username : '',

			'S_ERROR'			=> (count($errors)) ? true : false,

			'U_ACTION'			=> $this->u_action,
			'U_RESET_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->phpEx", 'mode=searchuser&amp;form=resetlogin&amp;field=reset_username&amp;select_single=true'),
		));
	}
}
