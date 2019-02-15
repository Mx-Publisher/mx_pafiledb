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
 *    $Id: functions.php,v 1.18 2005/12/08 15:15:12 jonohlsson Exp $
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

// =========================================
// This class is used for general pafiledb handling
// =========================================
class pafiledb_functions
{
	function set_config( $config_name, $config_value )
	{
		global $pafiledb_cache, $pafiledb_config, $db;

		$sql = "UPDATE " . PA_CONFIG_TABLE . " SET
			config_value = '" . str_replace( "\'", "''", $config_value ) . "'
			WHERE config_name = '$config_name'";
		
		if ( !$db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, "Failed to update pafiledb configuration for $config_name", "", __LINE__, __FILE__, $sql );
		}

		if ( !$db->sql_affectedrows() && !isset( $pafiledb_config[$config_name] ) )
		{
			$sql = 'INSERT INTO ' . PA_CONFIG_TABLE . " (config_name, config_value)
				VALUES ('$config_name', '" . str_replace( "\'", "''", $config_value ) . "')";

			if ( !$db->sql_query( $sql ) )
			{
				mx_message_die( GENERAL_ERROR, "Failed to update pafiledb configuration for $config_name", "", __LINE__, __FILE__, $sql );
			}
		}

		$pafiledb_config[$config_name] = $config_value;
		$pafiledb_cache->destroy( 'config' );
	}

	function pafiledb_config()
	{
		global $db;

		$sql = "SELECT * 
			FROM " . PA_CONFIG_TABLE;

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt query pafiledb configuration', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$pafiledb_config[$row['config_name']] = trim( $row['config_value'] );
		}

		$db->sql_freeresult( $result );

		return ( $pafiledb_config );
	}

	// ===================================================
	// since that I can't use the original function with new template system
	// I just copy it and chagne it
	// ===================================================
	function pa_generate_smilies( $mode, $page_id )
	{
		global $db, $board_config, $pafiledb_template, $lang, $images, $theme, $phpEx, $phpbb_root_path;
		global $user_ip, $session_length, $starttime;
		global $userdata; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$inline_columns = 4;
		$inline_rows = 5;
		$window_columns = 8;

		if ( $mode == 'window' )
		{
			$userdata = session_pagestart( $user_ip, $page_id );
			init_userprefs( $userdata );

			$gen_simple_header = true;

			$page_title = $lang['Review_topic'] . " - $topic_title";

			include( $mx_root_path . 'includes/page_header.' . $phpEx );

			$pafiledb_template->set_filenames( array( 'smiliesbody' => 'posting_smilies.tpl' ) 
				);
		}

		$sql = "SELECT emoticon, code, smile_url   
			FROM " . SMILIES_TABLE . " 
			ORDER BY smilies_id";
		if ( $result = $db->sql_query( $sql ) )
		{
			$num_smilies = 0;
			$rowset = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				if ( empty( $rowset[$row['smile_url']] ) )
				{
					$rowset[$row['smile_url']]['code'] = str_replace( "'", "\\'", str_replace( '\\', '\\\\', $row['code'] ) );
					$rowset[$row['smile_url']]['emoticon'] = $row['emoticon'];
					$num_smilies++;
				}
			}

			if ( $num_smilies )
			{
				$smilies_count = ( $mode == 'inline' ) ? min( 19, $num_smilies ) : $num_smilies;
				$smilies_split_row = ( $mode == 'inline' ) ? $inline_columns - 1 : $window_columns - 1;

				$s_colspan = 0;
				$row = 0;
				$col = 0;

				while ( list( $smile_url, $data ) = @each( $rowset ) )
				{
					if ( !$col )
					{
						$pafiledb_template->assign_block_vars( 'smilies_row', array() );
					}

					$pafiledb_template->assign_block_vars( 'smilies_row.smilies_col', array( 
							'SMILEY_CODE' => $data['code'],
							'SMILEY_IMG' => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smile_url,
							'SMILEY_DESC' => $data['emoticon'] ) 
						);

					$s_colspan = max( $s_colspan, $col + 1 );

					if ( $col == $smilies_split_row )
					{
						if ( $mode == 'inline' && $row == $inline_rows - 1 )
						{
							break;
						}
						$col = 0;
						$row++;
					}
					else
					{
						$col++;
					}
				}

				if ( $mode == 'inline' && $num_smilies > $inline_rows * $inline_columns )
				{
					$pafiledb_template->assign_block_vars( 'switch_smilies_extra', array() );

					$pafiledb_template->assign_vars( array( 
							'L_MORE_SMILIES' => $lang['More_emoticons'],
							'U_MORE_SMILIES' => append_sid( $phpbb_root_path . "posting.$phpEx?mode=smilies" ) ) 
						);
				}

				$pafiledb_template->assign_vars( array( 'L_EMOTICONS' => $lang['Emoticons'],
						'L_CLOSE_WINDOW' => $lang['Close_window'],
						'S_SMILIES_COLSPAN' => $s_colspan ) 
					);
			}
		}

		if ( $mode == 'window' )
		{
			$pafiledb_template->display( 'smiliesbody' );
			include( $mx_root_path . 'includes/page_tail.' . $phpEx );
		}
	}
		
	function post_icons( $file_posticon = '' )
	{
		global $lang, $phpbb_root_path; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx;
		$curicons = 1;

		if ( $file_posticon == 'none' || $file_posticon == 'none.gif' or empty( $file_posticon ) )
		{
			$posticons .= '<input type="radio" name="posticon" value="none" checked><a class="gensmall">' . $lang['None'] . '</a>&nbsp;';
		}
		else
		{
			$posticons .= '<input type="radio" name="posticon" value="none"><a class="gensmall">' . $lang['None'] . '</a>&nbsp;';
		}

		$handle = @opendir( $module_root_path . ICONS_DIR );

		while ( $icon = @readdir( $handle ) )
		{
			if ( $icon !== '.' && $icon !== '..' && $icon !== 'index.htm' )
			{
				if ( $file_posticon == $icon )
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '" checked><img src="' . $module_root_path . ICONS_DIR . $icon . '">&nbsp;';
				}
				else
				{
					$posticons .= '<input type="radio" name="posticon" value="' . $icon . '"><img src="' . $module_root_path . ICONS_DIR . $icon . '">&nbsp;';
				}

				$curicons++;

				if ( $curicons == 8 )
				{
					$posticons .= '<br>';
					$curicons = 0;
				}
			}
		}
		@closedir( $handle );
		return $posticons;
	}

	function license_list( $license_id = 0 )
	{
		global $db, $lang;

		if ( $license_id == 0 )
		{
			$list .= '<option calue="0" selected>' . $lang['None'] . '</option>';
		}
		else
		{
			$list .= '<option calue="0">' . $lang['None'] . '</option>';
		}

		$sql = 'SELECT * 
			FROM ' . PA_LICENSE_TABLE . ' 
			ORDER BY license_id';

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
		}

		while ( $license = $db->sql_fetchrow( $result ) )
		{
			if ( $license_id == $license['license_id'] )
			{
				$list .= '<option value="' . $license['license_id'] . '" selected>' . $license['license_name'] . '</option>';
			}
			else
			{
				$list .= '<option value="' . $license['license_id'] . '">' . $license['license_name'] . '</option>';
			}
		}
		return $list;
	}

	function gen_unique_name( $file_type )
	{
		global $pafiledb_config; 
		// MX
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		srand( ( double )microtime() * 1000000 ); // for older than version 4.2.0 of PHP
		
		do
		{
			$filename = md5( uniqid( rand() ) ) . $file_type;
		}
		while ( file_exists( $pafiledb_config['upload_dir'] . '/' . $filename ) );

		return $filename;
	}

	function get_extension( $filename )
	{
		return strtolower( array_pop( explode( '.', $filename ) ) );
	}

	function upload_file( $userfile, $userfile_name, $userfile_size, $upload_dir = '', $local = false )
	{
		global $phpbb_root_path, $lang, $phpEx, $board_config, $pafiledb_config, $userdata; 
		// MX
		global $pafiledb, $cat_id, $mx_root_path, $module_root_path, $is_block, $phpEx;

		@set_time_limit( 0 );
		$file_info = array();

		$file_info['error'] = false;

		if ( file_exists( $module_root_path . $upload_dir . $userfile_name ) )
		{
			$userfile_name = time() . $userfile_name;
		} 
		// =======================================================
		// if the file size is more than the allowed size another error message
		// =======================================================
		if ( $userfile_size > $pafiledb_config['max_file_size'] && ( $pafiledb->modules[$pafiledb->module_name]->auth[$cat_id]['auth_mod'] || $userdata['user_level'] != ADMIN ) && $userdata['session_logged_in'] )
		{
			$file_info['error'] = true;
			if ( !empty( $file_info['message'] ) )
			{
				$file_info['message'] .= '<br>';
			}
			$file_info['message'] .= $lang['Filetoobig'];
		} 
		// =======================================================
		// Then upload the file, and check the php version
		// =======================================================
		else
		{
			$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

			$upload_mode = ( @$ini_val( 'open_basedir' ) || @$ini_val( 'safe_mode' ) ) ? 'move' : 'copy';
			$upload_mode = ( $local ) ? 'local' : $upload_mode;

			if ( $this->do_upload_file( $upload_mode, $userfile, $module_root_path . $upload_dir . $userfile_name ) )
			{
				$file_info['error'] = true;
				if ( !empty( $file_info['message'] ) )
				{
					$file_info['message'] .= '<br>';
				}
				$file_info['message'] .= 'Couldn\'t Upload the File.';
			}
			$file_info['url'] = get_formated_url() . '/' . $module_root_path . $upload_dir . $userfile_name;
		}
		return $file_info;
	}

	function do_upload_file( $upload_mode, $userfile, $userfile_name )
	{
		switch ( $upload_mode )
		{
			case 'copy':
				if ( !@copy( $userfile, $userfile_name ) )
				{
					if ( !@move_uploaded_file( $userfile, $userfile_name ) )
					{
						return false;
					}
				}
				@chmod( $userfile_name, 0666 );
				break;

			case 'move':
				if ( !@move_uploaded_file( $userfile, $userfile_name ) )
				{
					if ( !@copy( $userfile, $userfile_name ) )
					{
						return false;
					}
				}
				@chmod( $userfile_name, 0666 );
				break;

			case 'local':
				if ( !@copy( $userfile, $userfile_name ) )
				{
					return false;
				}
				@chmod( $userfile_name, 0666 );
				@unlink( $userfile );
				break;
		}

		return;
	}

	function get_file_size( $file_id, $file_data = '' )
	{
		global $db, $lang, $phpbb_root_path, $pafiledb_config; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$directory = $module_root_path . $pafiledb_config['upload_dir'];

		if ( empty( $file_data ) )
		{
			$sql = "SELECT file_dlurl, file_size, unique_name, file_dir
				FROM " . PA_FILES_TABLE . " 
				WHERE file_id = '" . $file_id . "'";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt query Download URL', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $db->sql_fetchrow( $result );

			$db->sql_freeresult( $result );
		}

		$file_url = $file_data['file_dlurl'];
		$file_size = $file_data['file_size'];

		$formated_url = get_formated_url();
		$html_path = $formated_url . '/' . $directory;
		$update_filesize = false;

		if ( ( ( substr( $file_url, 0, strlen( $html_path ) ) == $html_path ) || !empty( $file_data['unique_name'] ) ) && empty( $file_size ) )
		{
			$file_url = basename( $file_url ) ;
			$file_name = basename( $file_url );

			if ( ( !empty( $file_data['unique_name'] ) ) && ( !file_exists( $module_root_path . $file_data['file_dir'] . $file_data['unique_name'] ) ) )
			{
				return $lang['Not_available'];
			}

			if ( empty( $file_data['unique_name'] ) )
			{
				$file_size = @filesize( $directory . $file_name );
			}
			else
			{
				$file_size = @filesize( $module_root_path . $file_data['file_dir'] . $file_data['unique_name'] );
			}

			$update_filesize = true;
		}
		elseif ( empty( $file_size ) && ( ( !( substr( $file_url, 0, strlen( $html_path ) ) == $html_path ) ) || empty( $file_data['unique_name'] ) ) )
		{
			$ourhead = "";
			$url = parse_url( $file_url );
			$host = $url['host'];
			$path = $url['path'];
			$port = ( !empty( $url['port'] ) ) ? $url['port'] : 80;

			$fp = @fsockopen( $host, $port, &$errno, &$errstr, 20 );

			if ( !$fp )
			{
				return $lang['Not_available'];
			}
			else
			{
				fputs( $fp, "HEAD $file_url HTTP/1.1\r\n" );
				fputs( $fp, "HOST: $host\r\n" );
				fputs( $fp, "Connection: close\r\n\r\n" );

				while ( !feof( $fp ) )
				{
					$ourhead = sprintf( '%s%s', $ourhead, fgets ( $fp, 128 ) );
				}
			}
			@fclose ( $fp );

			$split_head = explode( 'Content-Length: ', $ourhead );

			$file_size = round( abs( $split_head[1] ) );
			$update_filesize = true;
		}

		if ( $update_filesize )
		{
			$sql = 'UPDATE ' . PA_FILES_TABLE . "
				SET file_size = '$file_size'
				WHERE file_id = '$file_id'";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not update filesize', '', __LINE__, __FILE__, $sql );
			}
		}

		if ( $file_size < 1024 )
		{
			$file_size_out = intval( $file_size ) . ' ' . $lang['Bytes'];
		}
		if ( $file_size >= 1025 )
		{
			$file_size_out = round( intval( $file_size ) / 1024 * 100 ) / 100 . ' ' . $lang['KB'];
		}
		if ( $file_size >= 1048575 )
		{
			$file_size_out = round( intval( $file_size ) / 1048576 * 100 ) / 100 . ' ' . $lang['MB'];
		}

		return $file_size_out;
	}

	function get_rating( $file_id, $file_rating = '' )
	{
		global $db, $lang;

		$sql = "SELECT AVG(rate_point) AS rating 
			FROM " . PA_VOTES_TABLE . " 
			WHERE votes_file = '" . $file_id . "'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt rating info for the giving file', '', __LINE__, __FILE__, $sql );
		}

		$row = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );
		$file_rating = $row['rating'];

		return ( $file_rating != 0 ) ? round( $file_rating, 2 ) . ' / 10' : $lang['Not_rated'];
	} 

	function pafiledb_unlink( $filename )
	{
		global $pafiledb_config, $lang;

		$deleted = @unlink( $filename );

		if ( @file_exists( $this->pafiledb_realpath( $filename ) ) )
		{
			$filesys = eregi_replace( '/', '\\', $filename );
			$deleted = @system( "del $filesys" );

			if ( @file_exists( $this->pafiledb_realpath( $filename ) ) )
			{
				$deleted = @chmod ( $filename, 0775 );
				$deleted = @unlink( $filename );
				$deleted = @system( "del $filesys" );
			}
		}

		return ( $deleted );
	}

	function pafiledb_realpath( $path )
	{
		global $phpbb_root_path, $phpEx;

		return ( !@function_exists( 'realpath' ) || !@realpath( $phpbb_root_path . 'includes/functions.' . $phpEx ) ) ? $path : @realpath( $path );
	}

	function sql_query_limit( $query, $total, $offset = 0 )
	{
		global $db;

		$query .= ' LIMIT ' . ( ( !empty( $offset ) ) ? $offset . ', ' . $total : $total );
		return $db->sql_query( $query );
	}
}

