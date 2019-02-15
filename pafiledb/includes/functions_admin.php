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
 *    $Id: functions_admin.php,v 1.1 2005/12/11 16:19:20 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

function admin_display_cat_auth( $cat_parent = 0, $depth = 0 )
{
	global $pafiledb, $phpbb_root_path, $pafiledb_template, $phpEx;
	global $cat_auth_fields, $cat_auth_const, $cat_auth_levels, $lang;
	$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
	if ( isset( $pafiledb->subcat_rowset[$cat_parent] ) )
	{
		foreach( $pafiledb->subcat_rowset[$cat_parent] as $sub_cat_id => $cat_data )
		{
			$pafiledb_template->assign_block_vars( 'cat_row', array( 'CATEGORY_NAME' => $cat_data['cat_name'],
					'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] ) ? false : true,
					'PRE' => $pre,
					'U_CAT' => append_sid( "admin_pa_catauth.$phpEx?cat_parent=$sub_cat_id" ) ) 
				);

			for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
			{
				$custom_auth[$j] = '&nbsp;<select name="' . $cat_auth_fields[$j] . '[' . $sub_cat_id . ']' . '">';

				for( $k = 0; $k < count( $cat_auth_levels ); $k++ )
				{
					$selected = ( $cat_data[$cat_auth_fields[$j]] == $cat_auth_const[$k] ) ? ' selected="selected"' : '';
					$custom_auth[$j] .= '<option value="' . $cat_auth_const[$k] . '"' . $selected . '>' . $lang['Category_' . $cat_auth_levels[$k]] . '</option>';
				}
				$custom_auth[$j] .= '</select>&nbsp;';

				$pafiledb_template->assign_block_vars( 'cat_row.cat_auth_data', array( 'S_AUTH_LEVELS_SELECT' => $custom_auth[$j] ) 
					);
			}
			admin_display_cat_auth( $sub_cat_id, $depth + 1 );
		}
		return;
	}
	return;
}

function admin_display_cat_auth_ug( $cat_parent = 0, $depth = 0 )
{
	global $pafiledb, $phpbb_root_path, $pafiledb_template, $phpEx;
	global $cat_auth_fields, $optionlist_mod, $optionlist_acl_adv;
	$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
	if ( isset( $pafiledb->subcat_rowset[$cat_parent] ) )
	{
		foreach( $pafiledb->subcat_rowset[$cat_parent] as $sub_cat_id => $cat_data )
		{
			$pafiledb_template->assign_block_vars( 'cat_row', array( 'CAT_NAME' => $cat_data['cat_name'],
					'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] ) ? false : true,
					'PRE' => $pre,

					'U_CAT' => append_sid( "admin_pa_catauth.$phpEx?cat_id=$sub_cat_id" ),

					'S_MOD_SELECT' => $optionlist_mod[$sub_cat_id] ) 
				);

			for( $j = 0; $j < count( $cat_auth_fields ); $j++ )
			{
				$pafiledb_template->assign_block_vars( 'cat_row.aclvalues', array( 'S_ACL_SELECT' => $optionlist_acl_adv[$sub_cat_id][$j] ) 
					);
			}
			admin_display_cat_auth_ug( $sub_cat_id, $depth + 1 );
		}
		return;
	}
	return;
}

