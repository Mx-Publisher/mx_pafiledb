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
 *    $Id: admin_pa_settings.php,v 1.15 2005/12/11 16:19:20 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
if ( file_exists( './../viewtopic.php' ) )
{
	//
	// phpBB MOD mode
	//	
	define( 'IN_PHPBB', 1 );
	define( 'IN_PORTAL', 1 );
	define( 'MXBB_MODULE', false );
	
	$phpbb_root_path = $module_root_path = $mx_root_path = './../';
	require_once( $phpbb_root_path . 'extension.inc' );

	if ( !empty( $setmodules ) )
	{
		include_once( $module_root_path . 'pafiledb/includes/pafiledb_constants.' . $phpEx );
		$file = basename( __FILE__ );
		$module['pafileDB_Download']['0_Configuration_title'] = $file;
		return;
	}	

	// Load default header
	
	$no_page_header = true;

	require( './pagestart.' . $phpEx );
	
	include_once( $phpbb_root_path . 'includes/functions_admin.'.$phpEx );
	include_once( $phpbb_root_path . 'pafiledb/pafiledb_common.' . $phpEx );
	include_once( $phpbb_root_path . 'pafiledb/includes/functions_admin.' . $phpEx );
}
else 
{
	define( 'IN_PORTAL', 1 );
	define( 'MXBB_MODULE', true );
	
	if ( !empty( $setmodules ) )
	{
		$mx_root_path = './../';
		$module_root_path = './../modules/mx_pafiledb/';
		require_once( $mx_root_path . 'extension.inc' );
		include_once( $module_root_path . 'pafiledb/includes/pafiledb_constants.' . $phpEx );
				
		$file = basename( __FILE__ );
		$module['pafileDB_Download']['0_Configuration_title'] = 'modules/mx_pafiledb/admin/' . $file;
		return;
	}	
	
	$no_page_header = true;
	$module_root_path = './../';
	$mx_root_path = './../../../';
	
	define( 'MXBB_27x', file_exists( $mx_root_path . 'mx_login.php' ) );
	
	require( $mx_root_path . 'extension.inc' );
	require( $mx_root_path . 'admin/pagestart.' . $phpEx );
	
	include_once( $module_root_path . 'pafiledb/pafiledb_common.' . $phpEx );
	include_once( $module_root_path . 'pafiledb/includes/functions_admin.' . $phpEx );
	
}

$submit = ( isset( $_POST['submit'] ) ) ? true : false;
$size = ( isset( $_POST['max_size'] ) ) ? $_POST['max_size'] : '';

$sql = 'SELECT *
	FROM ' . PA_CONFIG_TABLE;