// =========================================
// This class is used to determin Browser and operating system info of the user

// Copyright (c) 2002 Chip Chapin <cchapin@chipchapin.com>
// http://www.chipchapin.com
// All rights reserved.
// =========================================
class pafiledb_user_info
{
	var $agent = 'unknown';
	var $ver = 0;
	var $majorver = 0;
	var $minorver = 0;
	var $platform = 'unknown';

	/* Constructor
	 Determine client browser type, version and platform using
	 heuristic examination of user agent string.
	 @param $user_agent allows override of user agent string for testing.
	*/

	function user_info( $user_agent = '' )
	{
		global $_SERVER, $HTTP_USER_AGENT, $HTTP_SERVER_VARS;

		if ( !empty( $_SERVER['HTTP_USER_AGENT'] ) )
		{
			$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		}
		else if ( !empty( $HTTP_SERVER_VARS['HTTP_USER_AGENT'] ) )
		{
			$HTTP_USER_AGENT = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
		}
		else if ( !isset( $HTTP_USER_AGENT ) )
		{
			$HTTP_USER_AGENT = '';
		}

		if ( empty( $user_agent ) )
		{
			$user_agent = $HTTP_USER_AGENT;
		}

		$user_agent = strtolower( $user_agent ); 
		// Determine browser and version
		// The order in which we test the agents patterns is important
		// Intentionally ignore Konquerer.  It should show up as Mozilla.
		// post-Netscape Mozilla versions using Gecko show up as Mozilla 5.0
		if ( preg_match( '/(opera |opera\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(msie )([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		elseif ( preg_match( '/(mozilla\/)([0-9]*).([0-9]{1,2})/', $user_agent, $matches ) ) ;
		else
		{
			$matches[1] = 'unknown';
			$matches[2] = 0;
			$matches[3] = 0;
		}

		$this->majorver = $matches[2];
		$this->minorver = $matches[3];
		$this->ver = $matches[2] . '.' . $matches[3];

		switch ( $matches[1] )
		{
			case 'opera/':
			case 'opera ':
				$this->agent = 'OPERA';
				break;

			case 'msie ':
				$this->agent = 'IE';
				break;

			case 'mozilla/':
				$this->agent = 'NETSCAPE';
				if ( $this->majorver >= 5 )
				{
					$this->agent = 'MOZILLA';
				}
				break;

			case 'unknown':
				$this->agent = 'OTHER';
				break;

			default:
				$this->agent = 'Oops!';
		} 
		// Determine platform
		// This is very incomplete for platforms other than Win/Mac
		if ( preg_match( '/(win|mac|linux|unix)/', $user_agent, $matches ) );
		else $matches[1] = 'unknown';

		switch ( $matches[1] )
		{
			case 'win':
				$this->platform = 'Win';
				break;

			case 'mac':
				$this->platform = 'Mac';
				break;

			case 'linux':
				$this->platform = 'Linux';
				break;

			case 'unix':
				$this->platform = 'Unix';
				break;

			case 'unknown':
				$this->platform = 'Other';
				break;

			default:
				$this->platform = 'Oops!';
		}
	}

	function update_downloader_info( $file_id )
	{
		global $user_ip, $db, $userdata;

		$where_sql = ( $userdata['user_id'] != ANONYMOUS ) ? "user_id = '" . $userdata['user_id'] . "'" : "downloader_ip = '" . $user_ip . "'";

		$sql = "SELECT user_id, downloader_ip 
			FROM " . PA_DOWNLOAD_INFO_TABLE . " 
			WHERE $where_sql";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query User id', '', __LINE__, __FILE__, $sql );
		}

		if ( !$db->sql_numrows( $result ) )
		{
			$sql = "INSERT INTO " . PA_DOWNLOAD_INFO_TABLE . " (file_id, user_id, downloader_ip, downloader_os, downloader_browser, browser_version) 
						VALUES('" . $file_id . "', '" . $userdata['user_id'] . "', '" . $user_ip . "', '" . $this->platform . "', '" . $this->agent . "', '" . $this->ver . "')";
			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Update Downloader Table Info', '', __LINE__, __FILE__, $sql );
			}
		}

		$db->sql_freeresult( $result );
	}

