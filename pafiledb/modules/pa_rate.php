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
 *    $Id: pa_rate.php,v 1.11 2005/12/08 15:15:13 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
/*
  paFileDB 3.0
  ©2001/2002 PHP Arena
  Written by Todd
  todd@phparena.net
  http://www.phparena.net
  Keep all copyright links on the script visible
  Please read the license included with this script for more information.
*/

class pafiledb_rate extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $lang, $board_config, $phpEx, $pafiledb_config, $db, $userdata;
		global $_REQUEST, $_POST, $phpbb_root_path, $pafiledb_user, $pafiledb_functions; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx, $mx_request_vars;

		// =======================================================
		// Request vars
		// =======================================================
		$file_id = $mx_request_vars->request('file_id', MX_TYPE_INT, '');
				
		if ( empty( $file_id ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['File_not_exist'] );
		}
				
		$rating = $mx_request_vars->request('rating', MX_TYPE_INT, 0);

		$sql = 'SELECT file_name, file_catid
			FROM ' . PA_FILES_TABLE . " 
			WHERE file_id = $file_id";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
		}

		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['File_not_exist'] );
		}

		$db->sql_freeresult( $result );

		if ( ( !$this->auth[$file_data['file_catid']]['auth_rate'] ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(append_sid($mx_root_path . "login.$phpEx?redirect=".pa_this_mxurl("action=rate&file_id=" . $file_id), true));
			}

			$message = sprintf( $lang['Sorry_auth_rate'], $this->auth[$file_data['file_catid']]['auth_rate_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$ipaddy = getenv ( "REMOTE_ADDR" );
		
		if ( $kb_config['votes_check_ip'] == 1 )
		{
			$sql = "SELECT * FROM " . KB_VOTES_TABLE . " WHERE votes_ip = '" . $ipaddy . "' AND votes_file = '" . $article_id . "'";
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query rate ip', '', __LINE__, __FILE__, $sql );
			}
		
			if ( $db->sql_numrows( $result ) > 0 )
			{
				$template->assign_vars( array( "META" => '<meta http-equiv="refresh" content="3;url=' . append_sid( this_kb_mxurl( "action=url&amp;k=" . $article_id ) ) . '">' ) 
					);
				$message = $lang['Rerror'] . "<br /><br />" . sprintf( $lang['Click_return_rate'], "<a href=\"" . append_sid( this_kb_mxurl( "mode=article&amp;k=$article_id" ) ) . "\">", "</a>" );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
		}
		
		if ( $kb_config['votes_check_userid'] == 1 )
		{
			$sql = "SELECT * FROM " . KB_VOTES_TABLE . " WHERE votes_userid = '" . $userdata['user_id'] . "' AND votes_file = '" . $article_id . "'";
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query rate ip', '', __LINE__, __FILE__, $sql );
			}
		
			if ( $db->sql_numrows( $result ) > 0 )
			{
				$template->assign_vars( array( "META" => '<meta http-equiv="refresh" content="3;url=' . append_sid( this_kb_mxurl( "action=url&amp;k=" . $article_id ) ) . '">' ) 
					);
				$message = $lang['Rerror'] . "<br /><br />" . sprintf( $lang['Click_return_rate'], "<a href=\"" . append_sid( this_kb_mxurl( "mode=article&amp;k=$article_id" ) ) . "\">", "</a>" );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
		}
				
		$pafiledb_template->assign_vars( array( 
				'L_INDEX' => "<<",
				'L_RATE' => $lang['Rate'],

				'U_INDEX' => append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD_HOME' => append_sid( pa_this_mxurl() ),
				'U_FILE_NAME' => append_sid( pa_this_mxurl( 'action=file&file_id=' . $file_id ) ),

				'FILE_NAME' => $file_data['file_name'],
				'DOWNLOAD' => $pafiledb_config['module_name'] ) 
			);

		if ( isset( $_POST['submit'] ) )
		{
			$result_msg = str_replace( "{filename}", $file_data['file_name'], $lang['Rconf'] );

			$result_msg = str_replace( "{rate}", $rating, $result_msg );

			if ( ( $rating <= 0 ) or ( $rating > 10 ) )
			{
				mx_message_die( GENERAL_ERROR, 'Bad submited value' );
			}

			$pafiledb_user->update_voter_info( $file_id, $rating );

			$rate_info = $pafiledb_functions->get_rating( $file_id );

			$result_msg = str_replace( "{newrating}", $rate_info, $result_msg );

			$message = $result_msg . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $file_id ) ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_forum'], '<a href="' . append_sid( $mx_root_path . 'index.' . $phpEx ) . '">', '</a>' );
			mx_message_die( GENERAL_MESSAGE, $message );
		}
		else
		{
			$rate_info = str_replace( "{filename}", $file_data['file_name'], $lang['Rateinfo'] );

			$pafiledb_template->assign_vars( array( 
					'S_RATE_ACTION' => append_sid( pa_this_mxurl( 'action=rate&file_id=' . $file_id ) ),
					'L_RATE' => $lang['Rate'],
					'L_RERROR' => $lang['Rerror'],
					'L_R1' => $lang['R1'],
					'L_R2' => $lang['R2'],
					'L_R3' => $lang['R3'],
					'L_R4' => $lang['R4'],
					'L_R5' => $lang['R5'],
					'L_R6' => $lang['R6'],
					'L_R7' => $lang['R7'],
					'L_R8' => $lang['R8'],
					'L_R9' => $lang['R9'],
					'L_R10' => $lang['R10'],
					'RATEINFO' => $rate_info,
					'ID' => $file_id ) 
				);
		}
		
		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );
		
		$this->display( $lang['Download'], 'pa_rate_body.tpl' );
	}
}

?>