function admin_cat_main( $cat_parent = 0, $depth = 0 )
{
	global $pafiledb, $phpbb_root_path, $pafiledb_template, $phpEx;

	$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
	if ( isset( $pafiledb->subcat_rowset[$cat_parent] ) )
	{
		foreach( $pafiledb->subcat_rowset[$cat_parent] as $subcat_id => $cat_data )
		{
			$pafiledb_template->assign_block_vars( 'cat_row', array( 'IS_HIGHER_CAT' => ( $cat_data['cat_allow_file'] == PA_CAT_ALLOW_FILE ) ? false : true,
					'U_CAT' => append_sid( 'admin_pa_category.php?cat_id=' . $subcat_id ),
					'U_CAT_EDIT' => append_sid( "admin_pa_category.$phpEx?mode=edit&amp;cat_id=$subcat_id" ),
					'U_CAT_DELETE' => append_sid( "admin_pa_category.$phpEx?mode=delete&amp;cat_id=$subcat_id" ),
					'U_CAT_MOVE_UP' => append_sid( "admin_pa_category.$phpEx?mode=cat_order&amp;move=-15&amp;cat_id_other=$subcat_id" ),
					'U_CAT_MOVE_DOWN' => append_sid( "admin_pa_category.$phpEx?mode=cat_order&amp;move=15&amp;cat_id_other=$subcat_id" ),
					'U_CAT_RESYNC' => append_sid( "admin_pa_category.$phpEx?mode=sync&amp;cat_id_other=$subcat_id" ),
					'CAT_NAME' => $cat_data['cat_name'],
					'PRE' => $pre ) 
				);
			admin_cat_main( $subcat_id, $depth + 1 );
		}
		return;
	}
	return;
}

function get_forums( $sel_id = 0 )
{
	global $db;

	$sql = "SELECT forum_id, forum_name
		FROM " . FORUMS_TABLE;

	if ( !$result = $db->sql_query( $sql ) )
	{
		mx_message_die( GENERAL_ERROR, "Couldn't get list of forums", "", __LINE__, __FILE__, $sql );
	}

	$forumlist = '<select name="forum_id">';

	if ( $sel_id == 0 )
	{
		$forumlist .= '<option value="0" selected > Select a Forum !</option>';
	}
	
	while ( $row = $db->sql_fetchrow( $result ) )
	{
		if ( $sel_id == $row['forum_id'] )
		{
			$status = "selected";
		}
		else
		{
			$status = '';
		}
		$forumlist .= '<option value="' . $row['forum_id'] . '" ' . $status . '>' . $row['forum_name'] . '</option>';
	}

	$forumlist .= '</select>';

	return $forumlist;
}

function pa_size_select( $select_name, $size_compare )
{
	global $lang;

	$size_types_text = array( $lang['Bytes'], $lang['KB'], $lang['MB'] );
	$size_types = array( 'b', 'kb', 'mb' );

	$select_field = '<select name="' . $select_name . '">';

	for ( $i = 0; $i < count( $size_types_text ); $i++ )
	{
		$selected = ( $size_compare == $size_types[$i] ) ? ' selected="selected"' : '';

		$select_field .= '<option value="' . $size_types[$i] . '"' . $selected . '>' . $size_types_text[$i] . '</option>';
	}

	$select_field .= '</select>';

	return ( $select_field );
}

function global_auth_check_user( $type, $key, $global_u_access, $is_admin )
{
	$auth_user = 0;

	if ( !empty( $global_u_access ) )
	{
		$result = 0;
		switch ( $type )
		{
			case AUTH_ACL:
				$result = $global_u_access[$key];

			case AUTH_MOD:
				$result = $result || is_moderator( $global_u_access['group_id'] );

			case AUTH_ADMIN:
				$result = $result || $is_admin;
				break;
		}

		$auth_user = $auth_user || $result;
	}
	else
	{
		$auth_user = $is_admin;
	}

	return $auth_user;
}

function is_moderator( $group_id )
{
	static $is_mod = false;

	if ( $is_mod !== false )
	{
		return $is_mod;
	}

	global $db;

	$sql = "SELECT * 
		FROM " . PA_AUTH_ACCESS_TABLE . " 
		WHERE group_id = $group_id
		AND auth_mod = '1'";

	if ( !( $result = $db->sql_query( $sql ) ) )
	{
		mx_message_die( GENERAL_ERROR, "Couldn't check for moderator $sql", "", __LINE__, __FILE__, $sql );
	}

	return ( $is_mod = ( $db->sql_fetchrow( $result ) ) ? 1 : 0 );
}
?>