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
 *    $Id: pafiledb_common.php,v 1.12 2005/12/08 15:15:13 jonohlsson Exp $
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

/*
// ===================================================
// addslashes to vars if magic_quotes_gpc is off
// ===================================================
if ( !@function_exists( 'slash_input_data' ) )
{
	function slash_input_data( &$data )
	{
		if ( is_array( $data ) )
		{
			foreach ( $data as $k => $v )
			{
				$data[$k] = ( is_array( $v ) ) ? slash_input_data( $v ) : addslashes( $v );
			}
		}
		return $data;
	}
}
// ===================================================
// to make it work with php version under 4.1 and other stuff
// ===================================================
if ( @phpversion() < '4.1' )
{
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
	$_SESSION = &$HTTP_SESSION_VARS;
}

if ( !isset( $_REQUEST ) )
{
	$_REQUEST = array_merge( $_GET, $_POST, $_COOKIE );
}

if ( !get_magic_quotes_gpc() )
{
	$_GET = slash_input_data( $_GET );
	$_POST = slash_input_data( $_POST );
	$_COOKIE = slash_input_data( $_COOKIE );
	$_REQUEST = slash_input_data( $_REQUEST );
}
*/

// ===================================================
// Include pafiledb data file
// ===================================================

include_once( $module_root_path . 'pafiledb/includes/pafiledb_constants.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/functions.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/functions_cache.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/functions_auth.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/functions_mx.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/functions_pafiledb.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/template.' . $phpEx );

$pafiledb_cache = new pafiledb_cache();
$pafiledb_functions = new pafiledb_functions();

if ( $pafiledb_cache->exists( 'config' ) )
{
	$pafiledb_config = $pafiledb_cache->get( 'config' );
}
else
{
	$pafiledb_config = $pafiledb_functions->pafiledb_config();
	$pafiledb_cache->put( 'config', $pafiledb_config );
}

$pafiledb_template = new pafiledb_template();
$pafiledb_template->set_template( $theme['template_name'] );

$pafiledb_user = new pafiledb_user_info();
$pafiledb = new pafiledb_public();

// ===================================================
// url rewrites
// ===================================================
function pa_this_mxurl( $args = '', $force_standalone_mode = false, $non_html_amp = false )
{
	global $mx_root_path, $module_root_path, $page_id, $phpEx, $is_block;

	if ( $force_standalone_mode || !$is_block )
	{
		$mxurl = $module_root_path . 'dload.' . $phpEx . ( $args == '' ? '' : '?' . $args );
	}
	else
	{
		$mxurl = $mx_root_path . 'index.' . $phpEx;
		
		if ( is_numeric( $page_id ) )
		{
			$mxurl .= '?page=' . $page_id . ( $args == '' ? '' : ( $non_html_amp ? '&' : '&amp;' ) . $args );
		}
		else
		{
			$mxurl .= ( $args == '' ? '' : '?' . $args );
		}
	}
	return $mxurl;
}

?>