	function update_voter_info( $file_id, $rating )
	{
		global $user_ip, $db, $userdata, $lang;

		$where_sql = ( $userdata['user_id'] != ANONYMOUS ) ? "user_id = '" . $userdata['user_id'] . "'" : "votes_ip = '" . $user_ip . "'";

		$sql = "SELECT user_id, votes_ip 
			FROM " . PA_VOTES_TABLE . " 
			WHERE $where_sql
			AND votes_file = '" . $file_id . "'
			LIMIT 1";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query User id', '', __LINE__, __FILE__, $sql );
		}

		if ( !$db->sql_numrows( $result ) )
		{
			$sql = "INSERT INTO " . PA_VOTES_TABLE . " (user_id, votes_ip, votes_file, rate_point, voter_os, voter_browser, browser_version) 
						VALUES('" . $userdata['user_id'] . "', '" . $user_ip . "', '" . $file_id . "','" . $rating . "', '" . $this->platform . "', '" . $this->agent . "', '" . $this->ver . "')";
			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Update Votes Table Info', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
			mx_message_die( GENERAL_MESSAGE, $lang['Rerror'] );
		}

		$db->sql_freeresult( $result );
	}
	
	function truncate_text( $mytext, $length = 200, $add_dots = true )
	{
		$do_trunc = false;
		if ( strlen( $mytext ) > $length )
		{
			$do_trunc = true;
			$mytext = substr( $mytext, 0, $length );
			$mytext = str_replace('<br />', '<br/>', $mytext);
			$mytext = substr( $mytext, 0, strrpos( $mytext, ' ' ) );
			$mytext = str_replace('<br/>', '<br />', $mytext);
			
			if ( $add_dots )
			{
				$mytext .= '...';
			}
		}
		$return_data = array($mytext, $do_trunc);
		return $return_data;
	}	
}

class mx_pa_text_tools
{
	// ===================================================
	// Public method
	// ===================================================	
	function decode( $mytext = '', $do_url = true, $do_images = '300', $do_wordwrap = true )
	{
		global $board_config; 

		if ( $do_url )
		{
			$mytext = $this->_magic_url( $mytext );
		}
		
		if ( $do_images > 0 )
		{
			$mytext = $this->_magic_img( $mytext, $do_images );
		}

		if ( $do_wordwrap )
		{
			$mytext = $this->_word_wrap_pass( $mytext );
		}
					
		return $mytext;
	}
	
	// Replace magic urls of form http://xxx.xxx., www.xxx. and xxx@xxx.xxx.
	// Cuts down displayed size of link if over 50 chars, turns absolute links
	// into relative versions when the server/script path matches the link
	// ===================================================
	// 
	// ===================================================	
	function _magic_url( $url )
	{
		global $board_config; 
		// $url = stripslashes($url);
		if ( $url )
		{
			$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
			$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim( $board_config['server_port'] ) . '/' : '/';
	
			$match = array();
			$replace = array(); 
			// relative urls for this board
			$match[] = '#(^|[\n ])' . $server_protocol . trim( $board_config['server_name'] ) . $server_port . preg_replace( '/^\/?(.*?)(\/)?$/', '$1', trim( $board_config['script_path'] ) ) . '/([^ \t\n\r <"\']+)#i';
			$replace[] = '<a href="$1" target="_blank">$1</a>'; 
			// matches a xxxx://aaaaa.bbb.cccc. ...
			$match[] = '#(^|[\n ])([\w]+?://.*?[^ \t\n\r<"]*)#ie';
			$replace[] = "'\$1<a href=\"\$2\" target=\"_blank\">' . ((strlen('\$2') > 25) ? substr(str_replace('http://','','\$2'), 0, 17) . '...' : '\$2') . '</a>'"; 
			// $replace[] = "'\$1<a href=\"\$2\" target=\"_blank\">' . ((strlen('\$2') > 25) ? substr(str_replace('http://','','\$2'), 0, 12) . ' ... ' . substr('\$2', -3) : '\$2') . '</a>'";
			// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
			$match[] = '#(^|[\n ])(www\.[\w\-]+\.[\w\-.\~]+(?:/[^ \t\n\r<"]*)?)#ie';
			$replace[] = "'\$1<a href=\"http://\$2\" target=\"_blank\">' . ((strlen('\$2') > 25) ? substr(str_replace(' ', '%20', str_replace('http://','', '\$2')), 0, 17) . '...' : '\$2') . '</a>'"; 
			// $replace[] = "'\$1<a href=\"http://\$2\" target=\"_blank\">' . ((strlen('\$2') > 25) ? substr(str_replace(' ', '%20', str_replace('http://','', '\$2')), 0, 12) . ' ... ' . substr('\$2', -3) : '\$2') . '</a>'";
			// matches an email@domain type address at the start of a line, or after a space.
			$match[] = '#(^|[\n ])([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#ie';
			$replace[] = "'\$1<a href=\"mailto:\$2\">' . ((strlen('\$2') > 25) ? substr('\$2', 0, 15) . ' ... ' . substr('\$2', -5) : '\$2') . '</a>'";
	
			$url = preg_replace( $match, $replace, $url ); 
			// Also fix already tagged links
			$url = preg_replace( "/<a href=(.*?)>(.*?)<\/a>/ie", "(strlen(\"\\2\") > 25 && !eregi(\"<\", \"\\2\") ) ? '<a href='.stripslashes(\"\\1\").'>'.substr(str_replace(\"http://\",\"\",\"\\2\"), 0, 17) . '...</a>' : '<a href='.stripslashes(\"\\1\").'>'.\"\\2\".'</a>'", $url );
			// $url = preg_replace("/<a href=(.*?)>(.*?)<\/a>/ie", "(strlen(\"\\2\") > 25 && !eregi(\"<\", \"\\2\") ) ? '<a href='.stripslashes(\"\\1\").'>'.substr(str_replace(\"http://\",\"\",\"\\2\"), 0, 12) . ' ... ' . substr(\"\\2\", -3).'</a>' : '<a href='.stripslashes(\"\\1\").'>'.\"\\2\".'</a>'", $url);
			return $url;
		}
		return $url;
	} 
	
