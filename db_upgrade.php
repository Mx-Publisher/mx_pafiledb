<?php
/** ------------------------------------------------------------------------
 *		Subject				: mxBB - a fully modular portal and CMS (for phpBB) 
 *		Author				: Jon Ohlsson and the mxBB Team
 *		Credits				: The phpBB Group & Marc Morisette, Mohd Basri & paFileDB 3.0 ©2001/2002 PHP Arena
 *		Copyright          	: (C) 2002-2005 mxBB Portal
 *		Email             	: jon@mxbb-portal.com
 *		Project site		: www.mxbb-portal.com
 * -------------------------------------------------------------------------
 * 
 *    $Id: db_upgrade.php,v 1.11 2005/12/08 15:15:11 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */

define( 'IN_PORTAL', true );
if ( !defined( 'IN_ADMIN' ) )
{
	$mx_root_path = './../../';
	include( $mx_root_path . 'extension.inc' );
	include( $mx_root_path . 'common.' . $phpEx ); 
	// Start session management
	$userdata = session_pagestart( $user_ip, PAGE_INDEX );
	mx_init_userprefs( $userdata );

	if ( !$userdata['session_logged_in'] )
	{
		die( "Hacking attempt(1)" );
	}

	if ( $userdata['user_level'] != ADMIN )
	{
		die( "Hacking attempt(2)" );
	} 
	// End session management
}

$mx_module_version = 'mxBB pafileDB Module 2.0.2';
$mx_module_copy = 'Based on <a href="http://www.phparena.net/" target="_phpbb" >PHP Arena, pafileDB 3.0</a> & <a href="http://www.phpbb.com/phpBB/viewtopic.php?t=56035" target="_phpbb" >Mohd pafileDB 0.0.9d</a>';

// For compatibility with core 2.7.+
define( 'MXBB_27x', file_exists( $mx_root_path . 'mx_login.php' ) );

if ( MXBB_27x )
{
	include_once( $mx_root_path . 'modules/mx_pafiledb/pafiledb/includes/functions_mx.' . $phpEx );
}

