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
 *    $Id: pa_file.php,v 1.15 2005/12/08 15:15:13 jonohlsson Exp $
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

class pafiledb_file extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $lang, $board_config, $phpEx, $pafiledb_config, $db, $images;
		global $phpbb_root_path, $userdata, $db, $pafiledb_functions; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx, $mx_request_vars;

		// =======================================================
		// Request vars
		// =======================================================
		$file_id = $mx_request_vars->request('file_id', MX_TYPE_INT, '');
				
		if ( empty( $file_id ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['File_not_exist'] );
		}
				
		// =======================================================
		// file id is not set, give him/her a nice error message
		// =======================================================
		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c, " . PA_CATEGORY_TABLE . " AS cat
					WHERE f.file_id = r.votes_file(+)
					AND f.user_id = u.user_id(+)
					AND f.file_id = c.file_id(+)
					AND f.file_id = $file_id
					AND f.file_approved = 1
					AND f.file_catid = cat.cat_id
					GROUP BY f.file_id ";
				break;

			default:
				$sql = "SELECT f.*, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) as total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f
						LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f.file_id = r.votes_file 
						LEFT JOIN " . USERS_TABLE . " AS u ON f.user_id = u.user_id
						LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f.file_id = c.file_id
						LEFT JOIN " . PA_CATEGORY_TABLE . " AS cat ON f.file_catid = cat.cat_id
					WHERE f.file_id = $file_id
					AND f.file_approved = 1
					GROUP BY f.file_id ";
				break;
		}

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
		} 
		
		// ===================================================
		// file doesn't exist'
		// ===================================================
		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['File_not_exist'] );
		}
		$db->sql_freeresult( $result ); 
		
		// ===================================================
		// Pafiledb auth for viewing file
		// ===================================================
		if ( ( !$this->auth[$file_data['file_catid']]['auth_view_file'] ) )
		{
			/*
			if ( !$userdata['session_logged_in'] )
			{
				mx_redirect(append_sid($mx_root_path . "login.$phpEx?redirect=".pa_this_mxurl("action=file&file_id=" . $file_id), true));
			}
			*/
			$message = sprintf( $lang['Sorry_auth_view'], $this->auth[$file_data['file_catid']]['auth_view_file_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$pafiledb_template->assign_vars( array( 
				'L_INDEX' => "<<",

				'U_INDEX' => append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD_HOME' => append_sid( pa_this_mxurl() ),

				'FILE_NAME' => $file_data['file_name'],
				'DOWNLOAD' => $pafiledb_config['module_name'] ) 
			);
			 
		// ===================================================
		// Prepare file info to display them
		// ===================================================
		$file_time = create_date( $board_config['default_dateformat'], $file_data['file_time'], $board_config['board_timezone'] );
		$file_last_download = ( $file_data['file_last'] ) ? create_date( $board_config['default_dateformat'], $file_data['file_last'], $board_config['board_timezone'] ) : $lang['never'];
		$file_update_time = ( $file_data['file_update_time'] ) ? create_date( $board_config['default_dateformat'], $file_data['file_update_time'], $board_config['board_timezone'] ) : $lang['never'];
		$file_author = trim( $file_data['file_creator'] );
		$file_version = trim( $file_data['file_version'] );
		$file_screenshot_url = trim( $file_data['file_ssurl'] );
		$file_website_url = trim( $file_data['file_docsurl'] );
		$file_download_link = ( $file_data['file_license'] > 0 ) ? append_sid( pa_this_mxurl( 'action=license&license_id=' . $file_data['file_license'] . '&file_id=' . $file_id ) ) : append_sid( pa_this_mxurl( 'action=download&file_id=' . $file_id, 1 ) );
		$file_size = $pafiledb_functions->get_file_size( $file_id, $file_data );

		$file_poster = ( $file_data['user_id'] != ANONYMOUS ) ? '<a href="' . append_sid( $phpbb_root_path . 'profile.' . $phpEx . '?mode=viewprofile&amp;' . POST_USERS_URL . '=' . $file_data['user_id'] ) . '">' : '';
		$file_poster .= ( $file_data['user_id'] != ANONYMOUS ) ? $file_data['username'] : $lang['Guest'];
		$file_poster .= ( $file_data['user_id'] != ANONYMOUS ) ? '</a>' : '';
		
		$pafiledb_template->assign_vars( array( 
			'L_CLICK_HERE' => $lang['Click_here'],
			'L_AUTHOR' => $lang['Creator'],
			'L_VERSION' => $lang['Version'],
			'L_SCREENSHOT' => $lang['Scrsht'],
			'L_WEBSITE' => $lang['Docs'],
			'L_FILE' => $lang['File'],
			'L_DESC' => $lang['Desc'],
			'L_DATE' => $lang['Date'],
			'L_UPDATE_TIME' => $lang['Update_time'],
			'L_LASTTDL' => $lang['Lastdl'],
			'L_DLS' => $lang['Dls'],
			'L_SIZE' => $lang['File_size'],
			'L_EDIT' => $lang['Editfile'],
			'L_DELETE' => $lang['Deletefile'],
			'L_DOWNLOAD' => $lang['Downloadfile'],
			'L_EMAIL' => $lang['Emailfile'],
			'L_SUBMITED_BY' => $lang['Submiter'],

			'SHOW_AUTHOR' => ( !empty( $file_author ) ) ? true : false,
			'SHOW_VERSION' => ( !empty( $file_version ) ) ? true : false,
			'SHOW_SCREENSHOT' => ( !empty( $file_screenshot_url ) ) ? true : false,
			'SHOW_WEBSITE' => ( !empty( $file_website_url ) ) ? true : false,
			'SS_AS_LINK' => ( $file_data['file_sshot_link'] ) ? true : false,
			'FILE_NAME' => $file_data['file_name'],
			'FILE_LONGDESC' => nl2br( $file_data['file_longdesc'] ),
			'FILE_SUBMITED_BY' => $file_poster,
			'FILE_AUTHOR' => $file_author,
			'FILE_VERSION' => $file_version,
			'FILE_SCREENSHOT' => $file_screenshot_url,
			'FILE_WEBSITE' => $file_website_url,

			'AUTH_EDIT' => ( ( $this->auth[$file_data['file_catid']]['auth_edit_file'] && $file_data['user_id'] == $userdata['user_id'] ) || $this->auth[$file_data['file_catid']]['auth_mod'] ) ? true : false,
			'AUTH_DELETE' => ( ( $this->auth[$file_data['file_catid']]['auth_delete_file'] && $file_data['user_id'] == $userdata['user_id'] ) || $this->auth[$file_data['file_catid']]['auth_mod'] ) ? true : false,
			'AUTH_DOWNLOAD' => ( $this->auth[$file_data['file_catid']]['auth_download'] ) ? true : false,
			'AUTH_EMAIL' => ( $this->auth[$file_data['file_catid']]['auth_email'] ) ? true : false,
				
			'DELETE_IMG' => $phpbb_root_path . $images['icon_delpost'],
			'EDIT_IMG' => $phpbb_root_path . $images['icon_edit'],
			'DOWNLOAD_IMG' => $images['pa_download'],
			'EMAIL_IMG' => $images['pa_email'],
			'TIME' => $file_time,
			'UPDATE_TIME' => ( $file_data['file_update_time'] != $file_data['file_time'] ) ? $file_update_time : $lang['never'],
			'FILE_DLS' => intval( $file_data['file_dls'] ),
			'FILE_SIZE' => $file_size,
			'LAST' => $file_last_download,

			'U_DOWNLOAD' => $file_download_link,
			'U_DELETE' => append_sid( pa_this_mxurl( 'action=user_upload&do=delete&file_id=' . $file_id ) ),
			'U_EDIT' => append_sid( pa_this_mxurl( 'action=user_upload&file_id=' . $file_id ) ),

			'U_EMAIL' => append_sid( pa_this_mxurl( 'action=email&file_id=' . $file_id ) ) ) 
		);

		include( $module_root_path . 'pafiledb/includes/functions_field.' . $phpEx );
		$custom_field = new custom_field();
		$custom_field->init();
		$custom_field->display_data( $file_id );

		//
		// Ratings
		//
		if ( $this->ratings[$file_data['file_catid']]['activated'] )
		{
			$file_rating = ( $file_data['rating'] != 0 ) ? round( $file_data['rating'], 2 ) . ' / 10' : $lang['Not_rated'];
			
			if ( $this->auth[$file_data['file_catid']]['auth_rate'] )
			{
				$rate_img = $images['pa_rate'];
			}
			
			$pafiledb_template->assign_block_vars( 'use_ratings', array(
				'L_RATING' => $lang['DlRating'],
				'L_RATE' => $lang['Rate'],
				'L_VOTES' => $lang['Votes'],
				'FILE_VOTES' => $file_data['total_votes'],
				'RATING' => $file_rating,
				
				//
				// Allowed to rate
				//
				'RATE_IMG' => $rate_img,
				'U_RATE' => append_sid( pa_this_mxurl( 'action=rate&file_id=' . $file_id ) ),
			));
		}

		//
		// Comments
		//	
		if ( $this->comments[$file_data['file_catid']]['activated'] && $this->auth[$file_data['file_catid']]['auth_view_comment'])
		{
			$comments_type = $this->comments[$file_data['file_catid']]['internal_comments'] ? 'internal' : 'phpbb';
			
			//
			// Instatiate comments
			//			
			include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
			$pafiledb_comments = new pafiledb_comments();
			$pafiledb_comments->init( $file_data, $comments_type );
			$pafiledb_comments->display_comments();
		}
		
		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );

		//
		// User authorisation levels output
		//
		$this->auth_can($file_data['file_catid']);
				
		//
		// Output all
		//
		$this->display( $lang['Download'], 'pa_file_body.tpl' );
	}
}

?>