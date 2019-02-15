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
 *    $Id: db_install.php,v 1.17 2005/12/08 15:15:11 jonohlsson Exp $
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

// If fresh install
if ( !$result = $db->sql_query( "SELECT config_name from " . $mx_table_prefix . "pa_config" ) )
{
	$message = "<b>This is a fresh install!</b><br/><br/>";

	$sql = array( 
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_cat ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_auth ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_comments ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_config ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_custom ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_customdata ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_download_info ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_license ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_votes ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_mirrors ",
		"DROP TABLE IF EXISTS " . $mx_table_prefix . "pa_files ", 
		
		// Table structure for table `pa_auth`
		"CREATE TABLE " . $mx_table_prefix . "pa_auth (
			   group_id mediumint(8) DEFAULT '0' NOT NULL,
			   cat_id smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
			   auth_view tinyint(1) DEFAULT '0' NOT NULL,
			   auth_read tinyint(1) DEFAULT '0' NOT NULL,
			   auth_view_file tinyint(1) DEFAULT '0' NOT NULL,
			   auth_edit_file tinyint(1) DEFAULT '0' NOT NULL,
			   auth_delete_file tinyint(1) DEFAULT '0' NOT NULL,
			   auth_upload tinyint(1) DEFAULT '0' NOT NULL,
			   auth_download tinyint(1) DEFAULT '0' NOT NULL,
			   auth_rate tinyint(1) DEFAULT '0' NOT NULL,
			   auth_email tinyint(1) DEFAULT '0' NOT NULL,
			   auth_view_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_post_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_edit_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_delete_comment tinyint(1) DEFAULT '0' NOT NULL,
			   auth_approval tinyint(1) DEFAULT '0' NOT NULL,
			   auth_approval_groups tinyint(1) DEFAULT '0' NOT NULL,
			   auth_mod tinyint(1) DEFAULT '1' NOT NULL,
			   auth_search tinyint(1) DEFAULT '1' NOT NULL,
			   auth_stats tinyint(1) DEFAULT '1' NOT NULL,
			   auth_toplist tinyint(1) DEFAULT '1' NOT NULL,
			   auth_viewall tinyint(1) DEFAULT '1' NOT NULL,
			   KEY group_id (group_id),
			   KEY cat_id (cat_id)
		)", 
		
		// Table structure for table `pa_cat`
		"CREATE TABLE " . $mx_table_prefix . "pa_cat (
			  cat_id int(10) NOT NULL auto_increment,
			  cat_name text,
			  cat_desc text,
			  cat_parent int(50) default NULL,
			  parents_data text NOT NULL,
			  cat_order int(50) default NULL,
			  cat_allow_file tinyint(2) NOT NULL default '0',
			
			  cat_allow_comments tinyint(2) NOT NULL default '1',	
			  internal_comments tinyint(2) NOT NULL default '-1',
			  autogenerate_comments tinyint(2) NOT NULL default '-1',
			  comments_forum_id mediumint(8) NOT NULL DEFAULT '-1',
		
		      cat_allow_ratings tinyint(2) NOT NULL default '-1',
		
		      show_pretext tinyint(2) NOT NULL default '-1',
	
			  notify tinyint(2) NOT NULL default '-1',
			  notify_group mediumint(8) unsigned NOT NULL default '-1',
					
			  cat_files mediumint(8) NOT NULL default '-1',
			  cat_last_file_id mediumint(8) unsigned NOT NULL default '0',
			  cat_last_file_name varchar(255) NOT NULL default '',
			  cat_last_file_time INT(50) UNSIGNED DEFAULT '0' NOT NULL,
		
			  auth_view tinyint(2) NOT NULL default '0',
			  auth_read tinyint(2) NOT NULL default '0',
			  auth_view_file tinyint(2) NOT NULL default '0',
			  auth_edit_file tinyint(2) DEFAULT '0' NOT NULL,
			  auth_delete_file tinyint(2) DEFAULT '0' NOT NULL,
			  auth_upload tinyint(2) NOT NULL default '0',
			  auth_download tinyint(2) NOT NULL default '0',
			  auth_rate tinyint(2) NOT NULL default '0',
			  auth_email tinyint(2) NOT NULL default '0',
			  auth_view_comment tinyint(2) NOT NULL default '0',
			  auth_post_comment tinyint(2) NOT NULL default '0',
			  auth_edit_comment tinyint(2) NOT NULL default '0',
			  auth_delete_comment tinyint(2) NOT NULL default '0',
			  auth_approval tinyint(2) NOT NULL default '0',
			  auth_approval_groups tinyint(2) NOT NULL default '0',
			  PRIMARY KEY  (cat_id)
 		)", 
		
		//
		// Insert
		//
		"INSERT INTO " . $mx_table_prefix . "pa_cat VALUES (1, 'My Category', '', 0, '', 1, 0, 1, 1, 0, 0, '', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)",
		"INSERT INTO " . $mx_table_prefix . "pa_cat VALUES (2, 'Test Cagegory', 'Just a test category', 1, '', 2, 1, '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', 1, 1, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)",
		
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_comments`
		"CREATE TABLE " . $mx_table_prefix . "pa_comments (
			  comments_id int(10) NOT NULL auto_increment,
			  file_id int(10) NOT NULL default '0',
			  comments_text text NOT NULL,
			  comments_title text NOT NULL,
			  comments_time int(50) NOT NULL default '0',
			  comment_bbcode_uid varchar(10) default NULL,
			  poster_id mediumint(8) NOT NULL default '0',
			  PRIMARY KEY  (comments_id),
			  KEY comments_id (comments_id),
			  FULLTEXT KEY comment_bbcode_uid (comment_bbcode_uid)
		)", 
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_config`
		"CREATE TABLE " . $mx_table_prefix . "pa_config (
			  config_name varchar(255) NOT NULL default '',
			  config_value varchar(255) NOT NULL default '',
			  PRIMARY KEY  (config_name)
		)", 
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_custom`
		"CREATE TABLE " . $mx_table_prefix . "pa_custom (
			  custom_id int(50) NOT NULL auto_increment,
			  custom_name text NOT NULL,
			  custom_description text NOT NULL,
			  data text NOT NULL,
			  field_order int(20) NOT NULL default '0',
			  field_type tinyint(2) NOT NULL default '0',
			  regex varchar(255) NOT NULL default '',
			  PRIMARY KEY  (custom_id)
		)", 
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_customdata`
		"CREATE TABLE " . $mx_table_prefix . "pa_customdata (
			  customdata_file int(50) NOT NULL default '0',
			  customdata_custom int(50) NOT NULL default '0',
			  data text NOT NULL
		)", 
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_download_info`
		"CREATE TABLE " . $mx_table_prefix . "pa_download_info (
			  file_id mediumint(8) NOT NULL default '0',
			  user_id mediumint(8) NOT NULL default '0',
			  downloader_ip varchar(8) NOT NULL default '',
			  downloader_os varchar(255) NOT NULL default '',
			  downloader_browser varchar(255) NOT NULL default '',
			  browser_version varchar(255) NOT NULL default ''
		)", 
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_files`
		"CREATE TABLE " . $mx_table_prefix . "pa_files (
			  file_id int(10) NOT NULL auto_increment,
			  user_id mediumint(8) NOT NULL default '0',
			  poster_ip varchar(8) NOT NULL default '',
			  file_name text,
			  file_size int(20) NOT NULL default '0',
			  unique_name varchar(255) NOT NULL default '',
			  real_name VARCHAR(255) NOT NULL,
			  file_dir VARCHAR(255) NOT NULL,
			  file_desc text,
			  file_creator text,
			  file_version text,
			  file_longdesc text,
			  file_ssurl text,
			  file_sshot_link tinyint(2) NOT NULL default '0',
			  file_dlurl text,
			  file_time int(50) default NULL,
			  file_update_time int(50) NOT NULL default '0',
			  file_catid int(10) default NULL,
			  file_posticon text,
			  file_license int(10) default NULL,
			  file_dls int(10) DEFAULT '0' NOT NULL,
			  file_last int(50) default NULL,
			  file_pin int(2) default NULL,
			  file_docsurl text,
			  file_approved TINYINT(1) DEFAULT '1' NOT NULL,
			  file_broken TINYINT(1) DEFAULT '0' NOT NULL,
	 		  topic_id mediumint(8) unsigned NOT NULL default '0',		
			  PRIMARY KEY  (file_id)
		)", 
		
		// --------------------------------------------------------
		"CREATE TABLE " . $mx_table_prefix . "pa_mirrors (
			  mirror_id mediumint(8) NOT NULL auto_increment, 
			  file_id int(10) NOT NULL,
			  unique_name varchar(255) NOT NULL default '',
			  file_dir VARCHAR(255) NOT NULL, 
			  file_dlurl varchar(255) NOT NULL default '',
			  mirror_location VARCHAR(255) NOT NULL default '',
			  PRIMARY KEY  (mirror_id),
			  KEY file_id (file_id)
		)", 
		
		// Table structure for table `phpbb_pa_license`
		"CREATE TABLE " . $mx_table_prefix . "pa_license (
			  license_id int(10) NOT NULL auto_increment,
			  license_name text,
			  license_text text,
			  PRIMARY KEY  (license_id)
		)", 
		
		// --------------------------------------------------------
		// Table structure for table `phpbb_pa_votes`
		"CREATE TABLE " . $mx_table_prefix . "pa_votes (
			  user_id mediumint(8) NOT NULL default '0',
			  votes_ip varchar(50) NOT NULL default '0',
			  votes_file int(50) NOT NULL default '0',
			  rate_point tinyint(3) unsigned NOT NULL default '0',
			  voter_os varchar(255) NOT NULL default '',
			  voter_browser varchar(255) NOT NULL default '',
			  browser_version varchar(8) NOT NULL default '',
			  KEY user_id (user_id),
			  KEY votes_file (votes_file),
			  KEY votes_ip (votes_ip),
			  KEY voter_os (voter_os),
			  KEY voter_browser (voter_browser),
			  KEY browser_version (browser_version),
			  KEY rate_point (rate_point)
		)",

		//
		// Config values
		//
		
		// General
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('enable_module', '0')", // settings_disable
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('module_name', 'Download Database')", // settings_dbname
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('wysiwyg_path', 'modules/')",		
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('upload_dir','pafiledb/uploads/')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('screenshots_dir','pafiledb/images/screenshots/')",
		
		// Files
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('max_file_size','262144')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('forbidden_extensions','php, php3, php4, phtml, pl, asp, aspx, cgi')", 
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('hotlink_prevent', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('hotlink_allowed', '')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('tpl_php', '0')",
				
		// Appearance
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('settings_topnumber', '10')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('settings_newdays', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('settings_stats', '')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('settings_viewall', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('settings_dbdescription', '')",

		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('sort_method', 'file_time')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('sort_order', 'DESC')", 
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('pagination', '20')", // art_pagination & settings_file_page
		
		// Comments
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('use_comments', '1')", // comments_show
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('internal_comments', '1')", // NEW
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('formatting_comment_wordwrap', '1')", // formatting_comment_fixup
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('formatting_comment_image_resize', '300')", // NEW
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('formatting_comment_truncate_links', '1')", // NEW
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('max_comment_subject_chars', '50')", // NEW
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('max_comment_chars', '5000')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_wysiwyg', '0')", // allow_wysiwyg_comments & allow_wysiwyg
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_html', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_bbcode', '1')",		
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_smilies', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_links', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allow_comment_images', '0')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('no_comment_image_message', '[No image please]')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('no_comment_link_message', '[No links please]')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('allowed_comment_html_tags', 'b,i,u,a')", // NEW
		"INSERT INTO " . $mx_table_prefix . "kb_config VALUES ('del_topic', '1')", // NEW
		"INSERT INTO " . $mx_table_prefix . "kb_config VALUES ('autogenerate_comments', '1')",	// NEW		
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('comments_pagination', '5')",		
				
		// Ratings
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('use_ratings', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('votes_check_userid', '1')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('votes_check_ip', '1')",	
				
		// Instructions
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('show_pretext', '0')", // NEW
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('pt_header', 'File Submission Instructions')", // NEW
		"INSERT INTO " . $mx_table_prefix . "pa_config values ('pt_body', 'Please check your references and include as much information as you can.')", // NEW
		
		// Notifications
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('notify', 'pm')", // pm_notify
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('notify_group', '0')",	// NEW	
		
		// Permissions
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('auth_search','0')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('auth_stats','0')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('auth_toplist','0')",
		"INSERT INTO " . $mx_table_prefix . "pa_config VALUES ('auth_viewall','0')",
			
		);

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
	// If already installed
	$message = "<b>Module is already installed...consider upgrading ;)</b><br/><br/>";
}

echo "<br /><br />";
echo "<table  width=\"90%\" align=\"center\" cellpadding=\"4\" cellspacing=\"1\" border=\"0\" class=\"forumline\">";
echo "<tr><th class=\"thHead\" align=\"center\">Module Installation/Upgrading/Uninstalling Information - module specific db tables</th></tr>";
echo "<tr><td class=\"row1\"  align=\"left\"><span class=\"gen\">" . $message . "</span></td></tr>";
echo "</table><br />";

?>