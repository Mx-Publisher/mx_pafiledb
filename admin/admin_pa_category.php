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
 *    $Id: admin_pa_category.php,v 1.14 2005/12/11 16:19:20 jonohlsson Exp $
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
		$module['pafileDB_Download']['1_Cat_manage_title'] = $file;
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
		$module['pafileDB_Download']['1_Cat_manage_title'] = 'modules/mx_pafiledb/admin/' . $file;
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

$pafiledb->init();

$mode = ( isset( $_REQUEST['mode'] ) ) ? htmlspecialchars( $_REQUEST['mode'] ) : '';
$cat_id = ( isset( $_REQUEST['cat_id'] ) ) ? intval( $_REQUEST['cat_id'] ) : 0;
$cat_id_other = ( isset( $_REQUEST['cat_id_other'] ) ) ? intval( $_REQUEST['cat_id_other'] ) : 0;

if ( $mode == 'do_add' && !$cat_id )
{
	$cat_id = $pafiledb->update_add_cat();
	$mode = 'add';
	if ( !sizeof( $pafiledb->error ) )
	{
		$pafiledb->_pafiledb();
		$message = $lang['Catadded'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_category.$phpEx" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_edit_permissions'], '<a href="' . append_sid( "admin_pa_catauth.$phpEx?cat_id=$cat_id" ) . '">', '</a>' );
		mx_message_die( GENERAL_MESSAGE, $message );
	}
}
elseif ( $mode == 'do_add' && $cat_id )
{
	$cat_id = $pafiledb->update_add_cat( $cat_id );
	if ( !sizeof( $pafiledb->error ) )
	{
		$pafiledb->_pafiledb();
		$message = $lang['Catedited'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_category.$phpEx" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_edit_permissions'], '<a href="' . append_sid( "admin_pa_catauth.$phpEx?cat_id=$cat_id" ) . '">', '</a>' );
		mx_message_die( GENERAL_MESSAGE, $message );
	}
}
elseif ( $mode == 'do_delete' )
{
	$pafiledb->delete_cat( $cat_id );
	if ( !sizeof( $pafiledb->error ) )
	{
		$pafiledb->_pafiledb();
		$message = $lang['Catsdeleted'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_category.$phpEx" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_admin_index'], '<a href="' . append_sid( $mx_root_path . "admin/index.$phpEx?pane=right" ) . '">', '</a>' );
		mx_message_die( GENERAL_MESSAGE, $message );
	}
}
elseif ( $mode == 'cat_order' )
{
	$pafiledb->order_cat( $cat_id_other );
}
elseif ( $mode == 'sync' )
{
	$pafiledb->sync( $cat_id_other );
}
elseif ( $mode == 'sync_all' )
{
	$pafiledb->sync_all();
}

switch ( $mode )
{
	case '':
	case 'cat_order':
	case 'sync':
	default:
		$template_file = 'admin/pa_admin_cat.tpl';
		$l_title = $lang['Panel_cat_title'];
		$l_explain = $lang['Panel_cat_explain'];
		$s_hidden_fields = '<input type="hidden" name="mode" value="add">';
		break;
	case 'add':
		$template_file = 'admin/pa_admin_cat_edit.tpl';
		$l_title = $lang['Acattitle'];
		$l_explain = $lang['Catexplain'];
		$s_hidden_fields = '<input type="hidden" name="mode" value="do_add">';
		break;
	case 'edit':
		$template_file = 'admin/pa_admin_cat_edit.tpl';
		$l_title = $lang['Ecattitle'];
		$l_explain = $lang['Catexplain'];
		$s_hidden_fields = '<input type="hidden" name="mode" value="do_add">';
		$s_hidden_fields .= '<input type="hidden" name="cat_id" value="' . $cat_id . '">';
		break;
	case 'delete':
		$template_file = 'admin/pa_admin_cat_delete.tpl';
		$l_title = $lang['Dcattitle'];
		$l_explain = $lang['Catexplain'];
		$s_hidden_fields = '<input type="hidden" name="mode" value="do_delete">';
		break;
}

$pafiledb_template->set_filenames( array( 'admin' => $template_file ) 
	);

$pafiledb_template->assign_vars( array( 
	'L_CAT_TITLE' => $l_title,
	'L_CAT_EXPLAIN' => $l_explain,
	
	'ERROR' => ( sizeof( $pafiledb->error ) ) ? implode( '<br />', $pafiledb->error ) : '',
	'S_HIDDEN_FIELDS' => $s_hidden_fields,
	'S_CAT_ACTION' => append_sid( "admin_pa_category.$phpEx" ) ) 
);

if ( $mode == '' || $mode == 'cat_order' || $mode == 'sync' || $mode == 'sync_all' )
{
	$pafiledb_template->assign_vars( array( 
		'L_CREATE_CATEGORY' => $lang['Create_category'],
		'L_EDIT' => $lang['Edit'],
		'L_DELETE' => $lang['Delete'],
		'L_MOVE_UP' => $lang['Move_up'],
		'L_MOVE_DOWN' => $lang['Move_down'],
		'L_SUB_CAT' => $lang['Sub_category'],
		'L_RESYNC' => $lang['Resync'] ) 
	);
	admin_cat_main( $cat_id );
}
elseif ( $mode == 'add' || $mode == 'edit' )
{
	if ( $mode == 'add' )
	{
		if ( !$_POST['cat_parent'] )
		{
			$cat_list .= '<option value="0" selected>' . $lang['None'] . '</option>';
		}
		else
		{
			$cat_list .= '<option value="0">' . $lang['None'] . '</option>';
		}
		
		$cat_list .= ( !$_POST['cat_parent'] ) ? $pafiledb->generate_jumpbox() : $pafiledb-generate_jumpboxn( 0, 0, array( $_POST['cat_parent'] => 1 ) );
		$checked_yes = ( $_POST['cat_allow_file'] ) ? ' checked' : '';
		$checked_no = ( !$_POST['cat_allow_file'] ) ? ' checked' : ''; 
		$cat_name = ( !empty( $_POST['cat_name'] ) ) ? $_POST['cat_name'] : '';
		$cat_desc = ( !empty( $_POST['cat_desc'] ) ) ? $_POST['cat_desc'] : '';
		
		//
		// Comments
		//
		$use_comments_yes = "";
		$use_comments_no = "";
		$use_comments_default = "checked=\"checked\"";

		$internal_comments_internal = "";
		$internal_comments_phpbb = "";
		$internal_comments_default = "checked=\"checked\"";
				
		$autogenerate_comments_yes = "";
		$autogenerate_comments_no = "";
		$autogenerate_comments_default = "checked=\"checked\"";

		$comments_forum_id = 0;
		
		//
		// Ratings
		//
		$use_ratings_yes = "";
		$use_ratings_no = "";
		$use_ratings_default = "checked=\"checked\"";
				
		//
		// Instructions
		//
		$pretext_show = "";
		$pretext_hide = "";
		$pretext_default = "checked=\"checked\"";
			
		//
		// Notification
		//
		$notify_none = "";
		$notify_pm = "";
		$notify_email = "";
		$notify_default = "checked=\"checked\"";
				
		$notify_group_list = mx_get_groups('', 'notify_group');		
	}
	else
	{
		if ( !$pafiledb->cat_rowset[$cat_id]['cat_parent'] )
		{
			$cat_list .= '<option value="0" selected>' . $lang['None'] . '</option>\n';
		}
		else
		{
			$cat_list .= '<option value="0">' . $lang['None'] . '</option>\n';
		}
		$cat_list .= $pafiledb->generate_jumpbox( 0, 0, array( $pafiledb->cat_rowset[$cat_id]['cat_parent'] => 1 ) );

		if ( $pafiledb->cat_rowset[$cat_id]['cat_allow_file'] )
		{
			$checked_yes = ' checked';
			$checked_no = '';
		}
		else
		{
			$checked_yes = '';
			$checked_no = ' checked';
		}

		$cat_name = $pafiledb->cat_rowset[$cat_id]['cat_name'];
		$cat_desc = $pafiledb->cat_rowset[$cat_id]['cat_desc'];
		
		//
		// Comments
		//
		$use_comments_yes = ( $pafiledb->cat_rowset[$cat_id]['cat_allow_comments'] == 1 ) ? "checked=\"checked\"" : "";
		$use_comments_no = ( $pafiledb->cat_rowset[$cat_id]['cat_allow_comments'] == 0 ) ? "checked=\"checked\"" : "";
		$use_comments_default = ( $pafiledb->cat_rowset[$cat_id]['cat_allow_comments'] == -1 ) ? "checked=\"checked\"" : "";

		$internal_comments_internal = ( $pafiledb->cat_rowset[$cat_id]['internal_comments'] == 1 ) ? "checked=\"checked\"" : "";
		$internal_comments_phpbb = ( $pafiledb->cat_rowset[$cat_id]['internal_comments'] == 0 ) ? "checked=\"checked\"" : "";
		$internal_comments_default = ( $pafiledb->cat_rowset[$cat_id]['internal_comments'] == -1 ) ? "checked=\"checked\"" : "";
				
		$comments_forum_id = $pafiledb->cat_rowset[$cat_id]['comments_forum_id'];
				
		$autogenerate_comments_yes = ( $pafiledb->cat_rowset[$cat_id]['autogenerate_comments'] == 1 ) ? "checked=\"checked\"" : "";
		$autogenerate_comments_no = ( $pafiledb->cat_rowset[$cat_id]['autogenerate_comments'] == 0 ) ? "checked=\"checked\"" : "";
		$autogenerate_comments_default = ( $pafiledb->cat_rowset[$cat_id]['autogenerate_comments'] == -1 ) ? "checked=\"checked\"" : "";

		//
		// Ratings
		//
		$use_ratings_yes = ( $pafiledb->cat_rowset[$cat_id]['cat_allow_ratings'] == 1 ) ? "checked=\"checked\"" : "";
		$use_ratings_no = ( $pafiledb->cat_rowset[$cat_id]['cat_allow_ratings'] == 0 ) ? "checked=\"checked\"" : "";
		$use_ratings_default = ( $pafiledb->cat_rowset[$cat_id]['cat_allow_ratings'] == -1 ) ? "checked=\"checked\"" : "";
				
		//
		// Instructions
		//
		$pretext_show = ( $pafiledb->cat_rowset[$cat_id]['show_pretext'] == 1 ) ? "checked=\"checked\"" : "";
		$pretext_hide = ( $pafiledb->cat_rowset[$cat_id]['show_pretext'] == 0 ) ? "checked=\"checked\"" : "";
		$pretext_default = ( $pafiledb->cat_rowset[$cat_id]['show_pretext'] == -1 ) ? "checked=\"checked\"" : "";
				
		//
		// Notification
		//
		$notify_none = ( $pafiledb->cat_rowset[$cat_id]['notify'] == 0 ) ? "checked=\"checked\"" : "";
		$notify_pm = ( $pafiledb->cat_rowset[$cat_id]['notify'] == 1 ) ? "checked=\"checked\"" : "";
		$notify_email = ( $pafiledb->cat_rowset[$cat_id]['notify'] == 2 ) ? "checked=\"checked\"" : "";
		$notify_default = ( $pafiledb->cat_rowset[$cat_id]['notify'] == -1 ) ? "checked=\"checked\"" : "";
				
		$notify_group_list = mx_get_groups($pafiledb->cat_rowset[$cat_id]['notify_group'], 'notify_group');		
	}

	$pafiledb_template->assign_vars( array( 
		'CAT_NAME' => $cat_name,
		'CAT_DESC' => $cat_desc,
		'CHECKED_YES' => $checked_yes,
		'CHECKED_NO' => $checked_no, 
		
		//
		// Comments
		//
		'L_COMMENTS_TITLE' => $lang['Comments_title'],
				
		'L_USE_COMMENTS' => $lang['Use_comments'],
		'L_USE_COMMENTS_EXPLAIN' => $lang['Use_comments_explain'],
		'S_USE_COMMENTS_YES' => $use_comments_yes,
		'S_USE_COMMENTS_NO' => $use_comments_no,
		'S_USE_COMMENTS_DEFAULT' => $use_comments_default,
				
		'L_INTERNAL_COMMENTS' => $lang['Internal_comments'],
		'L_INTERNAL_COMMENTS_EXPLAIN' => $lang['Internal_comments_explain'],
		'S_INTERNAL_COMMENTS_INTERNAL' => $internal_comments_internal,
		'S_INTERNAL_COMMENTS_PHPBB' => $internal_comments_phpbb,
		'S_INTERNAL_COMMENTS_DEFAULT' => $internal_comments_default,
		'L_INTERNAL_COMMENTS_INTERNAL' => $lang['Internal_comments_internal'],			
		'L_INTERNAL_COMMENTS_PHPBB' => $lang['Internal_comments_phpBB'],				

		'L_FORUM_ID' => $lang['Forum_id'],
		'L_FORUM_ID_EXPLAIN' => $lang['Forum_id_explain'],
		'FORUM_LIST' => get_forums( $comments_forum_id ),
								
		'L_AUTOGENERATE_COMMENTS' => $lang['Autogenerate_comments'],
		'L_AUTOGENERATE_COMMENTS_EXPLAIN' => $lang['Autogenerate_comments_explain'],
		'S_AUTOGENERATE_COMMENTS_YES' => $autogenerate_comments_yes,
		'S_AUTOGENERATE_COMMENTS_NO' => $autogenerate_comments_no,
		'S_AUTOGENERATE_COMMENTS_DEFAULT' => $autogenerate_comments_default,
				
		//
		// Ratings
		//
		'L_RATINGS_TITLE' => $lang['Ratings_title'],
				
		'L_USE_RATINGS' => $lang['Use_ratings'],
		'L_USE_RATINGS_EXPLAIN' => $lang['Use_ratings_explain'],
		'S_USE_RATINGS_YES' => $use_ratings_yes,
		'S_USE_RATINGS_NO' => $use_ratings_no,	
		'S_USE_RATINGS_DEFAULT' => $use_ratings_default,	
				
		//
		// Instructions
		//			
		'L_INSTRUCTIONS_TITLE' => $lang['Instructions_title'],
				
		'L_PRE_TEXT_NAME' => $lang['Pre_text_name'],
		'L_PRE_TEXT_EXPLAIN' => $lang['Pre_text_explain'],
		'S_SHOW_PRETEXT' => $pretext_show,
		'S_HIDE_PRETEXT' => $pretext_hide,
		'S_DEFAULT_PRETEXT' => $pretext_default,
				
		'L_SHOW' => $lang['Show'],
		'L_HIDE' => $lang['Hide'],

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
		'S_NOTIFY_DEFAULT' => $notify_default,
			
		'L_NOTIFY_GROUP' => $lang['Notify_group'],
		'L_NOTIFY_GROUP_EXPLAIN' => $lang['Notify_group_explain'],
		'NOTIFY_GROUP' => $notify_group_list,		

		'L_CAT_NAME' => $lang['Catname'],
		'L_CAT_NAME_INFO' => $lang['Catnameinfo'],
		'L_CAT_DESC' => $lang['Catdesc'],
		'L_CAT_DESC_INFO' => $lang['Catdescinfo'],
		'L_CAT_PARENT' => $lang['Catparent'],
		'L_CAT_PARENT_INFO' => $lang['Catparentinfo'],
		'L_CAT_ALLOWFILE' => $lang['Allow_file'],
		'L_CAT_ALLOWFILE_INFO' => $lang['Allow_file_info'], 
		'L_CAT_ALLOWCOMMENTS' => $lang['Allow_comments'],
		'L_CAT_ALLOWCOMMENTS_INFO' => $lang['Allow_comments_info'],
		'L_CAT_ALLOWRATINGS' => $lang['Allow_ratings'],
		'L_CAT_ALLOWRATINGS_INFO' => $lang['Allow_ratings_info'],

		'L_DEFAULT' => $lang['Use_default'],	
		'L_NONE' => $lang['None'],
		'L_YES' => $lang['Yes'],
		'L_NO' => $lang['No'],
		'L_CAT_NAME_FIELD_EMPTY' => $lang['Cat_name_missing'],
		'S_CAT_LIST' => $cat_list ) 
	);
}
elseif ( $mode == 'delete' )
{
	$select_cat = $pafiledb->generate_jumpbox( 0, 0, array( $cat_id => 1 ) );
	$file_to_select_cat = $pafiledb->generate_jumpbox( 0, 0, '', true );

	$pafiledb_template->assign_vars( array( 
		'S_SELECT_CAT' => $select_cat,
		'S_FILE_SELECT_CAT' => $file_to_select_cat,

		'L_DELETE' => $lang['Delete'],
		'L_DO_FILE' => $lang['Delfiles'],
		'L_DO_CAT' => $lang['Do_cat'],
		'L_MOVE_TO' => $lang['Move_to'],
		'L_SELECT_CAT' => $lang['Select_a_Category'],
		'L_DELETE' => $lang['Delete'],
		'L_MOVE' => $lang['Move'] ) 
	);
}

include( $mx_root_path . 'admin/page_header_admin.' . $phpEx );
$pafiledb_template->display( 'admin' );

$pafiledb->_pafiledb();
$pafiledb_cache->unload();

include( $mx_root_path . 'admin/page_footer_admin.' . $phpEx );

?>