	// Validates the img for block_size and resizes when needed
	// run within a div tag to ensure the table layout is not broken
	// ===================================================
	// 
	// ===================================================	
	function _magic_img( $img, $do_images = '300' )
	{
		global $board_config, $block_size; 
		// $img = stripslashes($img);
		$image_size = $do_images;
		if ( $img )
		{ 
			// Also fix already tagged links
			// $img = preg_replace("/<img src=(.*?)(|border(.*?)|alt(.*?))>/ie", "'<br /><br /><center><img src='.stripslashes(\"\\1\").' width=\"'.makeImgWidth(trim(stripslashes(\"\\1\"))).'\" ></center><br />'", $img);
			$img = preg_replace( "/<img src=(.*?)>/ie", "(substr_count(\"\\1\", \"smiles\") > 0 ) ? '<img src='.stripslashes(\"\\1\").'>' : 
			
			'<div style=\" overflow: hidden; margin: 0px; padding: 0px; float: left; \">
			<img class=\"noenlarge\" src='.stripslashes(\"\\1\").' border=\"0\"  OnLoad=\"if(this.width > $image_size) { this.width = $image_size }\" onclick = \"full_img( this.src )\" alt=\" Click to enlarge \">
			</div>'", $img );
			return $img;
		}
		return $img;
	}
	
	// Force Word Wrapping (by TerraFrost)
	// ===================================================
	// 
	// ===================================================	
	function _word_wrap_pass( $message )
	{
		$tempText = "";
		$finalText = "";
		$curCount = $tempCount = 0;
		$longestAmp = 9;
		$inTag = false;
		$ampText = "";
	
		for ( $num = 0;$num < strlen( $message );$num++ )
		{
			$curChar = $message{$num};
	
			if ( $curChar == "<" )
			{
				for ( $snum = 0;$snum < strlen( $ampText );$snum++ )
				$this->_addWrap( $ampText{$snum}, $ampText{$snum+1}, $finalText, $tempText, $curCount, $tempCount );
				$ampText = "";
				$tempText .= "<";
				$inTag = true;
			}elseif ( $inTag && $curChar == ">" )
			{
				$tempText .= ">";
				$inTag = false;
			}elseif ( $inTag )
				$tempText .= $curChar;
			elseif ( $curChar == "&" )
			{
				for ( $snum = 0;$snum < strlen( $ampText );$snum++ )
				$this->_addWrap( $ampText{$snum}, $ampText{$snum+1}, $finalText, $tempText, $curCount, $tempCount );
				$ampText = "&";
			}elseif ( strlen( $ampText ) < $longestAmp && $curChar == ";" &&
					( strlen( html_entity_decode( "$ampText;" ) ) == 1 || preg_match( '/^&#[0-9][0-9]*$/', $ampText ) ) )
			{
				$this->_addWrap( "$ampText;", $message{$num+1}, $finalText, $tempText, $curCount, $tempCount );
				$ampText = "";
			}elseif ( strlen( $ampText ) >= $longestAmp || $curChar == ";" )
			{
				for ( $snum = 0;$snum < strlen( $ampText );$snum++ )
				$this->_addWrap( $ampText{$snum}, $ampText{$snum+1}, $finalText, $tempText, $curCount, $tempCount );
				$this->_addWrap( $curChar, $message{$num+1}, $finalText, $tempText, $curCount, $tempCount );
				$ampText = "";
			}elseif ( strlen( $ampText ) != 0 && strlen( $ampText ) < $longestAmp )
				$ampText .= $curChar;
			else
				$this->_addWrap( $curChar, $message{$num+1}, $finalText, $tempText, $curCount, $tempCount );
		}
	
		return $finalText . $tempText;
	}

	// ===================================================
	// 
	// ===================================================	
	function _addWrap( $curChar, $nextChar, &$finalText, &$tempText, &$curCount, &$tempCount )
	{
		$softHyph = "&shy;"; 
		// $softHyph = "&emsp;";
		$maxChars = 10;
		$wrapProhibitedChars = "([{!;,:?}])";
	
		if ( $curChar == " " || $curChar == "\n" )
		{
			$finalText .= $tempText . $curChar;
			$tempText = "";
			$curCount = 0;
			$curChar = "";
		}elseif ( $curCount >= $maxChars )
		{
			$finalText .= $tempText . $softHyph;
			$tempText = "";
			$curCount = 1;
		}
		else
		{
			$tempText .= $curChar;
			$curCount++;
		} 
		// the following code takes care of (unicode) characters prohibiting non-mandatory breaks directly before them.
		// $curChar isn't a " " or "\n"
		if ( $tempText != "" && $curChar != "" )
			$tempCount++; 
		// $curChar is " " or "\n", but $nextChar prohibits wrapping.
		elseif ( ( $curCount == 1 && strstr( $wrapProhibitedChars, $curChar ) !== false ) ||
				( $curCount == 0 && $nextChar != "" && $nextChar != " " && $nextChar != "\n" && strstr( $wrapProhibitedChars, $nextChar ) !== false ) )
			$tempCount++; 
		// $curChar and $nextChar aren't both either " " or "\n"
		elseif ( !( $curCount == 0 && ( $nextChar == " " || $nextChar == "\n" ) ) )
			$tempCount = 0;
	
		if ( $tempCount >= $maxChars && $tempText == "" )
		{
			$finalText .= "&nbsp;";
			$tempCount = 1;
			$curCount = 2;
		}
	
		if ( $tempText == "" && $curCount > 0 )
			$finalText .= $curChar;
	}	

	// ===================================================
	// 
	// ===================================================	
	function remove_images_links( $comments_text, $allow_images = false, $no_image_message = '[No image please]', $allow_links = false, $no_link_message = '[No links please]')
	{
		if ( $comments_text != '' )
		{
			if ( !$allow_images )
			{
				if ( preg_match( '/(<img src=)(.+?)(\>)/i', $comments_text ) )
				{
					$comments_text = preg_replace( '/(<img src=)(.+?)(\>)/i', $no_image_message, $comments_text );
				}
	
				if ( preg_match( '/(\[img\])([^\[]*)(\[\/img\])/i', $comments_text ) )
				{
					$comments_text = preg_replace( '/(\[img\])([^\[]*)(\[\/img\])/i', $no_image_message, $comments_text );
				}
			}
	
			if ( !$allow_links )
			{
				if ( preg_match( '/(\[url=(.*?)\])([^\[]*)(\[\/url\])/i', $comments_text ) )
				{
					$comments_text = preg_replace( '/(\[url=(.*?)\])([^\[]*)(\[\/url\])/i', $no_link_message, $comments_text );
				}
	
				if ( preg_match( '/(\[url\])([^\[]*)(\[\/url\])/i', $comments_text ) )
				{
					$comments_text = preg_replace( '/(\[url\])([^\[]*)(\[\/url\])/i', $no_link_message, $comments_text );
				}
	
				if ( preg_match( "#([\n ])http://www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^,\t \n\r]*)?)#i", $comments_text ) )
				{
					$comments_text = preg_replace( "#([\n ])http://www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^,\t \n\r]*)?)#i", $no_link_message, $comments_text );
				}
	
				if ( preg_match( "#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^,\t \n\r]*)?)#i", $comments_text ) )
				{
					$comments_text = preg_replace( "#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^,\t \n\r]*)?)#i", $no_link_message, $comments_text );
				}
			}
		}
		return $comments_text;
	}		
	
	function truncate_text( $mytext, $length = 200, $add_dots = true )
	{
		$do_trunc = false;
		if ( strlen( $mytext ) > $length )
		{
			$do_trunc = true;
			$mytext = substr( $mytext, 0, $length );
			$mytext = str_replace('<br />', '<br/>', $mytext);
			$mytext = substr( $mytext, 0, strrpos( $mytext, ' ' ) );
			$mytext = str_replace('<br/>', '<br />', $mytext);
			
			if ( $add_dots )
			{
				$mytext .= '...';
			}
		}
		$return_data = array($mytext, $do_trunc);
		return $return_data;
	}	
}

define('MX_MAIL_MODE'						, 1);
define('MX_PM_MODE'							, 2);
define('MX_POST_MODE'						, 2);

define('MX_NEW_NOTIFICATION'				, 10);
define('MX_EDITED_NOTIFICATION'				, 11);
define('MX_APPROVED_NOTIFICATION'			, 12);
define('MX_UNAPPROVED_NOTIFICATION'			, 13);

/********************************************************************************\
| mx_pa_notification class
| ------------------------
| This class will handle most PM/MAIL tasks.
|
| Usage:
| $mx_pa_notification = new mx_pa_notification()
| 
| // MODE: MX_PM_MODE/MX_MAIL_MODE, $id: get all file/article data for this id
| $mx_pa_notification->init($mode, $id); // MODE: MX_PM_MODE/MX_MAIL_MODE
|
| // MODE: MX_PM_MODE/MX_MAIL_MODE, ACTION: MX_NEW_NOTIFICATION/MX_EDITED_NOTIFICATION/MX_APPROVED_NOTIFICATION/MX_UNAPPROVED_NOTIFICATION
| notify( $mode = MX_PM_MODE, $action = MX_NEW_NOTIFICATION, $to_id, $from_id, $subject, $message, $html_on, $bbcode_on, $smilies_on )
|
\********************************************************************************/

class mx_pa_notification
{
	var $data = array(); // all item data in one array
	var $langs = array(); // generic lang keys
	var $url_rewrite = 'pa_this_mxurl';
	
