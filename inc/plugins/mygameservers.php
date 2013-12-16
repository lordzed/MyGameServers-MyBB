<?php
/**
 * MyGameservers Plugin for MyBB
 * Copyright � 2010 MyBB Mods
 *
 * By: Lordzed
 * Website: http://320it.tk/
 * Version: 1.5
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('admin_load', 'mygameservers_admin');
$plugins->add_hook('admin_config_menu', 'mygameservers_admin_config_menu');
$plugins->add_hook('admin_config_action_handler', 'mygameservers_admin_config_action_handler');
$plugins->add_hook('admin_config_permissions', 'mygameservers_admin_config_permissions');
$plugins->add_hook('global_start', 'mygameservers');

function mygameservers_info()
{
	global $lang;
	$lang->load('mygameservers');
	
	return array(
		'name'=> $lang->mygameservers,
		'description'   => $lang->mygameservers_desc,
		'website'       => 'http://mods.mybb.com/',
		'author'        => 'Lordzed',
		'authorsite'    => 'http://320it.tk',
		'version'       => '1.0.0',
		'guid'          => '1abd2b44188b679d300422e828eff0b1',
		'compatibility' => '16*'
	);
}

function mygameservers_install()
{
	global $db;

	$db->write_query("
		CREATE TABLE ".TABLE_PREFIX."mygameservers (
			`sid` int(10) unsigned NOT NULL auto_increment,
			`ipadress` varchar(100) NOT NULL,
			`port` varchar(50) NOT NULL,
			PRIMARY KEY (sid)
		) Type=MyISAM;
	");
}

function mygameservers_is_installed()
{
	global $db;

	if($db->table_exists('mygameservers'))
	{
		return true;
	}

	return false;
}

function mygameservers_uninstall()
{
	global $db;
	$db->drop_table('mygameservers');
}

function mygameservers_activate()
{
	global $db;	
	change_admin_permission('config', 'mygameservers', 0);
}

function mygameservers_deactivate()
{
	global $db;
	change_admin_permission('config', 'mygameservers', -1);
}

function mygameservers_admin_config_menu(&$sub_menu)
{
	global $lang;
	$lang->load('mygameservers');
	
	$sub_menu[] = array('id' => 'mygameservers', 'title' => $lang->mygameservers, 'link' => 'index.php?module=config/mygameservers');
}

function mygameservers_admin_config_action_handler(&$actions)
{
	$actions['mygameservers'] = array('active' => 'mygameservers', 'file' => 'mygameservers');
}

function mygameservers_admin_config_permissions($admin_permissions)
{
	global $lang;
	$lang->load('mygameservers');
	
	$admin_permissions['mygameservers'] = $lang->can_manage_gameservers;
}

function mygameservers_admin()
{
	global $db, $lang, $mybb, $page, $run_module, $action_file;
	$lang->load('mygameservers');

	if($run_module == 'config' && $action_file == 'mygameservers')
	{
		$page->add_breadcrumb_item($lang->mygameservers, 'index.php?module=config/mygameservers');

		if($mybb->input['action'] == 'add')
		{
			if($mybb->request_method == 'post')
			{
				if(!trim($mybb->input['ipadress']))
				{
					$errors[] = $lang->error_no_ip;
				}
				elseif(!trim($mybb->input['port']))
				{
					$errors[] = $lang->error_no_ip;
				}
				if(!$errors)
				{
					$new_gameserver = array(
						'ipadress' => $db->escape_string($mybb->input['ipadress']),
						'port' => $db->escape_string($mybb->input['port'])
					);

					$sid = $db->insert_query('mygameservers', $new_gameserver);

					log_admin_action($sid);

					flash_message($lang->success_server_saved, 'success');
					admin_redirect('index.php?module=config/mygameservers');
				}
			}

			$page->add_breadcrumb_item($lang->add_server);
			$page->output_header($lang->mygameservers.' - '.$lang->add_server);

			$sub_tabs['manage_gameservers'] = array(
				'title' => $lang->mygameservers,
				'link'  => 'index.php?module=config/mygameservers',
			);

			$sub_tabs['add_gameserver'] = array(
				'title'       => $lang->add_server,
				'link'        => 'index.php?module=config/mygameservers&amp;action=add',
				'description' => $lang->add_server_desc
			);

			$page->output_nav_tabs($sub_tabs, $lang->add_server);

			if($errors)
			{
				$page->output_inline_error($errors);
			}

			$form = new Form('index.php?module=config/mygameservers&amp;action=add', 'post', 'add');
			$form_container = new FormContainer($lang->add_server);
			$form_container->output_row($lang->ip.' <em>*</em>', $lang->ip_desc, $form->generate_text_box('ipadress', $mybb->input['ipadress']));
			$form_container->output_row($lang->port.' <em>*</em>', $lang->port_desc, $form->generate_text_box('port', $mybb->input['port']));
			$form_container->end();

			$buttons[] = $form->generate_submit_button($lang->add_server);

			$form->output_submit_wrapper($buttons);

			$form->end();

			$page->output_footer();
		}

		if($mybb->input['action'] == 'edit')
		{
			$query = $db->simple_select('mygameservers', '*', "sid='".intval($mybb->input['sid'])."'");
			$servidor = $db->fetch_array($query);

			if(!$servidor['sid'])
			{
				flash_message($lang->error_invalid_server, 'error');
				admin_redirect('index.php?module=config/mygameservers');
			}

			if($mybb->request_method == 'post')
			{
				if(!trim($mybb->input['ipadress']))
				{
					$errors[] = $lang->error_no_ip;
				}
				elseif(!trim($mybb->input['port']))
				{
					$errors[] = $lang->error_no_port;
				}				

				if(!$errors)
				{
					$servidor = array(
						'ipadress' => $db->escape_string($mybb->input['ipadress']),
						'port' => $db->escape_string($mybb->input['port'])
					);

					$db->update_query('mygameservers', $servidor, "sid='".intval($mybb->input['sid'])."'");

					log_admin_action(intval($mybb->input['sid']));

					flash_message($lang->success_server_saved, 'success');
					admin_redirect('index.php?module=config/mygameservers');
				}
			}

			$page->add_breadcrumb_item($lang->edit_server);
			$page->output_header($lang->mygameservers.' - '.$lang->edit_server);

			$sub_tabs['edit_gameserver'] = array(
				'title'       => $lang->edit_server,
				'link'        => 'index.php?module=config/mygameservers',
				'description' => $lang->edit_server_desc
			);

			$page->output_nav_tabs($sub_tabs, 'edit_gameserver');

			if($errors)
			{
				$page->output_inline_error($errors);
			}
			else
			{
				$mybb->input = $servidor;
			}

			$form = new Form('index.php?module=config/mygameservers&amp;action=edit', 'post', 'edit');
			echo $form->generate_hidden_field('sid', $servidor['sid']);

			$form_container = new FormContainer($lang->edit_server);
			$form_container->output_row($lang->ip.' <em>*</em>', $lang->ip_desc, $form->generate_text_box('ipadress', $mybb->input['ipadress']));
			$form_container->output_row($lang->port.' <em>*</em>', $lang->port_desc, $form->generate_text_box('port', $mybb->input['port']));
			$form_container->end();

			$buttons[] = $form->generate_submit_button($lang->add_server);
			$buttons[] = $form->generate_reset_button($lang->reset);

			$form->output_submit_wrapper($buttons);

			$form->end();

			$page->output_footer();
		}

		if($mybb->input['action'] == 'delete')
		{
			$query = $db->simple_select('mygameservers', '*', "sid='".intval($mybb->input['sid'])."'");
			$servidor = $db->fetch_array($query);

			if(!$servidor['sid'])
			{
				flash_message($lang->error_invalid_server, 'error');
				admin_redirect('index.php?module=config/mygameservers');
			}

			if($mybb->input['no'])
			{
				admin_redirect('index.php?module=config/mygameservers');
			}

			if($mybb->request_method == 'post')
			{
				$db->delete_query('mygameservers', "sid='{$servidor['sid']}'");

				log_admin_action($servidor['sid']);

				flash_message($lang->success_server_deleted, 'success');
				admin_redirect('index.php?module=config/mygameservers');
			}
			else
			{
				$page->output_confirm_action("index.php?module=config/mygameservers&amp;action=delete&amp;sid={$servidor['sid']}", $lang->confirm_server_deletion);
			}
		}

		if(!$mybb->input['action'])
		{
			$page->output_header($lang->mygameservers);

			$sub_tabs['manage_gameservers'] = array(
				'title'       => $lang->mygameservers,
				'link'        => 'index.php?module=config/mygameservers',
				'description' => $lang->manage_servers_desc
			);

			$sub_tabs['add_gameserver'] = array(
				'title' => $lang->add_server,
				'link'  => 'index.php?module=config/mygameservers&amp;action=add'
			);

			$page->output_nav_tabs($sub_tabs, 'manage_gameservers');

			$table = new Table;
			$table->construct_header($lang->ip);
			$table->construct_header($lang->port);
			$table->construct_header($lang->actions, array('class' => "align_center", 'colspan' => 2));

			$query = $db->simple_select('mygameservers', '*');
			while($servidor = $db->fetch_array($query))
			{
				$table->construct_cell($servidor['ipadress'], array('width' => '25%'));
				$table->construct_cell($servidor['port'], array('width' => '25%'));
				$table->construct_cell("<a href=\"index.php?module=config/mygameservers&amp;action=edit&amp;sid={$servidor['sid']}\">{$lang->actions_edit}</a>", array("class" => "align_center"));
				$table->construct_cell("<a href=\"index.php?module=config/mygameservers&amp;action=delete&amp;sid={$servidor['sid']}&amp;my_post_key={$mybb->post_code}\" onclick=\"return AdminCP.deleteConfirmation(this, '{$lang->confirm_server_deletion}')\">{$lang->actions_delete}</a>", array("class" => "align_center"));
				$table->construct_row();
			}

			if($table->num_rows() == 0)
			{
				$table->construct_cell($lang->no_servers, array('colspan' => 4));
				$table->construct_row();
			}

			$table->output($lang->mygameservers);

			$page->output_footer();
		}

		exit;
	}
}

function mygameservers()
{
	global $db, $mybb, $lang, $templates;
}

?>