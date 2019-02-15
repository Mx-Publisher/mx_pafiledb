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
 *    $Id: pafiledb_constants.php,v 1.14 2005/12/11 16:19:20 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
if ( !MXBB_MODULE )
{
	$server_protocol = ($board_config['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['server_name']));
	$server_port = ($board_config['server_port'] <> 80) ? ':' . trim($board_config['server_port']) : '';
	$script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($board_config['script_path']));
	$script_name = ($script_name == '') ? $script_name : '/' . $script_name;
		
	define( 'PORTAL_URL', $server_protocol . $server_name . $server_port . $script_name . '/' );
	define( 'PHPBB_URL', PORTAL_URL );
	
	$mx_table_prefix = $table_prefix;
	$is_block = false; // This also makes the script work for phpBB ;)
}
define( 'PAGE_DOWNLOAD', -501 ); // If this id generates a conflict with other mods, change it ;)	
 
// define('PAFILEDB_DEBUG', 0);		// Pafiledb Mod Debugging off
define( 'PAFILEDB_DEBUG', 1 ); // Pafiledb Mod Debugging on
define( 'PAFILEDB_QUERY_DEBUG', 1 );

define( 'PA_ROOT_CAT', 0 );
define( 'PA_CAT_ALLOW_FILE', 1 );

define( 'PA_AUTH_LIST_ALL', 0 );
define( 'PA_AUTH_ALL', 0 );

define( 'FILE_PINNED', 1 );

define( 'PA_AUTH_VIEW', 1 );
define( 'PA_AUTH_READ', 2 );
define( 'PA_AUTH_VIEW_FILE', 3 );
define( 'PA_AUTH_UPLOAD', 4 );
define( 'PA_AUTH_DOWNLOAD', 5 );
define( 'PA_AUTH_RATE', 6 );
define( 'PA_AUTH_EMAIL', 7 );
define( 'PA_AUTH_COMMENT_VIEW', 8 );
define( 'PA_AUTH_COMMENT_POST', 9 );
define( 'PA_AUTH_COMMENT_EDIT', 10 );
define( 'PA_AUTH_COMMENT_DELETE', 11 );

// Field Types
define( 'INPUT', 0 );
define( 'TEXTAREA', 1 );
define( 'RADIO', 2 );
define( 'SELECT', 3 );
define( 'SELECT_MULTIPLE', 4 );
define( 'CHECKBOX', 5 );

define( 'ICONS_DIR', 'pafiledb/images/icons/' ); 

define( 'PA_CATEGORY_TABLE', $mx_table_prefix . 'pa_cat' );
define( 'PA_COMMENTS_TABLE', $mx_table_prefix . 'pa_comments' );
define( 'PA_CUSTOM_TABLE', $mx_table_prefix . 'pa_custom' );
define( 'PA_CUSTOM_DATA_TABLE', $mx_table_prefix . 'pa_customdata' );
define( 'PA_DOWNLOAD_INFO_TABLE', $mx_table_prefix . 'pa_download_info' );
define( 'PA_FILES_TABLE', $mx_table_prefix . 'pa_files' );
define( 'PA_LICENSE_TABLE', $mx_table_prefix . 'pa_license' );
define( 'PA_CONFIG_TABLE', $mx_table_prefix . 'pa_config' );
define( 'PA_VOTES_TABLE', $mx_table_prefix . 'pa_votes' );
define( 'PA_AUTH_ACCESS_TABLE', $mx_table_prefix . 'pa_auth' );
define( 'PA_MIRRORS_TABLE', $mx_table_prefix . 'pa_mirrors' );

// **********************************************************************
// Read language definition
// **********************************************************************
if ( !file_exists( $module_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx ) )
{
	$link_language = 'lang_english';
	include( $module_root_path . 'language/' . $link_language . '/lang_main.' . $phpEx );
	include( $module_root_path . 'language/' . $link_language . '/lang_admin.' . $phpEx );
}
else
{
	$link_language = 'lang_' . $board_config['default_lang'];
	include( $module_root_path . 'language/' . $link_language . '/lang_main.' . $phpEx );
	include( $module_root_path . 'language/' . $link_language . '/lang_admin.' . $phpEx );
} 

if ( file_exists( $module_root_path . "templates/" . $theme['template_name'] . "/images" ) )
{
	// ----------
	$current_template_images = $module_root_path . "templates/" . $theme['template_name'] . "/images" ;
	// ----------
}
else
{
	// ----------
	$current_template_images = $module_root_path . "templates/" . "subSilver" . "/images" ;
	// ----------
}

$images['pa_search'] = "$current_template_images/" . $link_language . "/icon_pa_search.gif";
$images['pa_stats'] = "$current_template_images/" . $link_language . "/icon_pa_stats.gif";
$images['pa_toplist'] = "$current_template_images/" . $link_language . "/icon_pa_toplist.gif";
$images['pa_upload'] = "$current_template_images/" . $link_language . "/icon_pa_upload.gif";
$images['pa_viewall'] = "$current_template_images/" . $link_language . "/icon_pa_viewall.gif";
$images['pa_download'] = "$current_template_images/" . $link_language . "/icon_pa_download.gif";
$images['pa_rate'] = "$current_template_images/" . $link_language . "/icon_pa_rate.gif";
$images['pa_email'] = "$current_template_images/" . $link_language . "/icon_pa_email.gif";
$images['pa_comment_post'] = "$current_template_images/" . $link_language . "/icon_pa_post_comment.gif";
$images['pa_file_new'] = "$current_template_images/icon_pa_new.gif";

if ( !MXBB_MODULE || MXBB_27x )
{
	$pa_module_version = "pafileDB Download Manager v. 2.0.x";
	$pa_module_author = "Haplo/Jon";
	$pa_module_orig_author = "Mohd";
}
else 
{
	$mxbb_footer_addup[] = 'mxBB pafileDB Module';
}
?>