	//
	// PM/EMAIL Notification
	//
	var $subject = '';
	var $message = '';
	var $auto_message = ''; // for auto generated messages
	var $auto_message_update = ''; // for auto generated messages
	
	var $to_id = '';
	var $from_id = '';
	var $html_on = 0;
	var $bbcode_on = 1;
	var $smilies_on = 1;
	
	//
	// Autogenerated comments
	//
	var $first_commnent = ''; // only used for phpBB comments
	var $next_commnent = '';
	
	function init( $mode = MX_PM_MODE, $item_id = 0 )
	{
		global $db, $lang, $pafiledb_custom_field;
			// =======================================================
			// item id is not set, give him/her a nice error message
			// =======================================================
			if (empty($item_id))
			{
				mx_message_die(GENERAL_ERROR, 'Bad Init pars');	
			}

			if (!is_object($pafiledb_custom_field))
			{
				include( $module_root_path . 'pafiledb/includes/functions_field.' . $phpEx );
				$this->custom_field = new pafiledb_custom_field();
				$this->custom_field->init();
			}
						
			unset($this->langs);
			
			//
			// Build up generic lang keys
			//
			$this->langs['item_not_exist'] = $lang['File_not_exist'];
			$this->langs['module_title'] = $lang['KB_title'];
			$this->langs['notify_subject_new'] = $lang['KB_notify_subject_new'];
			$this->langs['notify_subject_edited'] = $lang['KB_notify_subject_edited'];
			$this->langs['notify_subject_approved'] = $lang['KB_notify_subject_approved'];
			$this->langs['notify_subject_unapproved'] = $lang['KB_notify_subject_unapproved'];
			$this->langs['notify_body'] = $lang['KB_notify_body'];
			$this->langs['item_title'] = $lang['Article_title'];
			$this->langs['author'] = $lang['Author'];
			$this->langs['item_description'] = $lang['Article_description'];
			$this->langs['item_type'] = $lang['Article_type'];
			$this->langs['category'] = $lang['Category'];
			$this->langs['read_full_item'] = $lang['Read_full_article'];
			$this->langs['edited_item_info'] = $lang['Edited_Article_info'];

			switch ( SQL_LAYER )
			{
				case 'oracle':
					$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments
						FROM " . PA_FILES_TABLE . " AS f, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_CATEGORIES_TABLE . " AS c, " . PA_COMMENTS_TABLE . " AS cm
						WHERE f.file_id = r.votes_file(+)
						AND f.user_id = u.user_id(+)
						AND f.file_id = cm.file_id(+)
						AND c.cat_id = a.file_catid
						AND f.file_id = '" . $item_id . "'
						GROUP BY f.file_id ";
					break;
	
				default:
					$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments
						FROM " . PA_FILES_TABLE . " AS f, " . PA_CATEGORIES_TABLE . " AS c
							LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f.file_id = r.votes_file 
							LEFT JOIN " . USERS_TABLE . " AS u ON f.user_id = u.user_id
							LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f.file_id = c.file_id
						WHERE c.cat_id = a.file_catid
							AND f.file_id = '" . $item_id . "'
						GROUP BY f.file_id ";
					break;
									
				/*
				case 'oracle':
					$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments
						FROM " . PA_FILES_TABLE . " AS f, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c
						WHERE f.file_id = r.votes_file(+)
						AND f.user_id = u.user_id(+)
						AND f.file_id = c.file_id(+)
						AND f.file_id = '" . $item_id . "'
						GROUP BY f.file_id ";
					break;
	
				default:
					$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments
						FROM " . PA_FILES_TABLE . " AS f
							LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f.file_id = r.votes_file 
							LEFT JOIN " . USERS_TABLE . " AS u ON f.user_id = u.user_id
							LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f.file_id = c.file_id
						WHERE f.file_id = '" . $item_id . "'
						GROUP BY f.file_id ";
					break;
				*/
			}
	
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
			} 
			
			// ===================================================
			// file doesn't exist'
			// ===================================================
			if ( !$item_data = $db->sql_fetchrow( $result ) )
			{
				mx_message_die( GENERAL_MESSAGE, $this->langs['Item_not_exist'] );
			}
			
			$db->sql_freeresult( $result ); 
	
			unset($this->data);
			
			//
			// File data
			//
			$this->data['item_id'] = $item_id;
			$this->data['item_title'] = $item_data['file_name'];
			$this->data['item_desc'] = $item_data['file_desc'];
			
			
			//
			// Category data
			//
			$this->data['item_category_id'] = $item_data['cat_id'];
			$this->data['item_category_name'] = $item_data['cat_name'];
			
			//
			// File author
			//
			$this->data['item_author_id'] = $item_data['user_id'];
			$this->data['item_author'] = ( $item_data['user_id'] != ANONYMOUS ) ? $item_data['username'] : $lang['Guest'];
			
			//
			// File editor
			//
			$this->data['item_editor_id'] = $userdata['user_id'];
			$this->data['item_editor'] = ( $userdata['user_id'] != '-1' ) ? $userdata['username'] : $lang['Guest'];
	}
		
