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
 *    $Id: admin_pa_fchecker.php,v 1.14 2005/12/11 16:19:20 jonohlsson Exp $
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
		$module['pafileDB_Download']['6_Fchecker_title'] = $file;
		return;
	}	

	// Load default header
	
	$no_page_header = true;

	require( './pagestart.' . $phpEx );
	
	include_once( $phpbb_root_path . 'includes/functions_admin.'.$phpEx );
	include_once( $phpbb_root_path . 'pafiledb/pafiledb_common.' . $phpEx );
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
		$module['pafileDB_Download']['6_Fchecker_title'] = 'modules/mx_pafiledb/admin/' . $file;
		return;
	}	
	
	$no_page_header = true;
	$module_root_path = './../';
	$mx_root_path = './../../../';
	
	define( 'MXBB_27x', file_exists( $mx_root_path . 'mx_login.php' ) );
	
	require( $mx_root_path . 'extension.inc' );
	require( $mx_root_path . 'admin/pagestart.' . $phpEx );
	
	include_once( $module_root_path . 'pafiledb/pafiledb_common.' . $phpEx );
	include_once( $mx_root_path . 'admin/page_header_admin.' . $phpEx );
}

$this_dir = $module_root_path . 'pafiledb/uploads/';

// MX
$html_path = PORTAL_URL . $module_root_path . 'pafiledb/uploads/';

if ( isset( $HTTP_GET_VARS['safety'] ) || isset( $HTTP_POST_VARS['safety'] ) )
{
	$safety = ( isset( $HTTP_POST_VARS['safety'] ) ) ? intval( $HTTP_POST_VARS['safety'] ) : intval( $HTTP_GET_VARS['safety'] );
}

/* - Original
$template->set_filenames(array(
    	'admin' => 'admin/pa_admin_file_checker.tpl')
);
*/
// MX Module
$template->set_filenames( array( 'admin' => 'admin/pa_admin_file_checker.tpl' ) 
	);

$template->assign_vars( array( 'L_FILE_CHECKER' => $lang['File_checker'],
		'L_FCHECKER_EXPLAIN' => $lang['File_checker_explain'] ) 
	);

if ( $safety == 1 )
{
	$saved = 0;

	$template->assign_block_vars( "check", array() );

	$template->assign_vars( array( 'L_FILE_CHECKER_SP1' => $lang['Checker_sp1'] ) 
		);

	$sql = "SELECT * FROM " . PA_FILES_TABLE;

	if ( !( $overall_result = $db->sql_query( $sql ) ) )
	{
		mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
	}

	while ( $temp = $db->sql_fetchrow( $overall_result ) )
	{
		$temp_dlurl = $temp['file_dlurl'];
		if ( substr( $temp_dlurl, 0, strlen( $html_path ) ) !== $html_path )
		{
			continue;
		}

		if ( !is_file( $this_dir . "/" . str_replace( $html_path, "", $temp_dlurl ) ) )
		{
			/*			$sql = "DELETE FROM " . PA_FILES_TABLE . " WHERE file_dlurl = '" . $temp_dlurl . "'";
			if ( !($db->sql_query($sql)) )
			{
				mx_message_die(GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql);
			}*/
			$template->assign_block_vars( "check.check_step1", array( 'DEL_DURL' => $temp_dlurl ) 
				);
		}
	}

	$template->assign_vars( array( 'L_FILE_CHECKER_SP2' => $lang['Checker_sp2'] ) 
		);
	$sql = "SELECT * FROM " . PA_FILES_TABLE;

	if ( !( $overall_result = $db->sql_query( $sql ) ) )
	{
		mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
	}
	while ( $temp = $db->sql_fetchrow( $overall_result ) )
	{
		$temp_ssurl = $temp['file_ssurl'];
		$temp_file_id = $temp['file_id'];
		if ( substr( $temp_ssurl, 0, strlen( $html_path ) ) !== $html_path )
		{
			continue;
		}

		if ( !is_file( $this_dir . "/" . str_replace( $html_path, "", $temp_ssurl ) ) )
		{
			/*$sql = "UPDATE " . PA_FILES_TABLE . " SET file_ssurl='' WHERE file_id = '" . $temp_file_id . "'";

			if ( !($db->sql_query($sql)) )
			{
				mx_message_die(GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql);
			}*/

			$template->assign_block_vars( "check.check_step2", array( 'DEL_SSURL' => $temp_file_id ) 
				);
		}
	}

	$template->assign_vars( array( 'L_FILE_CHECKER_SP3' => $lang['Checker_sp3'] ) 
		);

	$files = opendir( $this_dir );
	while ( $temp = readdir( $files ) )
	{
		if ( $temp == "." || $temp == ".." )
		{
			continue;
		}
		if ( !is_file( $this_dir . $temp ) )
		{
			continue;
		}

		$sql = "SELECT * FROM " . PA_FILES_TABLE . " WHERE file_dlurl = '" . $html_path . $temp . "' OR file_ssurl = '" . $html_path . $temp . "'";
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
		}
		$numhits = $db->sql_numrows( $result );

		if ( !$numhits )
		{
			$saved = $saved + filesize( $this_dir . $temp ); 
			// unlink($this_dir.$temp);
			$template->assign_block_vars( "check.check_step3", array( 'DEL_FILE' => $temp ) 
				);
		}
	}
	closedir( $files );

	if ( $saved == 0 )
	{
		$saved = "N/A";
	}elseif ( $saved >= 1073741824 )
	{
		$saved = round( $saved / 1073741824 * 100 ) / 100 . " Giga Byte";
	}elseif ( $saved >= 1048576 )
	{
		$saved = round( $saved / 1048576 * 100 ) / 100 . " Mega Byte";
	}elseif ( $saved >= 1024 )
	{
		$saved = round( $saved / 1024 * 100 ) / 100 . " Kilo Byte";
	}
	else
	{
		$saved = $saved . " Bytes";
	}

	$template->assign_vars( array( 'L_FILE_CHECKER_SAVED' => $lang['Checker_saved'],
			'SAVED' => $saved ) 
		);
}
else
{
	$template->assign_block_vars( "perform", array() );

	$lang['File_saftey'] = str_replace( "{html_path}", $html_path, $lang['File_saftey'] );

	$template->assign_vars( array( 'L_FILE_CHECKER' => $lang['File_checker'],
			'L_FILE_PERFORM' => $lang['File_checker_perform'],
			'L_FILE_SAFTEY' => $lang['File_saftey'] ) 
		);
}

// Output
include( $mx_root_path . 'admin/page_header_admin.' . $phpEx );
$template->pparse( 'admin' );
include( $mx_root_path . 'admin/page_footer_admin.' . $phpEx );

?>