if ( !$result = $db->sql_query( $sql ) )
{
	mx_message_die( CRITICAL_ERROR, "Could not query config information in admin_board", "", __LINE__, __FILE__, $sql );
}
else
{
	while ( $row = $db->sql_fetchrow( $result ) )
	{
		$config_name = $row['config_name'];
		$config_value = $row['config_value'];
		$pa_config[$config_name] = $config_value;

		$new[$config_name] = ( isset( $_POST[$config_name] ) ) ? $_POST[$config_name] : $pa_config[$config_name];

		if ( ( empty( $size ) ) && ( !$submit ) && ( $config_name == 'max_file_size' ) )
		{
			$size = ( intval( $pa_config[$config_name] ) >= 1048576 ) ? 'mb' : ( ( intval( $pa_config[$config_name] ) >= 1024 ) ? 'kb' : 'b' );
		}

		if ( ( !$submit ) && ( $config_name == 'max_file_size' ) )
		{
			if ( $new[$config_name] >= 1048576 )
			{
				$new[$config_name] = round( $new[$config_name] / 1048576 * 100 ) / 100;
			}
			else if ( $new[$config_name] >= 1024 )
			{
				$new[$config_name] = round( $new[$config_name] / 1024 * 100 ) / 100;
			}
		}

		if ( $submit )
		{
			if ( $config_name == 'max_file_size' )
			{
				$new[$config_name] = ( $size == 'kb' ) ? round( $new[$config_name] * 1024 ) : ( ( $size == 'mb' ) ? round( $new[$config_name] * 1048576 ) : $new[$config_name] );
			}

			if ( $config_name == 'tpl_php' && isset( $_POST[$config_name] ) && $new[$config_name] != $pa_config[$config_name] )
			{
				$pafiledb_template->compile_cache_clear();
			}

			$pafiledb_functions->set_config( $config_name, $new[$config_name] );
		}
	}

	if ( $submit )
	{
		$pafiledb_cache->unload();
		$message = $lang['Settings_changed'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_settings.$phpEx" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_admin_index'], '<a href="' . append_sid( $mx_root_path . "admin/index.$phpEx?pane=right" ) . '">', '</a>' );
		mx_message_die( GENERAL_MESSAGE, $message );
	}
}

$template->set_filenames( array( 'admin' => 'admin/pa_admin_settings.tpl' ) ); 

$cat_auth_levels = array( 'ALL', 'REG', 'PRIVATE', 'MOD', 'ADMIN' );
$cat_auth_const = array( AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN );
$global_auth = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' );
$auth_select = array();

foreach( $global_auth as $auth )
{
	$auth_select[$auth] = '&nbsp;<select name="' . $auth . '">';
	for( $k = 0; $k < count( $cat_auth_levels ); $k++ )
	{
		$selected = ( $new[$auth] == $cat_auth_const[$k] ) ? ' selected="selected"' : '';
		$auth_select[$auth] .= '<option value="' . $cat_auth_const[$k] . '"' . $selected . '>' . $lang['Category_' . $cat_auth_levels[$k]] . '</option>';
	}
	$auth_select[$auth] .= '</select>&nbsp;';
}

//
// General Settings
//
$module_name = $new['module_name'];

$enable_module_yes = ( $new['enable_module'] ) ? "checked=\"checked\"" : "";
$enable_module_no = ( !$new['enable_module'] ) ? "checked=\"checked\"" : "";

$wysiwyg_path = $new['wysiwyg_path'];
$upload_dir = $new['upload_dir'];
$screenshots_dir = $new['screenshots_dir'];

//
// File
//
$hotlink_prevent_yes = ( $new['hotlink_prevent'] ) ? "checked=\"checked\"" : "";
$hotlink_prevent_no = ( !$new['hotlink_prevent'] ) ? "checked=\"checked\"" : "";

$hotlink_allowed = $new['hotlink_allowed'];

$php_template_yes = ( $new['settings_tpl_php'] ) ? "checked=\"checked\"" : "";
$php_template_no = ( !$new['settings_tpl_php'] ) ? "checked=\"checked\"" : "";

$max_file_size = $new['max_file_size'];

$forbidden_extensions = $new['forbidden_extensions'];

//
// Appearance
//
$pagination = $new['pagination'];

$sort_method_options = array();
$sort_method_options = array( "file_name", "file_time", "file_rating", "file_dls", "file_update_time" );

$sort_method_list = '<select name="sort_method">';
for( $j = 0; $j < count( $sort_method_options ); $j++ )
{
	if ( $new['sort_method'] == $sort_method_options[$j] )
	{
		$status = "selected";
	}
	else
	{
		$status = '';
	}
	$sort_method_list .= '<option value="' . $sort_method_options[$j] . '" ' . $status . '>' . $sort_method_options[$j] . '</option>';
}
$sort_method_list .= '</select>';

$sort_order_options = array();
$sort_order_options = array( "DESC", "ASC" );

$sort_order_list = '<select name="sort_order">';

for( $j = 0; $j < count( $sort_order_options ); $j++ )
{
	if ( $new['sort_order'] == $sort_order_options[$j] )
	{
		$status = "selected";
	}
	else
	{
		$status = '';
	}
	$sort_order_list .= '<option value="' . $sort_order_options[$j] . '" ' . $status . '>' . $sort_order_options[$j] . '</option>';
}
$sort_order_list .= '</select>';

$settings_topnumber = $new['settings_topnumber'];
$settings_newdays = $new['settings_newdays'];

$view_all_yes = ( $new['settings_viewall'] ) ? "checked=\"checked\"" : "";
$view_all_no = ( !$new['settings_viewall'] ) ? "checked=\"checked\"" : "";

//
// Instructions
//
$pretext_show = ( $new['show_pretext'] ) ? "checked=\"checked\"" : "";
$pretext_hide = ( !$new['show_pretext'] ) ? "checked=\"checked\"" : "";

$pt_header = $new['pt_header'];
$pt_body = $new['pt_body'];


//
// Comments (default settings)
//
$use_comments_yes = ( $new['use_comments'] ) ? "checked=\"checked\"" : "";
$use_comments_no = ( !$new['use_comments'] ) ? "checked=\"checked\"" : "";

$internal_comments_internal = ( $new['internal_comments'] ) ? "checked=\"checked\"" : "";
$internal_comments_phpbb = ( !$new['internal_comments'] ) ? "checked=\"checked\"" : "";

$del_topic_yes = ( $new['del_topic'] ) ? "checked=\"checked\"" : "";
$del_topic_no = ( !$new['del_topic'] ) ? "checked=\"checked\"" : "";

$autogenerate_comments_yes = ( $new['autogenerate_comments'] ) ? "checked=\"checked\"" : "";
$autogenerate_comments_no = ( !$new['autogenerate_comments'] ) ? "checked=\"checked\"" : "";

$allow_comment_wysiwyg_yes = ( $new['allow_comment_wysiwyg'] ) ? "checked=\"checked\"" : "";
$allow_comment_wysiwyg_no = ( !$new['allow_comment_wysiwyg'] ) ? "checked=\"checked\"" : "";

$allow_comment_html_yes = ( $new['allow_comment_html'] ) ? "checked=\"checked\"" : "";
$allow_comment_html_no = ( !$new['allow_comment_html'] ) ? "checked=\"checked\"" : "";

$allowed_comment_html_tags = $new['allowed_comment_html_tags'];

$allow_comment_bbcode_yes = ( $new['allow_comment_bbcode'] ) ? "checked=\"checked\"" : "";
$allow_comment_bbcode_no = ( !$new['allow_comment_bbcode'] ) ? "checked=\"checked\"" : "";

$allow_comment_smilies_yes = ( $new['allow_comment_smilies'] ) ? "checked=\"checked\"" : "";
$allow_comment_smilies_no = ( !$new['allow_comment_smilies'] ) ? "checked=\"checked\"" : "";

$allow_comment_links_yes = ( $new['allow_comment_links'] ) ? "checked=\"checked\"" : "";
$allow_comment_links_no = ( !$new['allow_comment_links'] ) ? "checked=\"checked\"" : "";

$allow_comment_images_yes = ( $new['allow_comment_images'] ) ? "checked=\"checked\"" : "";
$allow_comment_images_no = ( !$new['allow_comment_images'] ) ? "checked=\"checked\"" : "";

$no_comment_link_message = $new['no_comment_link_message'];
$no_comment_image_message = $new['no_comment_image_message'];

$max_comment_chars = $new['max_comment_chars'];
$max_comment_subject_chars = $new['max_comment_subject_chars'];

$format_comment_truncate_links_yes = ( $new['formatting_comment_truncate_links'] ) ? "checked=\"checked\"" : "";
$format_comment_truncate_links_no = ( !$new['formatting_comment_truncate_links'] ) ? "checked=\"checked\"" : "";

$format_comment_image_resize = $new['formatting_comment_image_resize'];

$format_comment_wordwrap_yes = ( $new['formatting_comment_wordwrap'] ) ? "checked=\"checked\"" : "";
$format_comment_wordwrap_no = ( !$new['formatting_comment_wordwrap'] ) ? "checked=\"checked\"" : "";

$comments_pag = $new['comments_pagination'];

//
// Ratings (default settings)
//
$use_ratings_yes = ( $new['use_ratings'] ) ? "checked=\"checked\"" : "";
$use_ratings_no = ( !$new['use_ratings'] ) ? "checked=\"checked\"" : "";

$votes_check_ip_yes = ( $new['votes_check_ip'] ) ? "checked=\"checked\"" : "";
$votes_check_ip_no = ( !$new['votes_check_ip'] ) ? "checked=\"checked\"" : "";

$votes_check_userid_yes = ( $new['votes_check_userid'] ) ? "checked=\"checked\"" : "";
$votes_check_userid_no = ( !$new['votes_check_userid'] ) ? "checked=\"checked\"" : "";

//
// Notifications
//
$notify_none = ( $new['notify'] == 0 ) ? "checked=\"checked\"" : "";
$notify_pm = ( $new['notify'] == 1 ) ? "checked=\"checked\"" : "";
$notify_email = ( $new['notify'] == 2 ) ? "checked=\"checked\"" : "";

$notify_group_list = mx_get_groups($new['notify_group'], 'notify_group');

$template->assign_vars( array( 
	'S_SETTINGS_ACTION' => append_sid( "admin_pa_settings.$phpEx" ),

	'L_CONFIGURATION_TITLE' => $lang['Panel_config_title'],
	'L_CONFIGURATION_EXPLAIN' => $lang['Panel_config_explain'],
	
	'L_RESET' => $lang['Reset'],
	'L_SUBMIT' => $lang['Submit'],
	'L_YES' => $lang['Yes'],
	'L_NO' => $lang['No'],	

	//
	// General
	//
	'L_GENERAL_TITLE' => $lang['General_title'],
		
	'L_MODULE_NAME' => $lang['Module_name'],
	'L_MODULE_NAME_EXPLAIN' => $lang['Module_name_explain'],
	'MODULE_NAME' => $module_name,
	
	'L_ENABLE_MODULE' => $lang['Enable_module'],
	'L_ENABLE_MODULE_EXPLAIN' => $lang['Enable_module_explain'],
	'S_ENABLE_MODULE_YES' => $enable_module_yes,
	'S_ENABLE_MODULE_NO' => $enable_module_no,
	
	'L_WYSIWYG_PATH' => $lang['Wysiwyg_path'],
	'L_WYSIWYG_PATH_EXPLAIN' => $lang['Wysiwyg_path_explain'],
	'WYSIWYG_PATH' => $wysiwyg_path,
	
	'L_UPLOAD_DIR' => $lang['Upload_directory'],
	'L_UPLOAD_DIR_EXPLAIN' => $lang['Upload_directory_explain'],
	'UPLOAD_DIR' => $upload_dir,

	'L_SCREENSHOT_DIR' => $lang['Screenshots_directory'],
	'L_SCREENSHOT_DIR_EXPLAIN' => $lang['Screenshots_directory_explain'],
	'SCREENSHOT_DIR' => $screenshots_dir,	
		
	//
	// FILE
	//	
	'L_FILE_TITLE' => $lang['File_title'],
	
	'L_HOTLINK' => $lang['Hotlink_prevent'],
	'L_HOTLINK_INFO' => $lang['Hotlinl_prevent_info'],
	'S_HOTLINK_YES' => $hotlink_prevent_yes,
	'S_HOTLINK_NO' => $hotlink_prevent_no,
		
	'L_HOTLINK_ALLOWED' => $lang['Hotlink_allowed'],
	'L_HOTLINK_ALLOWED_INFO' => $lang['Hotlink_allowed_info'],
	'HOTLINK_ALLOWED' => $hotlink_allowed,
	
	'L_PHP_TPL' => $lang['Php_template'],
	'L_PHP_TPL_INFO' => $lang['Php_template_info'],
	'S_PHP_TPL_YES' => $php_template_yes,
	'S_PHP_TPL_NO' => $php_template_no,	
	
	'L_MAX_FILE_SIZE' => $lang['Max_filesize'],
	'L_MAX_FILE_SIZE_INFO' => $lang['Max_filesize_explain'],
	'MAX_FILE_SIZE' => $max_file_size,
	'S_FILESIZE' => pa_size_select( 'max_size', $size ),
	
	'L_FORBIDDEN_EXTENSIONS' => $lang['Forbidden_extensions'],
	'L_FORBIDDEN_EXTENSIONS_EXPLAIN' => $lang['Forbidden_extensions_explain'],
	'FORBIDDEN_EXTENSIONS' => $forbidden_extensions,	
	
	
	//
	// Appearance
	//	
	'L_APPEARANCE_TITLE' => $lang['Appearance_title'],
	
	'L_PAGINATION' => $lang['Article_pag'],
	'L_PAGINATION_EXPLAIN' => $lang['Article_pag_explain'],
	'PAGINATION' => $pagination,
	
	'L_SORT_METHOD' => $lang['Sort_method'],
	'L_SORT_METHOD_EXPLAIN' => $lang['Sort_method_explain'],
	'SORT_METHOD' => $sort_method_list,

	'L_SORT_ORDER' => $lang['Sort_order'],
	'L_SORT_ORDER_EXPLAIN' => $lang['Sort_order_explain'],
	'SORT_ORDER' => $sort_order_list,	
	
	'L_TOPNUM' => $lang['Topnum'],
	'L_TOPNUMINFO' => $lang['Topnuminfo'],
	'SETTINGS_TOPNUMBER' => $settings_topnumber,
	
	'L_NFDAYS' => $lang['Nfdays'],
	'L_NFDAYSINFO' => $lang['Nfdaysinfo'],
	'SETTINGS_NEWDAYS' => $settings_newdays,

	'L_SHOW_VIEWALL' => $lang['Showva'],
	'L_VIEWALL_INFO' => $lang['Showvainfo'],
	'S_VIEW_ALL_YES' => $view_all_yes,
	'S_VIEW_ALL_NO' => $view_all_no,	
		
	//
	// Comments
	//
	'L_COMMENTS_TITLE' => $lang['Comments_title'],
	'L_COMMENTS_TITLE_EXPLAIN' => $lang['Comments_title_explain'],
	
	'L_USE_COMMENTS' => $lang['Use_comments'],
	'L_USE_COMMENTS_EXPLAIN' => $lang['Use_comments_explain'],
	'S_USE_COMMENTS_YES' => $use_comments_yes,
	'S_USE_COMMENTS_NO' => $use_comments_no,
	
	'L_INTERNAL_COMMENTS' => $lang['Internal_comments'],
	'L_INTERNAL_COMMENTS_EXPLAIN' => $lang['Internal_comments_explain'],
	'S_INTERNAL_COMMENTS_INTERNAL' => $internal_comments_internal,
	'S_INTERNAL_COMMENTS_PHPBB' => $internal_comments_phpbb,
	'L_INTERNAL_COMMENTS_INTERNAL' => $lang['Internal_comments_internal'],			
	'L_INTERNAL_COMMENTS_PHPBB' => $lang['Internal_comments_phpBB'],	

	'L_AUTOGENERATE_COMMENTS' => $lang['Autogenerate_comments'],
	'L_AUTOGENERATE_COMMENTS_EXPLAIN' => $lang['Autogenerate_comments_explain'],
	'S_AUTOGENERATE_COMMENTS_YES' => $autogenerate_comments_yes,
	'S_AUTOGENERATE_COMMENTS_NO' => $autogenerate_comments_no,	

	'L_ALLOW_COMMENT_WYSIWYG' => $lang['Allow_Wysiwyg'],
	'L_ALLOW_COMMENT_WYSIWYG_EXPLAIN' => $lang['Allow_Wysiwyg_explain'],
	'S_ALLOW_COMMENT_WYSIWYG_YES' => $allow_comment_wysiwyg_yes,
	'S_ALLOW_COMMENT_WYSIWYG_NO' => $allow_comment_wysiwyg_no,
		
	'L_ALLOW_COMMENT_HTML' => $lang['Allow_HTML'],
	'L_ALLOW_COMMENT_HTML_EXPLAIN' => $lang['Allow_html_explain'],
	'S_ALLOW_COMMENT_HTML_YES' => $allow_comment_html_yes,
	'S_ALLOW_COMMENT_HTML_NO' => $allow_comment_html_no,	

	'L_ALLOW_COMMENT_BBCODE' => $lang['Allow_BBCode'],
	'L_ALLOW_COMMENT_BBCODE_EXPLAIN' => $lang['Allow_bbcode_explain'],
	'S_ALLOW_COMMENT_BBCODE_YES' => $allow_comment_bbcode_yes,
	'S_ALLOW_COMMENT_BBCODE_NO' => $allow_comment_bbcode_no,

	'L_ALLOW_COMMENT_SMILIES' => $lang['Allow_smilies'],
	'L_ALLOW_COMMENT_SMILIES_EXPLAIN' => $lang['Allow_smilies_explain'],
	'S_ALLOW_COMMENT_SMILIES_YES' => $allow_comment_smilies_yes,
	'S_ALLOW_COMMENT_SMILIES_NO' => $allow_comment_smilies_no,

	'L_ALLOWED_COMMENT_HTML_TAGS' => $lang['Allowed_tags'],
	'L_ALLOWED_COMMENT_HTML_TAGS_EXPLAIN' => $lang['Allowed_tags_explain'],
	'ALLOWED_COMMENT_HTML_TAGS' => $allowed_comment_html_tags,
	
	'L_ALLOW_COMMENT_IMAGES' => $lang['Allow_images'],
	'L_ALLOW_COMMENT_IMAGES_EXPLAIN' => $lang['Allow_images_explain'],
	'S_ALLOW_COMMENT_IMAGES_YES' => $allow_comment_images_yes,
	'S_ALLOW_COMMENT_IMAGES_NO' => $allow_comment_images_no,

	'L_ALLOW_COMMENT_LINKS' => $lang['Allow_links'],
	'L_ALLOW_COMMENT_LINKS_EXPLAIN' => $lang['Allow_links_explain'],
	'S_ALLOW_COMMENT_LINKS_YES' => $allow_comment_links_yes,
	'S_ALLOW_COMMENT_LINKS_NO' => $allow_comment_links_no,	
	
	'L_COMMENT_LINKS_MESSAGE' => $lang['Allow_links_message'],
	'L_COMMENT_LINKS_MESSAGE_EXPLAIN' => $lang['Allow_links_message_explain'],
	'COMMENT_MESSAGE_LINK' => $no_comment_link_message,
	
	'L_COMMENT_IMAGES_MESSAGE' => $lang['Allow_images_message'],
	'L_COMMENT_IMAGES_MESSAGE_EXPLAIN' => $lang['Allow_images_message_explain'],
	'COMMENT_MESSAGE_IMAGE' => $no_comment_image_message,
	
	'L_COMMENT_MAX_SUBJECT_CHAR' => $lang['Max_subject_char'],
	'L_COMMENT_MAX_SUBJECT_CHAR_EXPLAIN' => $lang['Max_subject_char_explain'],
	'COMMENT_MAX_SUBJECT_CHAR' => $max_comment_subject_chars,

	'L_COMMENT_MAX_CHAR' => $lang['Max_char'],
	'L_COMMENT_MAX_CHAR_EXPLAIN' => $lang['Max_char_explain'],
	'COMMENT_MAX_CHAR' => $max_comment_chars,	
	
	'L_COMMENT_FORMAT_WORDWRAP' => $lang['Format_wordwrap'],
	'L_COMMENT_FORMAT_WORDWRAP_EXPLAIN' => $lang['Format_wordwrap_explain'],
	'S_COMMENT_FORMAT_WORDWRAP_YES' => $format_comment_wordwrap_yes,
	'S_COMMENT_FORMAT_WORDWRAP_NO' => $format_comment_wordwrap_no,

	'L_COMMENT_FORMAT_IMAGE_RESIZE' => $lang['Format_image_resize'],
	'L_COMMENT_FORMAT_IMAGE_RESIZE_EXPLAIN' => $lang['Format_image_resize_explain'],
	'COMMENT_FORMAT_IMAGE_RESIZE' => $format_comment_image_resize,

	'L_COMMENT_FORMAT_TRUNCATE_LINKS' => $lang['Format_truncate_links'],
	'L_COMMENT_FORMAT_TRUNCATE_LINKS_EXPLAIN' => $lang['Format_truncate_links_explain'],
	'S_COMMENT_FORMAT_TRUNCATE_LINKS_YES' => $format_comment_truncate_links_yes,
	'S_COMMENT_FORMAT_TRUNCATE_LINKS_NO' => $format_comment_truncate_links_no,	
	
	'L_COMMENTS_PAG' => $lang['Comments_pag'],
	'L_COMMENTS_PAG_EXPLAIN' => $lang['Comments_pag_explain'],
	'COMMENTS_PAG' => $comments_pag,	
	
	'L_DEL_TOPIC' => $lang['Del_topic'],
	'L_DEL_TOPIC_EXPLAIN' => $lang['Del_topic_explain'],
	'S_DEL_TOPIC_YES' => $del_topic_yes,
	'S_DEL_TOPIC_NO' => $del_topic_no,	
		
	//
	// Ratings
	//	
	'L_RATINGS_TITLE' => $lang['Ratings_title'],
	'L_RATINGS_TITLE_EXPLAIN' => $lang['Ratings_title_explain'],

	'L_USE_RATINGS' => $lang['Use_ratings'],
	'L_USE_RATINGS_EXPLAIN' => $lang['Use_ratings_explain'],
	'S_USE_RATINGS_YES' => $use_ratings_yes,
	'S_USE_RATINGS_NO' => $use_ratings_no,

	'L_VOTES_CHECK_IP' => $lang['Votes_check_ip'],
	'L_VOTES_CHECK_IP_EXPLAIN' => $lang['Votes_check_ip_explain'],
	'S_VOTES_CHECK_IP_YES' => $votes_check_ip_yes,
	'S_VOTES_CHECK_IP_NO' => $votes_check_ip_no,

	'L_VOTES_CHECK_USERID' => $lang['Votes_check_userid'],
	'L_VOTES_CHECK_USERID_EXPLAIN' => $lang['Votes_check_userid_explain'],
	'S_VOTES_CHECK_USERID_YES' => $votes_check_userid_yes,
	'S_VOTES_CHECK_USERID_NO' => $votes_check_userid_no,
		
	//
	// Instructions
	//	
	'L_INSTRUCTIONS_TITLE' => $lang['Instructions_title'],

	'L_SHOW' => $lang['Show'],
	'L_HIDE' => $lang['Hide'],
	'L_PRE_TEXT_NAME' => $lang['Pre_text_name'],
	'L_PRE_TEXT_HEADER' => $lang['Pre_text_header'],
	'L_PRE_TEXT_BODY' => $lang['Pre_text_body'],
	'L_PRE_TEXT_EXPLAIN' => $lang['Pre_text_explain'],
	'S_SHOW_PRETEXT' => $pretext_show,
	'S_HIDE_PRETEXT' => $pretext_hide,
	'L_PT_HEADER' => $pt_header,
	'L_PT_BODY' => $pt_body,
		
	//
	// Notifications
	//	
	'L_NOTIFICATIONS_TITLE' => $lang['Notifications_title'],

	'L_NOTIFY' => $lang['Notify'],
	'L_NOTIFY_EXPLAIN' => $lang['Notify_explain'],
	'L_EMAIL' => $lang['Email'],
	'L_PM' => $lang['PM'],
	'S_NOTIFY_NONE' => $notify_none,
	'S_NOTIFY_EMAIL' => $notify_email,
	'S_NOTIFY_PM' => $notify_pm,

	'L_NOTIFY_GROUP' => $lang['Notify_group'],
	'L_NOTIFY_GROUP_EXPLAIN' => $lang['Notify_group_explain'],
	'NOTIFY_GROUP' => $notify_group_list,	

	//
	// Permissions
	//
	'L_PERMISSION_SETTINGS' => $lang['Permission_settings'],
	
	'L_ATUH_SEARCH' => $lang['Auth_search'],
	'L_ATUH_SEARCH_INFO' => $lang['Auth_search_explain'],
	'S_ATUH_SEARCH' => $auth_select['auth_search'],
	
	'L_ATUH_STATS' => $lang['Auth_stats'],
	'L_ATUH_STATS_INFO' => $lang['Auth_stats_explain'],
	'S_ATUH_STATS' => $auth_select['auth_stats'],
	
	'L_ATUH_TOPLIST' => $lang['Auth_toplist'],
	'S_ATUH_TOPLIST' => $auth_select['auth_toplist'],
	'L_ATUH_TOPLIST_INFO' => $lang['Auth_toplist_explain'],
	
	'L_ATUH_VIEWALL' => $lang['Auth_viewall'],
	'L_ATUH_VIEWALL_INFO' => $lang['Auth_viewall_explain'],
	'S_ATUH_VIEWALL' => $auth_select['auth_viewall'],
	
));

//	
// Output
//
include( $mx_root_path . 'admin/page_header_admin.' . $phpEx );
$template->pparse( 'admin' );
include( $mx_root_path . 'admin/page_footer_admin.' . $phpEx );

?>