	/*
	function kb_get_data($row = '', $userdata = '', $kb_post_mode = 'add')
	{
		global $db, $lang, $username, $kb_config;
	
			// Debug checks
			if ( empty( $row ) || empty( $userdata ) )
			{
				die('kb_get_data - empty pars' );
			}
			
			$kb_author_data = $this->get_kb_author( $row['article_author_id'], true );
	
			$sql = "SELECT * FROM " . KB_CATEGORIES_TABLE . " WHERE category_id = '" . $row['article_category_id'] . "'";
			if ( !( $results = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Could not get comments_forum_id", '', __LINE__, __FILE__, $sql );
			}
			$cat_row = $db->sql_fetchrow( $results );
			
			// Article data
			$kb_comment['article_id'] = $row['article_id'];
			$kb_comment['article_title'] = $row['article_title'];
			$kb_comment['article_desc'] = $row['article_description'];
			
			$kb_comment['article_category_id'] = $row['article_category_id'];
			$kb_comment['category_name'] = $cat_row['category_name'];
			$kb_comment['category_forum_id'] = $cat_row['comments_forum_id'];
			$kb_comment['topic_id'] = $kb_post_mode == 'edit' ? $row['topic_id'] : '';
			
			$kb_comment['article_type_id'] = $row['article_type'];
			$kb_comment['article_type'] = $this->get_kb_type( $kb_comment['article_type_id'] );
			
			// Article author
			$kb_comment['article_author_id'] = $row['article_author_id'];
			$kb_comment['article_author'] = $row['article_author_id'] != -1 ? $kb_author_data['username'] : ( ( $row['username'] == '' ) ? $lang['Guest'] : $row['username'] )  ;
			$kb_comment['article_author_sig'] = $kb_author_data['user_attachsig'];
			
			// Article editor
			$kb_comment['article_editor_id'] = $userdata['user_id'];
			$kb_comment['article_editor'] = ( $userdata['user_id'] != '-1' ) ? $userdata['username'] : ( ( $username == '' ) ? $lang['Guest'] : stripslashes($username) );
			$kb_comment['article_editor_sig'] = ( $userdata['user_id'] != '-1' ) ? $userdata['user_attachsig'] : '0';
			
			// Debug checks
			if ( $kb_post_mode == 'edit' && $kb_config['use_comments'] && empty($kb_comment['topic_id']))
			{
				die('kb_get_data - no forum topic id for comment');
			}
					
			return $kb_comment;
	}
	*/
		
	// ===================================================
	// Notification - email/PM
	// Note: This method may be used by itself (if subject and message is passed)
	// ===================================================
	function notify( $mode = MX_PM_MODE, $action = MX_NEW_NOTIFICATION, $to_id, $from_id = '', $subject = '', $message = '', $html_on = 0, $bbcode_on = 1, $smilies_on = 1 )
	{
		global $lang, $board_config, $pafiledb_config, $db, $module_root_path, $phpbb_root_path, $mx_root_path, $phpEx, $userdata;
	
		//
		// Precheck
		//
		if (intval($to_id) > 0)
		{
			$this->to_id = intval($to_id);
		}
		else 
		{
		 	mx_message_die(GENERAL_ERROR, 'bad notify to id');	
		}
		
	    $this->from_id = empty( $from_id ) ? $userdata['user_id'] : $from_id;

	   	//
	   	// Why send PM/MAIL to yourself???
	   	//
	   	if ( $this->to_id == $this->from_id )
	   	{
	   	 	return;
	   	}
	   	    
		//
		// Toggles
		//
		$this->html_on = $html_on; 
		$this->bbcode_on = $bbcode_on; 
		$this->smilies_on = $smilies_on;
		
		//
		// Compose Subject
		//
		if (empty($subject))
		{
			//
			// Auto generated subject
			//			
			switch ( $action )
			{
				case MX_NEW_NOTIFICATION:
					$this->subject = $this->langs['module_title'] . ' - ' . $this->langs['notify_subject_new'];
				break;
				
				case MX_EDITED_NOTIFICATION:
					$this->subject = $this->langs['module_title'] . ' - ' . $this->langs['notify_subject_edited'];
				break;
				
				case MX_APPROVED_NOTIFICATION:
					$this->subject = $this->langs['module_title'] . ' - ' . $this->langs['notify_subject_approved'];
				break;
				
				case MX_UNAPPROVED_NOTIFICATION:
					$this->subject = $this->langs['module_title'] . ' - ' . $this->langs['notify_subject_unapproved'];
				break;
				
				default:
					mx_message_die(GENERAL_ERROR, 'Bad notify action');	
			}
		}
		else 
		{
			//
			// Custom subject
			//
			$this->subject = $subject;
		}
		
		//
		// Compose Message
		//
		if (empty($message))
		{		
			//
			// Auto generated message
			//
			$this->message = $this->langs['notify_body'] . '\n\n\n' . $this->auto_message;
		}
		else 
		{
			//
			// Custom message
			//
			$this->message = $message;
		}	
		
		//
		// Now send PM/MAIL
		//
		switch ( $mode )
		{
			case MX_MAIL_MODE:
				$this->_mailer();			
			break;
				
			case MX_PM_MODE:
				$this->_insert_pm();			
			break;
	
			default:		
				mx_message_die(GENERAL_ERROR, 'Bad notify type');	
		}			
				
	}
	
