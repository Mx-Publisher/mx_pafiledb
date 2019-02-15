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
 *    $Id: dload_quickdl.php,v 1.6 2005/12/08 15:15:11 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
if ( !function_exists( 'read_block_config' ) )
{
	define( 'IN_PORTAL', true );
	$mx_root_path = '../../';
	include_once( $mx_root_path . 'extension.inc' );
	include_once( $mx_root_path . 'common.' . $phpEx ); 
	
	// Start session management
	
	$userdata = session_pagestart( $user_ip, PAGE_INDEX );
	mx_init_userprefs( $userdata ); 
	
	// End session management
	
	$block_id = ( !empty( $HTTP_GET_VARS['block_id'] ) ) ? $HTTP_GET_VARS['block_id'] : $HTTP_POST_VARS['id'];
	if ( empty( $block_id ) )
	{
		$sql = "SELECT * FROM " . BLOCK_TABLE . "  WHERE block_title = 'PafileDB' LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, "Could not query PafileDB module information", "", __LINE__, __FILE__, $sql );
		}
		$row = $db->sql_fetchrow( $result );
		$block_id = $row['block_id'];
	}
	$is_block = false;
}
else
{ 
	//
	// Read Block Settings
	//
	$title = $mx_block->block_info['block_title'];
	$block_size = ( isset( $block_size ) && !empty( $block_size ) ? $block_size : '100%' );

	$is_block = true;
	global $images;
}

list( $trash, $mx_script_name_temp ) = split ( trim( $board_config['server_name'] ), PORTAL_URL );
$mx_script_name = preg_replace( '#^\/?(.*?)\/?$#', '\1', trim( $mx_script_name_temp ) );

// Setup config parameters
$config_name = array( 'pa_mapping', 'pa_quick_cat' );

for( $i = 0; $i < count( $config_name ); $i++ )
{
	$config_value = $mx_block->get_parameters( $config_name[$i] );
	$pafiledb_quickdl[$config_name[$i]] = $config_value;
}

// ===================================================
// Include the common file
// ===================================================
/* - orig
include($phpbb_root_path . 'pafiledb/pafiledb_common.'.$phpEx);
*/
// MX
include_once( $module_root_path . 'pafiledb/pafiledb_common.' . $phpEx );
// ===================================================
// Get action variable other wise set it to the main
// ===================================================
$action = ( isset( $_REQUEST['action'] ) ) ? htmlspecialchars( $_REQUEST['action'] ) : 'quickdl';
// ===================================================
// if the database disabled give them a nice message
// ===================================================
if ( intval( $pafiledb_config['module_enable'] ) )
{
	mx_message_die( GENERAL_MESSAGE, $lang['pafiledb_disable'] );
}
// ===================================================
// an array of all expected actions
// ===================================================
$actions = array( 'quickdl' => 'quickdl',
'download' => 'download' );
// ===================================================
// Lets Build the page
// ===================================================
$page_title = $lang['Download'];

if ( $action != 'download' )
{
	/* - orig
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);
	*/
	// MX
	if ( !$is_block )
	{
		include( $mx_root_path . 'includes/page_header.' . $phpEx );
	}
}

$pafiledb->module( $actions[$action] );
$pafiledb->modules[$actions[$action]]->main( $action );

if ( $action != 'download' )
{
	/* - orig
	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	*/
	// MX
	if ( !$is_block )
	{
		include( $mx_root_path . 'includes/page_tail.' . $phpEx );
	}
}

?>