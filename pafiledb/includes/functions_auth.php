<?php
/** ------------------------------------------------------------------------
 *		Subject				: mxBB - a fully modular portal and CMS (for phpBB) 
 *		Author				: Jon Ohlsson and the mxBB Team
 *		Credits				: The phpBB Group & Marc Morisette, wGeric
 *		Copyright          	: (C) 2002-2005 mxBB Portal
 *		Email             	: jon@mxbb-portal.com
 *		Project site		: www.mxbb-portal.com
 * -------------------------------------------------------------------------
 * 
 *    $Id: functions_auth.php,v 1.1 2005/12/08 15:15:12 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
if ( !defined( 'IN_PORTAL' ) )
{
	die( 'Hacking attempt' );
}

// ===================================================
// pafiledb Auth class
// ===================================================
class mx_pafiledb_auth
{
	var $auth = array();
	var $auth_global = array();
		
	//
	// $c_access :: category access row
	// $u_access :: private access row
	//
	function auth( $c_access )
	{
		global $db, $db, $lang, $userdata, $pafiledb_config;

		$a_sql = 'a.auth_view, a.auth_read, a.auth_view_file, a.auth_edit_file, a.auth_delete_file, a.auth_upload, a.auth_download, a.auth_rate, a.auth_email, a.auth_view_comment, a.auth_post_comment, a.auth_edit_comment, a.auth_delete_comment, a.auth_mod, a.auth_search, a.auth_stats, a.auth_toplist, a.auth_viewall, a.auth_approval';
		$auth_fields = array( 'auth_view', 'auth_read', 'auth_view_file', 'auth_edit_file', 'auth_delete_file', 'auth_upload', 'auth_download', 'auth_rate', 'auth_email', 'auth_view_comment', 'auth_post_comment', 'auth_edit_comment', 'auth_delete_comment', 'auth_approval' );
		$auth_fields_global = array( 'auth_search', 'auth_stats', 'auth_toplist', 'auth_viewall' ); 
		
		// If the user isn't logged on then all we need do is check if the forum
		// has the type set to ALL, if yes they are good to go, if not then they
		// are denied access
		
		$u_access = array();
		$global_u_access = array();
		if ( $userdata['session_logged_in'] )
		{
			$sql = "SELECT a.cat_id, a.group_id, $a_sql 
				FROM " . PA_AUTH_ACCESS_TABLE . " a, " . USER_GROUP_TABLE . " ug 
				WHERE ug.user_id = {$userdata['user_id']}
					AND ug.user_pending = 0 
					AND a.group_id = ug.group_id";
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Failed obtaining category access control lists', '', __LINE__, __FILE__, $sql );
			}

			if ( $row = $db->sql_fetchrow( $result ) )
			{
				do
				{
					if ( $row['cat_id'] )
					{
						$u_access[$row['cat_id']][] = $row;
					}
					else
					{
						$global_u_access = $row;
					}
				}
				while ( $row = $db->sql_fetchrow( $result ) );
			}
		}

		$is_admin = ( $userdata['user_level'] == ADMIN && $userdata['session_logged_in'] ) ? true : 0;

		for( $i = 0; $i < count( $auth_fields ); $i++ )
		{
			$key = $auth_fields[$i]; 
			
			// If the user is logged on and the forum type is either ALL or REG then the user has access
			
			// If the type if ACL, MOD or ADMIN then we need to see if the user has specific permissions
			// to do whatever it is they want to do ... to do this we pull relevant information for the
			// user (and any groups they belong to)
			
			// Now we compare the users access level against the forums. We assume here that a moderator
			// and admin automatically have access to an ACL forum, similarly we assume admins meet an
			// auth requirement of MOD
			
			for( $k = 0; $k < count( $c_access ); $k++ )
			{
				$value = $c_access[$k][$key];
				$c_cat_id = $c_access[$k]['cat_id'];
				global $lang;

				switch ( $value )
				{
					case AUTH_ALL:
						$this->auth[$c_cat_id][$key] = true;
						$this->auth[$c_cat_id][$key . '_type'] = $lang['Auth_Anonymous_users'];
						break;

					case AUTH_REG:
						$this->auth[$c_cat_id][$key] = ( $userdata['session_logged_in'] ) ? true : 0;
						$this->auth[$c_cat_id][$key . '_type'] = $lang['Auth_Registered_Users'];
						break;

					case AUTH_ACL:
						$this->auth[$c_cat_id][$key] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user( AUTH_ACL, $key, $u_access[$c_cat_id], $is_admin ) : 0;
						$this->auth[$c_cat_id][$key . '_type'] = $lang['Auth_Users_granted_access'];
						break;

					case AUTH_MOD:
						$this->auth[$c_cat_id][$key] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user( AUTH_MOD, 'auth_mod', $u_access[$c_cat_id], $is_admin ) : 0;
						$this->auth[$c_cat_id][$key . '_type'] = $lang['Auth_Moderators'];
						break;

					case AUTH_ADMIN:
						$this->auth[$c_cat_id][$key] = $is_admin;
						$this->auth[$c_cat_id][$key . '_type'] = $lang['Auth_Administrators'];
						break;

					default:
						$this->auth[$c_cat_id][$key] = 0;
						break;
				}
			}
		}
		for( $k = 0; $k < count( $c_access ); $k++ )
		{
			$c_cat_id = $c_access[$k]['cat_id'];
			$this->auth[$c_cat_id]['auth_mod'] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user( AUTH_MOD, 'auth_mod', $u_access[$c_cat_id], $is_admin ) : 0;
		}

		for( $i = 0; $i < count( $auth_fields_global ); $i++ )
		{
			$key = $auth_fields_global[$i];
			$value = $pafiledb_config[$auth_fields_global[$i]];
			global $lang;

			switch ( $value )
			{
				case AUTH_ALL:
					$this->auth_global[$key] = true;
					$this->auth_global[$key . '_type'] = $lang['Auth_Anonymous_users'];
					break;

				case AUTH_REG:
					$this->auth_global[$key] = ( $userdata['session_logged_in'] ) ? true : 0;
					$this->auth_global[$key . '_type'] = $lang['Auth_Registered_Users'];
					break;

				case AUTH_ACL:
					$this->auth_global[$key] = ( $userdata['session_logged_in'] ) ? $this->global_auth_check_user( AUTH_ACL, $key, $global_u_access, $is_admin ) : 0;
					$this->auth_global[$key . '_type'] = $lang['Auth_Users_granted_access'];
					break;

				case AUTH_MOD:
					$this->auth_global[$key] = ( $userdata['session_logged_in'] ) ? $this->global_auth_check_user( AUTH_MOD, 'auth_mod', $global_u_access, $is_admin ) : 0;
					$this->auth_global[$key . '_type'] = $lang['Auth_Moderators'];
					break;

				case AUTH_ADMIN:
					$this->auth_global[$key] = $is_admin;
					$this->auth_global[$key . '_type'] = $lang['Auth_Administrators'];
					break;

				default:
					$this->auth_global[$key] = 0;
					break;
			}
		}
	}

	function auth_check_user( $type, $key, $u_access, $is_admin )
	{
		$auth_user = 0;

		if ( count( $u_access ) )
		{
			for( $j = 0; $j < count( $u_access ); $j++ )
			{
				$result = 0;
				switch ( $type )
				{
					case AUTH_ACL:
						$result = $u_access[$j][$key];

					case AUTH_MOD:
						$result = $result || $u_access[$j]['auth_mod'];

					case AUTH_ADMIN:
						$result = $result || $is_admin;
						break;
				}

				$auth_user = $auth_user || $result;
			}
		}
		else
		{
			$auth_user = $is_admin;
		}

		return $auth_user;
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
					$result = $result || $this->is_moderator();

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

	function is_moderator()
	{
		if ( !empty( $this->auth ) )
		{
			foreach( $this->auth as $cat_id => $auth_fields )
			{
				if ( $auth_fileds['auth_mod'] )
				{
					return true;
				}
			}
			return false;
		}
		return false;
	}	
}
?>