	// ===================================================
	// Notification - PM
	// based on wgErics good old insert_pm function
	// ===================================================
	function _insert_pm()
	{
	   global $db, $lang, $user_ip, $board_config, $userdata, $phpbb_root_path, $phpEx;
	
	   //
	   // get varibles ready
	   //
	   $msg_time = time();
	   $attach_sig = $userdata['user_attachsig'];
	   
	   //
	   //get 'to user's info
	   //
	   $sql = "SELECT user_id, user_notify_pm, user_email, user_lang, user_active
	      FROM " . USERS_TABLE . "
	      WHERE user_id = '" . $this->to_id . "'
	         AND user_id <> " . ANONYMOUS;
	   
	   if ( !($result = $db->sql_query($sql)) )
	   {
	      $error = TRUE;
	      $error_msg = $lang['No_such_user'];
	   }
	
	   $to_userdata = $db->sql_fetchrow($result);
	
	   $privmsg_subject = trim(strip_tags($this->subject));
	   
	   if ( empty($privmsg_subject) )
	   {
	      $error = TRUE;
	      $error_msg .= ( ( !empty($error_msg) ) ? '<br />' : '' ) . $lang['Empty_subject'];
	   }
	
	   if ( !empty($this->message) )
	   {
	      if ( !$error )
	      {
	         if ( $this->bbcode_on )
	         {
	            $bbcode_uid = make_bbcode_uid();
	         }
	
	         $privmsg_message = prepare_message($this->message, $this->html_on, $this->bbcode_on, $this->smilies_on, $bbcode_uid);
	         $privmsg_message = str_replace('\\\n', '\n', $privmsg_message);
	      }
	   }
	   else
	   {
	      $error = TRUE;
	      $error_msg .= ( ( !empty($error_msg) ) ? '<br />' : '' ) . $lang['Empty_message'];
	   }
	
	   //
	   // See if recipient is at their inbox limit
	   //
	   $sql = "SELECT COUNT(privmsgs_id) AS inbox_items, MIN(privmsgs_date) AS oldest_post_time
	      FROM " . PRIVMSGS_TABLE . "
	      WHERE ( privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
	            OR privmsgs_type = " . PRIVMSGS_READ_MAIL . " 
	            OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )
	         AND privmsgs_to_userid = " . $to_userdata['user_id'];
	   
	   if ( !($result = $db->sql_query($sql)) )
	   {
	      mx_message_die(GENERAL_MESSAGE, $lang['No_such_user']);
	   }
	
	   $sql_priority = ( SQL_LAYER == 'mysql' ) ? 'LOW_PRIORITY' : '';
	
	   if ( $inbox_info = $db->sql_fetchrow($result) )
	   {
	      if ( $inbox_info['inbox_items'] >= $board_config['max_inbox_privmsgs'] )
	      {
	         $sql = "SELECT privmsgs_id FROM " . PRIVMSGS_TABLE . "
	            WHERE ( privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
	                  OR privmsgs_type = " . PRIVMSGS_READ_MAIL . "
	                  OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . "  )
	               AND privmsgs_date = " . $inbox_info['oldest_post_time'] . "
	               AND privmsgs_to_userid = " . $to_userdata['user_id'];
	         
	         if ( !$result = $db->sql_query($sql) )
	         {
	            mx_message_die(GENERAL_ERROR, 'Could not find oldest privmsgs (inbox)', '', __LINE__, __FILE__, $sql);
	         }
	         
	         $old_privmsgs_id = $db->sql_fetchrow($result);
	         $old_privmsgs_id = $old_privmsgs_id['privmsgs_id'];
	            
	         $sql = "DELETE $sql_priority FROM " . PRIVMSGS_TABLE . "
	            WHERE privmsgs_id = $old_privmsgs_id";
	         
	         if ( !$db->sql_query($sql) )
	         {
	            mx_message_die(GENERAL_ERROR, 'Could not delete oldest privmsgs (inbox)'.$sql, '', __LINE__, __FILE__, $sql);
	         }
	
	         $sql = "DELETE $sql_priority FROM " . PRIVMSGS_TEXT_TABLE . "
	            WHERE privmsgs_text_id = $old_privmsgs_id";
	         
	         if ( !$db->sql_query($sql) )
	         {
	            mx_message_die(GENERAL_ERROR, 'Could not delete oldest privmsgs text (inbox)', '', __LINE__, __FILE__, $sql);
	         }
	      }
	   }
	
	   $sql_info = "INSERT INTO " . PRIVMSGS_TABLE . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip, privmsgs_enable_html, privmsgs_enable_bbcode, privmsgs_enable_smilies, privmsgs_attach_sig)
	      VALUES (" . PRIVMSGS_NEW_MAIL . ", '" . str_replace("\'", "''", $privmsg_subject) . "', " . $this->from_id . ", " . $to_userdata['user_id'] . ", $msg_time, '$user_ip', $this->html_on, $this->bbcode_on, $this->smilies_on, $attach_sig)";
	
	   if ( !($result = $db->sql_query($sql_info, BEGIN_TRANSACTION)) )
	   {
	      mx_message_die(GENERAL_ERROR, "Could not insert/update private message sent info.", "", __LINE__, __FILE__, $sql_info);
	   }
	
	   $privmsg_sent_id = $db->sql_nextid();
	
	   $sql = "INSERT INTO " . PRIVMSGS_TEXT_TABLE . " (privmsgs_text_id, privmsgs_bbcode_uid, privmsgs_text)
	      VALUES ($privmsg_sent_id, '" . $bbcode_uid . "', '" . str_replace("\'", "''", $privmsg_message) . "')";
	
	   if ( !$db->sql_query($sql, END_TRANSACTION) )
	   {
	      mx_message_die(GENERAL_ERROR, "Could not insert/update private message sent text.", "", __LINE__, __FILE__, $sql);
	   }
	
	   //
	   // Add to the users new pm counter
	   //
	   $sql = "UPDATE " . USERS_TABLE . "
	      SET user_new_privmsg = user_new_privmsg + 1, user_last_privmsg = " . time() . " 
	      WHERE user_id = " . $to_userdata['user_id'];
	   
	   if ( !$status = $db->sql_query($sql) )
	   {
	      mx_message_die(GENERAL_ERROR, 'Could not update private message new/read status for user', '', __LINE__, __FILE__, $sql);
	   }
	
	   if ( $to_userdata['user_notify_pm'] && !empty($to_userdata['user_email']) && $to_userdata['user_active'] )
	   {
	      $script_name = preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['script_path']));
	      $script_name = ( $script_name != '' ) ? $script_name . '/privmsg.'.$phpEx : 'privmsg.'.$phpEx;
	      $server_name = trim($board_config['server_name']);
	      $server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
	      $server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim($board_config['server_port']) . '/' : '/';
	
	      //
	      // Include and initiate emailer
	      //
	      include($phpbb_root_path . 'includes/emailer.'.$phpEx);
	      $emailer = new emailer($board_config['smtp_delivery']);
	               
	      $emailer->from($board_config['board_email']);
	      $emailer->replyto($board_config['board_email']);
	
	      $emailer->use_template('privmsg_notify', $to_userdata['user_lang']);
	      $emailer->email_address($to_userdata['user_email']);
	      $emailer->set_subject($lang['Notification_subject']);
	               
	      $emailer->assign_vars(array(
	         'USERNAME' => $to_username,
	         'SITENAME' => $board_config['sitename'],
	         'EMAIL_SIG' => (!empty($board_config['board_email_sig'])) ? str_replace('<br />', "\n", "-- \n" . $board_config['board_email_sig']) : '',
	
	         'U_INBOX' => $server_protocol . $server_name . $server_port . $script_name . '?folder=inbox')
	      );
	
	      $emailer->send();
	      $emailer->reset();
	   }
	
	   return;
	   
	   $msg = $lang['Message_sent'] . '<br /><br />' . sprintf($lang['Click_return_inbox'], '<a href="' . append_sid("privmsg.$phpEx?folder=inbox") . '">', '</a> ') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . append_sid("index.$phpEx") . '">', '</a>');
	
	   mx_message_die(GENERAL_MESSAGE, $msg);
	
	}

	// ===================================================
	// Notification - email
	// ===================================================	
	function _mailer()
	{
	   global $db, $lang, $user_ip, $board_config, $userdata, $phpbb_root_path, $phpEx;
	
	   //
	   //get varibles ready
	   //
	   $msg_time = time();
	   $attach_sig = $userdata['user_attachsig'];
	   
	   //
	   //get to users info
	   //
	   $sql = "SELECT user_id, user_notify_pm, user_email, user_lang, user_active
	      FROM " . USERS_TABLE . "
	      WHERE user_id = '".$this->to_id."'
	         AND user_id <> " . ANONYMOUS;
	   
	   if ( !($result = $db->sql_query($sql)) )
	   {
	      $error = TRUE;
	      $error_msg = $lang['No_such_user'];
	   }
	
	   $to_userdata = $db->sql_fetchrow($result);
	
	   $mail_subject = trim(strip_tags($this->subject));
	   
	   if ( empty($mail_subject) )
	   {
	      $error = TRUE;
	      $error_msg .= ( ( !empty($error_msg) ) ? '<br />' : '' ) . $lang['Empty_subject'];
	   }
	
	   if ( !empty($this->message) )
	   {
	      if ( !$error )
	      {
	         if ( $this->bbcode_on )
	         {
	            $bbcode_uid = make_bbcode_uid();
	         }
	
	         $mail_message = prepare_message($this->message, $this->html_on, $this->bbcode_on, $this->smilies_on, $bbcode_uid);
	         $mail_message = str_replace('\\\n', '\n', $mail_message);
	
	      }
	   }
	   else
	   {
	      $error = TRUE;
	      $error_msg .= ( ( !empty($error_msg) ) ? '<br />' : '' ) . $lang['Empty_message'];
	   }
	   
		$script_name = preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['script_path']));
	    $script_name = ( $script_name != '' ) ? $script_name . '/privmsg.'.$phpEx : 'privmsg.'.$phpEx;
	    $server_name = trim($board_config['server_name']);
	    $server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
	    $server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim($board_config['server_port']) . '/' : '/';
	
	    //
	    // Include and initiate mailer
	    //
	    include($phpbb_root_path . 'includes/emailer.'.$phpEx);
	    $emailer = new emailer($board_config['smtp_delivery']);
	               
	    //
	    // Mail
	    //
	    $emailer->from( $board_config['board_email'] );
	    $emailer->replyto( $board_config['board_email'] );
	
	    $emailer->email_address($to_userdata['user_email'] );
	    $emailer->set_subject( $mail_subject );
		$emailer->msg = $mail_message;
	
	    $emailer->send();
	    $emailer->reset();
	}
	
	// Compose phpbb comment header
	function _compose_auto_message()
	{
		global $lang, $phpEx;
		
			$search = array ( "'&(quot|#34);'i", // Replace HTML entities
				"'&(amp|#38);'i",
				"'&(lt|#60);'i",
				"'&(gt|#62);'i" 
				);
				
			$replace = array ( "\"",
				"&",
				"<",
				">" 
				);

			//
			// Compose phpBB post header
			//
			$temp_url = PORTAL_URL . $this->url_rewrite("mode=" . "article&k=" . $this->data['item_id']);
	
			$this->auto_message = "[b]" . $this->langs['item_title'] . ":[/b] " . preg_replace( $search, $replace, $this->data['item_title'] ) . "\n";
			$this->auto_message .= "[b]" . $this->langs['author'] . ":[/b] " . $this->data['item_author'] . "\n";
			$this->auto_message .= "[b]" . $this->langs['item_description'] . ":[/b] [i]" . preg_replace( $search, $replace, $this->data['item_desc'] ) . "[/i]\n\n";
					
			//$this->auto_message .= "[b]" . $this->langs['category'] . ":[/b] " . $this->data['item_category_name'] . "\n";
			//$this->auto_message .= "[b]" . $this->langs['item_type'] . ":[/b] " . $this->data['item_type'] . "\n";
			
			$this->auto_message .= $this->custom_field->add_comment( $this->data['item_id'] );
			
			$this->auto_message .= "\n\n[b][url=" . $temp_url . "]" . $this->langs['read_full_item'] . "[/url][/b]";

			//
			// Update message
			//
			$this->auto_message_update = "[i]" . $this->langs['edited_item_info'] . $this->data['item_editor'] . "[/i]" . "\n\n";
	}
	

	
	function _get_admins( $get_all_userdata = false )
	{
		global $db;
	
		$admin_type = ADMIN;
		
		$sql = "SELECT *  
	       		FROM " . USERS_TABLE . " 
	      		WHERE user_level = '$admin_type'";
	
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, "Could not obtain author data", '', __LINE__, __FILE__, $sql );
		}
	
		if ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $get_all_userdata )
			{
				$name = $row;
			}
			else 
			{ 
				$name = $row['username'];
			}
		}
		else
		{
			$name = '';
		}
	
		return $name;
	}
	
}