$sql = array();
// Precheck
if ( $result = $db->sql_query( "SELECT config_name from " . $mx_table_prefix . "pa_config" ) )
{

	// Upgrade checks
	$upgrade_103 = 0;
	$upgrade_201 = 0;
		
	$message = "<b>Upgrading!</b><br/><br/>"; 
	// validate before 1.0.3
	if ( !$result = $db->sql_query( "SELECT auth_edit_file from " . $mx_table_prefix . "pa_cat" ) )
	{
		$upgrade_103 = 1;
		$message .= "<b>Upgrading to v. 1.0.3...</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 1.0.3...ok</b><br/><br/>";
	} 
	
	// validate before 2.0.1
	if ( !$result = $db->sql_query( "SELECT auth_approval from " . $mx_table_prefix . "pa_cat" ) )
	{
		$upgrade_201 = 1;
		$message .= "<b>Validating v. 2.0.1...ok</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 2.0.1...ok</b><br/><br/>";
	} 
	
	// validate before 2.0.2
	$result = $db->sql_query( "SELECT config_value from " . $mx_table_prefix . "pa_config WHERE config_name = 'internal_comments'" );
	if ( $db->sql_numrows( $result ) == 0 || true)
	{
		$upgrade_202 = 1;
		$message .= "<b>Upgrading to v. 2.0.2...ok</b><br/><br/>";
	}
	else
	{
		$message .= "<b>Validating v. 2.0.2...ok</b><br/><br/>";
	}	

	// ------------------------------------------------------------------------------------------------------
	if ( $upgrade_103 == 1 )
	{	
		
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD auth_edit_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_view_file ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD auth_delete_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_edit_file ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD cat_allow_ratings tinyint(2) NOT NULL default '1' AFTER cat_allow_file ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD cat_allow_comments tinyint(2) NOT NULL default '1' AFTER cat_allow_ratings ";
		
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_auth ADD auth_edit_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_view_file ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_auth ADD auth_delete_file tinyint(1) DEFAULT '0' NOT NULL AFTER auth_edit_file ";
		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('pm_notify', '0')";
	}

	if ( $upgrade_201 == 1 )
	{	
		
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD auth_approval tinyint(2) DEFAULT '0' NOT NULL AFTER auth_delete_comment ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_auth ADD auth_approval tinyint(1) DEFAULT '0' NOT NULL AFTER auth_delete_comment ";
		
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat MODIFY auth_edit_file tinyint(2) DEFAULT '0' NOT NULL ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat MODIFY auth_delete_file tinyint(2) DEFAULT '0' NOT NULL ";
		
		// Upgrade the config table to avoid duplicate entries
		/*
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_config MODIFY config_name VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_config MODIFY config_value VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_config DROP PRIMARY KEY, ADD PRIMARY KEY (config_name) ";
		*/
		
	}

	if ( $upgrade_202 == 1 )
	{
		// Upgrade the config table to avoid duplicate entries
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_config MODIFY config_name VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_config MODIFY config_value VARCHAR(255) NOT NULL default '' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_config DROP PRIMARY KEY, ADD PRIMARY KEY (config_name) ";	

		// Configs
		$sql[] = "UPDATE " . $mx_table_prefix . "pa_config" . " SET config_name = 'enable_module' WHERE config_name = 'settings_disable'";
		$sql[] = "UPDATE " . $mx_table_prefix . "pa_config" . " SET config_name = 'module_name' WHERE config_name = 'settings_dbname'";
		$sql[] = "UPDATE " . $mx_table_prefix . "pa_config" . " SET config_name = 'pagination' WHERE config_name = 'settings_file_page'";
		
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'art_pagination'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'comments_show'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'pm_notify'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'allow_wysiwyg_comments'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'allow_wysiwyg'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'formatting_fixup'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'formatting_comment_fixup'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'need_validation'";
		$sql[] = "DELETE FROM " . $mx_table_prefix . "pa_config" . " WHERE config_name = 'validator'";
				
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('wysiwyg_path', 'modules/')";
		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('use_comments', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('internal_comments', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('formatting_comment_wordwrap', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('formatting_comment_image_resize', '300')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('formatting_comment_truncate_links', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('max_comment_subject_chars', '50')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('max_comment_chars', '5000')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_wysiwyg', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_html', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_bbcode', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_smilies', '1')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_links', '1')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_images', '1')";	
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('no_comment_image_message', '[No image please]')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('no_comment_link_message', '[No links please]')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allowed_comment_html_tags', 'b,i,u,a')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('del_topic', '1')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('autogenerate_comments', '1')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('comments_pagination', '5')";	
		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('use_ratings', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('votes_check_userid', '1')";
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('votes_check_ip', '1')";
			
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('notify', '0')";	
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('notify_group', '0')";	
			
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('show_pretext', '1')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('pt_header', 'File Submission Instructions')";		
		$sql[] = "INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('pt_body', 'Please check your references and include as much information as you can.')";		
		
		
		// add fields to pa_category table
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD internal_comments tinyint(2) NOT NULL DEFAULT '-1' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD autogenerate_comments tinyint(2) NOT NULL DEFAULT '-1' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD comments_forum_id mediumint(8) NOT NULL DEFAULT '-1' ";

		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD show_pretext tinyint(2) NOT NULL default '-1' ";
		
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD notify tinyint(2) NOT NULL DEFAULT '-1' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD notify_group mediumint(8) NOT NULL DEFAULT '-1' ";
		
		// auth
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_cat ADD auth_approval_groups tinyint(2) NOT NULL default '0' ";
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_auth ADD auth_approval_groups tinyint(1) DEFAULT '0' NOT NULL ";
		
		// add fields to pa_files table
		$sql[] = "ALTER TABLE " . $mx_table_prefix . "pa_files ADD topic_id mediumint(8) unsigned NOT NULL default '0'";
	}

	if ( !MXBB_27x )
	{	
		$sql[] = "UPDATE " . $mx_table_prefix . "module" . "
				    SET module_version  = '" . $mx_module_version . "',
				      module_copy  = '" . $mx_module_copy . "'
				    WHERE module_id = '" . $mx_module_id . "'";
	}
			
	$message .= mx_do_install_upgrade( $sql );
}
else
{ 
	// If not installed
	$message = "<b>Module not installed...and thus cannot be upgraded ;)</b><br/><br/>";
}

echo "<br /><br />";
echo "<table  width=\"90%\" align=\"center\" cellpadding=\"4\" cellspacing=\"1\" border=\"0\" class=\"forumline\">";
echo "<tr><th class=\"thHead\" align=\"center\">Module Installation/Upgrading/Uninstalling Information - module specific db tables</th></tr>";
echo "<tr><td class=\"row1\"  align=\"left\"><span class=\"gen\">" . $message . "</span></td></tr>";
echo "</table><br />";

?>