//
// Functions
//
function get_formated_url()
{
	global $board_config; 
	global $mx_script_name;

	$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
	$server_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $board_config['server_name'] ) );
	$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim( $board_config['server_port'] ) : '';
	$script_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $mx_script_name ) );
	$script_name = ( $script_name == '' ) ? $script_name : '/' . $script_name;
	$formated_url = $server_protocol . $server_name . $server_port . $script_name;

	return $formated_url;
}

function pafiledb_page_header( $page_title )
{
	global $pafiledb_config, $lang, $pafiledb_template, $userdata, $images, $action, $_REQUEST, $pafiledb;
	global $template, $db, $theme, $gen_simple_header, $starttime, $phpEx, $board_config, $user_ip, $phpbb_root_path;
	global $admin_level, $level_prior, $tree, $do_gzip_compress; 
	global $mx_root_path, $module_root_path, $is_block, $phpEx, $title;

	if ( $action != 'download' )
	{
		//include_once( $mx_root_path . 'includes/page_header.' . $phpEx );
	}

	if ( $action == 'category' )
	{
		$upload_url = append_sid( pa_this_mxurl( "action=user_upload&cat_id={$_REQUEST['cat_id']}" ) );
		$mcp_url = append_sid( pa_this_mxurl( "action=mcp&cat_id={$_REQUEST['cat_id']}" ) );

		$upload_auth = $pafiledb->modules[$pafiledb->module_name]->auth[$_REQUEST['cat_id']]['auth_upload'];
		$mcp_auth = $pafiledb->modules[$pafiledb->module_name]->auth[$_REQUEST['cat_id']]['auth_mod'];
	}
	else
	{
		$upload_url = append_sid( pa_this_mxurl( "action=user_upload" ) );

		$cat_list = $pafiledb->modules[$pafiledb->module_name]->generate_jumpbox( 0, 0, '', true, true );
		// $upload_auth = (empty($cat_list)) ? FALSE : TRUE;
		$upload_auth = false;
		$mcp_auth = false;
		unset( $cat_list );
	}

	$pafiledb_template->assign_vars( array( 'L_TITLE' => $title,
			'IS_AUTH_VIEWALL' => ( $pafiledb_config['settings_viewall'] ) ? ( ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_viewall'] ) ? true : false ) : false,
			'IS_AUTH_SEARCH' => ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_search'] ) ? true : false,
			'IS_AUTH_STATS' => ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_stats'] ) ? true : false,
			'IS_AUTH_TOPLIST' => ( $pafiledb->modules[$pafiledb->module_name]->auth_global['auth_toplist'] ) ? true : false,

			'IS_AUTH_UPLOAD' => $upload_auth,
			'IS_ADMIN' => ( $userdata['user_level'] == ADMIN && $userdata['session_logged_in'] ) ? true : 0, 
			'IS_MOD' => $pafiledb->modules[$pafiledb->module_name]->auth[$_REQUEST['cat_id']]['auth_mod'],
			'IS_AUTH_MCP' => $mcp_auth,
			'MCP_LINK' => $lang['pa_MCP'],
			'U_MCP' => $mcp_url,

			'L_OPTIONS' => $lang['Options'],
			'L_SEARCH' => $lang['Search'],
			'L_STATS' => $lang['Statistics'],
			'L_TOPLIST' => $lang['Toplist'],
			'L_UPLOAD' => $lang['User_upload'],
			'L_VIEW_ALL' => $lang['Viewall'],

			'SEARCH_IMG' => $images['pa_search'],
			'STATS_IMG' => $images['pa_stats'],
			'TOPLIST_IMG' => $images['pa_toplist'],
			'UPLOAD_IMG' => $images['pa_upload'],
			'VIEW_ALL_IMG' => $images['pa_viewall'],

			'U_TOPLIST' => append_sid( pa_this_mxurl( "action=toplist" ) ),
			'U_PASEARCH' => append_sid( pa_this_mxurl( "action=search" ) ),
			'U_UPLOAD' => $upload_url,
			'U_VIEW_ALL' => append_sid( pa_this_mxurl( "action=viewall" ) ),
			'U_PASTATS' => append_sid( pa_this_mxurl( "action=stats" ) ) 
			) 
		);
}
// ===================================================
// page footer for pafiledb
// ===================================================
function pafiledb_page_footer()
{
	global $pafiledb_cache, $lang, $pafiledb_template, $board_config, $_GET, $pafiledb, $userdata, $phpbb_root_path;
	global $phpEx, $template, $do_gzip_compress, $debug, $db, $starttime; 
	global $mx_root_path, $module_root_path, $is_block, $phpEx, $page_id;
	global $pa_module_version, $pa_module_orig_author, $pa_module_author;

	$pafiledb_template->assign_vars( array( 
		'L_QUICK_GO' => $lang['Quick_go'],
		'L_QUICK_NAV' => $lang['Quick_nav'],
		'L_QUICK_JUMP' => $lang['Quick_jump'],
		'JUMPMENU' => $pafiledb->modules[$pafiledb->module_name]->generate_jumpbox( 0, 0, array( $_GET['cat_id'] => 1 ) ),
		
		'S_AUTH_LIST' => $pafiledb->modules[$pafiledb->module_name]->auth_can_list,
		
		'MX_PAGE' => $page_id,
		'L_MODULE_VERSION' => $pa_module_version,
		'L_MODULE_ORIG_AUTHOR' => $pa_module_orig_author,
		'L_MODULE_AUTHOR' => $pa_module_author,
		'S_JUMPBOX_ACTION' => append_sid( pa_this_mxurl( ) ),
		'S_TIMEZONE' => sprintf( $lang['All_times'], $lang[number_format( $board_config['board_timezone'] )] ) ) 
	);
	
	$pafiledb->modules[$pafiledb->module_name]->_pafiledb();
	
	if ( !MXBB_MODULE || MXBB_27x )
	{
		$pafiledb_template->assign_block_vars( 'copy_footer', array() );
	}
		
	if ( !isset( $_GET['explain'] ) )
	{
		$pafiledb_template->display( 'body' );
	}
	
	$pafiledb_cache->unload();

	if ( $action != 'download' )
	{
		if ( !$is_block )
		{
			//include( $mx_root_path . 'includes/page_tail.' . $phpEx );
		}
	}
}

?>