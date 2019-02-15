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
 *    $Id: pa_post_comment.php,v 1.12 2005/12/08 15:15:13 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */

class pafiledb_post_comment extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $pafiledb_functions, $lang, $board_config, $phpEx, $pafiledb_config, $db, $images, $userdata, $_POST;
		global $html_entities_match, $html_entities_replace, $unhtml_specialchars_match, $unhtml_specialchars_replace;
		global $mx_root_path, $module_root_path, $phpbb_root_path, $is_block, $phpEx, $mx_request_vars;

		//
		// Includes
		//
		include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx);
		include_once( $phpbb_root_path . 'includes/functions_post.' . $phpEx );

		//
		// Request vars
		//
		$cid = $mx_request_vars->request('cid', MX_TYPE_INT, '');
		
		if ( $mx_request_vars->is_request('item_id') && $mx_request_vars->is_request('cat_id') )
		{
			$item_id = $mx_request_vars->request('item_id', MX_TYPE_INT, 0);
			$cat_id = $mx_request_vars->request('cat_id', MX_TYPE_INT, 0);
		}
		else
		{
			mx_message_die( GENERAL_MESSAGE, $lang['File_not_exist'] );
		}

		$delete = $mx_request_vars->request('delete', MX_TYPE_NO_TAGS, '');
		$submit = $mx_request_vars->is_request('submit');
		$preview = $mx_request_vars->is_request('preview');

		$subject = ( !empty( $_POST['subject'] ) ) ? htmlspecialchars( trim( stripslashes( $_POST['subject'] ) ) ) : '';
		$message = ( !empty( $_POST['message'] ) ) ? htmlspecialchars( trim( stripslashes( $_POST['message'] ) ) ) : '';

		$sql = "SELECT file_name, file_catid
			FROM " . PA_FILES_TABLE . " 
			WHERE file_id = '" . $item_id . "'";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt select download', '', __LINE__, __FILE__, $sql );
		}

		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['File_not_exist'] );
		}

		$db->sql_freeresult( $result );

		if ( ( !$this->auth[$file_data['file_catid']]['auth_post_comment'] ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(append_sid($mx_root_path . "login.$phpEx?redirect=".pa_this_mxurl("action=post_comment&item_id=" . $item_id), true));
			}

			$message = sprintf( $lang['Sorry_auth_download'], $this->auth[$file_data['file_catid']]['auth_post_comment_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$html_on = ( $pafiledb_config['allow_comment_html'] ) ? true : 0;
		$bbcode_on = ( $pafiledb_config['allow_comment_bbcode'] ) ? true : 0;
		$smilies_on = ( $pafiledb_config['allow_comment_smilies'] ) ? true : 0; 
		$links_on = ( $pafiledb_config['allow_comment_links'] ) ? true : 0; 
		$images_on = ( $pafiledb_config['allow_comment_images'] ) ? true : 0; 
		
		// =======================================================
		// Delete
		// =======================================================
		if ( $delete == 'do' )
		{
			$sql = 'SELECT *
				FROM ' . PA_FILES_TABLE . "
				WHERE file_id = $item_id";
			
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
			}
			$file_info = $db->sql_fetchrow( $result );

			if ( ( $this->auth[$file_info['file_catid']]['auth_delete_comment'] && $file_info['user_id'] == $userdata['user_id'] ) || $this->auth[$file_info['file_catid']]['auth_mod'] )
			{
				if ( $this->comments[$file_data['file_catid']]['internal_comments'] )
				{				
					$sql = 'DELETE FROM ' . PA_COMMENTS_TABLE . "
					WHERE comments_id = $cid";
	
					if ( !( $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt delete comment', '', __LINE__, __FILE__, $sql );
					}
				}
				else 
				{
					include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
					$pafiledb_comments = new pafiledb_comments();				
					$pafiledb_comments->init( $file_info, 'phpbb' );	
					$pafiledb_comments->post('delete', $cid);
				}

				$this->_pafiledb();
				$message = $lang['Comment_deleted'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( pa_this_mxurl( "action=file&file_id=$item_id" ) ) . '">', '</a>' );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
			else
			{
				$message = sprintf( $lang['Sorry_auth_delete'], $this->auth[$cat_id]['auth_upload_type'] );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
		}

		// =======================================================
		// Main
		// =======================================================		
		if ( !$submit )
		{ 
			//
			// Instatiate text tools
			//
			$mx_pa_text_tools = new mx_pa_text_tools();
			
			//
			// Generate smilies listing for page output
			//
			$pafiledb_functions->pa_generate_smilies( 'inline', PAGE_POSTING );
			$html_status = ( $html_on ) ? $lang['HTML_is_ON'] : $lang['HTML_is_OFF'];
			$bbcode_status = ( $bbcode_on ) ? $lang['BBCode_is_ON'] : $lang['BBCode_is_OFF'];
			$smilies_status = ( $smilies_on ) ? $lang['Smilies_are_ON'] : $lang['Smilies_are_OFF'];
			$links_status = ( $links_on ) ? $lang['Links_are_ON'] : $lang['Links_are_OFF'];
			$images_status = ( $images_on ) ? $lang['Images_are_ON'] : $lang['Images_are_OFF'];
				
			if ( $mx_request_vars->is_get('cid') )
			{
				if ( $this->comments[$file_data['file_catid']]['internal_comments'] )
				{
					//
					// Query internal comment to edit
					//
					$sql = 'SELECT c.*, u.*
						FROM ' . PA_COMMENTS_TABLE . ' AS c 
							LEFT JOIN ' . USERS_TABLE . " AS u ON c.poster_id = u.user_id
						WHERE c.file_id = '" . $item_id . "'
						AND c.comments_id = '" . $mx_request_vars->request('cid', MX_TYPE_INT, '') . "'";
					
					$comment_arg_title = 'comments_title';	
					$comment_arg_message = 'comments_text';	
					$comment_arg_bbcode_uid = 'comment_bbcode_uid';	
				}
				else 
				{
					//
					// Query internal comment to edit
					// Note: cid = post_id
					//
					$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, u.user_allowsmile, p.*,  pt.post_text, pt.post_subject, pt.bbcode_uid
						FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
						WHERE pt.post_id = p.post_id
							AND u.user_id = p.poster_id
							AND p.post_id = '" . $mx_request_vars->request('cid', MX_TYPE_INT, '') . "'";	
					
					$comment_arg_title = 'post_subject';	
					$comment_arg_message = 'post_text';	
					$comment_arg_bbcode_uid = 'bbcode_uid';				
					
				}
		
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, 'Couldnt select comments', '', __LINE__, __FILE__, $sql );
				}
				
				$comment_row = $db->sql_fetchrow( $result );
						
				//
				// Edit comment
				//
				$comment_title = stripslashes($comment_row[$comment_arg_title]);
				$comment_body = $comment_row[$comment_arg_message];
				
				if ( $comment_row[$comment_arg_bbcode_uid] != '' )
				{
					$comment_body = preg_replace('/\:(([a-z0-9]:)?)' . $comment_row[$comment_arg_bbcode_uid] . '/s', '', $comment_body);
				}
		
				$comment_body = str_replace('<', '&lt;', $comment_body);
				$comment_body = str_replace('>', '&gt;', $comment_body);
				$comment_body = str_replace('<br />', "\n", $comment_body);
								
				$hidden_form_fields = '<input type="hidden" name="action" value="post_comment">
							<input type="hidden" name="cat_id" value="' . $cat_id . '">
							<input type="hidden" name="item_id" value="' . $item_id . '">
							<input type="hidden" name="cid" value="' . $mx_request_vars->request('cid', MX_TYPE_INT, '') . '">
							<input type="hidden" name="comment" value="post">'; 				
			}
			else 
			{
				//
				// New comment
				//
				$comment_title = '';
				$comment_body = '';
	
				$hidden_form_fields = '<input type="hidden" name="action" value="post_comment">
							<input type="hidden" name="cat_id" value="' . $cat_id . '">
							<input type="hidden" name="item_id" value="' . $item_id . '">
							<input type="hidden" name="comment" value="post">'; 				
			}

			
			//
			// Output the data to the template
			//
			$pafiledb_template->assign_vars( array( 
				'HTML_STATUS' => $html_status,
				'BBCODE_STATUS' => sprintf( $bbcode_status, '<a href="' . append_sid( "faq.$phpEx?mode=bbcode" ) . '" target="_phpbbcode">', '</a>' ),
				'SMILIES_STATUS' => $smilies_status,
				'LINKS_STATUS' => $links_status,
				'IMAGES_STATUS' => $images_status,
				'FILE_NAME' => $file_data['file_name'],
				'DOWNLOAD' => $pafiledb_config['module_name'],
				'MESSAGE_LENGTH' => $pafiledb_config['max_comment_chars'],
				
				'TITLE' => $comment_title,
				'COMMENT' => $comment_body,

				'L_COMMENT_ADD' => $lang['Comment_add'],
				'L_COMMENT' => $lang['Message_body'],
				'L_COMMENT_TITLE' => $lang['Subject'],
				'L_OPTIONS' => $lang['Options'],
				'L_COMMENT_EXPLAIN' => sprintf( $lang['Comment_explain'], $pafiledb_config['max_comment_chars'] ),
				'L_PREVIEW' => $lang['Preview'],
				'L_SUBMIT' => $lang['Submit'],
				'L_DOWNLOAD' => $lang['Download'],

				'L_INDEX' => "<<",
				'L_CHECK_MSG_LENGTH' => $lang['Check_message_length'],
				'L_MSG_LENGTH_1' => $lang['Msg_length_1'],
				'L_MSG_LENGTH_2' => $lang['Msg_length_2'],
				'L_MSG_LENGTH_3' => $lang['Msg_length_3'],
				'L_MSG_LENGTH_4' => $lang['Msg_length_4'],
				'L_MSG_LENGTH_5' => $lang['Msg_length_5'],
				'L_MSG_LENGTH_6' => $lang['Msg_length_6'],

				'L_BBCODE_B_HELP' => $lang['bbcode_b_help'],
				'L_BBCODE_I_HELP' => $lang['bbcode_i_help'],
				'L_BBCODE_U_HELP' => $lang['bbcode_u_help'],
				'L_BBCODE_Q_HELP' => $lang['bbcode_q_help'],
				'L_BBCODE_C_HELP' => $lang['bbcode_c_help'],
				'L_BBCODE_L_HELP' => $lang['bbcode_l_help'],
				'L_BBCODE_O_HELP' => $lang['bbcode_o_help'],
				'L_BBCODE_P_HELP' => $lang['bbcode_p_help'],
				'L_BBCODE_W_HELP' => $lang['bbcode_w_help'],
				'L_BBCODE_A_HELP' => $lang['bbcode_a_help'],
				'L_BBCODE_S_HELP' => $lang['bbcode_s_help'],
				'L_BBCODE_F_HELP' => $lang['bbcode_f_help'],
				'L_EMPTY_MESSAGE' => $lang['Empty_message'],

				'L_FONT_COLOR' => $lang['Font_color'],
				'L_COLOR_DEFAULT' => $lang['color_default'],
				'L_COLOR_DARK_RED' => $lang['color_dark_red'],
				'L_COLOR_RED' => $lang['color_red'],
				'L_COLOR_ORANGE' => $lang['color_orange'],
				'L_COLOR_BROWN' => $lang['color_brown'],
				'L_COLOR_YELLOW' => $lang['color_yellow'],
				'L_COLOR_GREEN' => $lang['color_green'],
				'L_COLOR_OLIVE' => $lang['color_olive'],
				'L_COLOR_CYAN' => $lang['color_cyan'],
				'L_COLOR_BLUE' => $lang['color_blue'],
				'L_COLOR_DARK_BLUE' => $lang['color_dark_blue'],
				'L_COLOR_INDIGO' => $lang['color_indigo'],
				'L_COLOR_VIOLET' => $lang['color_violet'],
				'L_COLOR_WHITE' => $lang['color_white'],
				'L_COLOR_BLACK' => $lang['color_black'],

				'L_FONT_SIZE' => $lang['Font_size'],
				'L_FONT_TINY' => $lang['font_tiny'],
				'L_FONT_SMALL' => $lang['font_small'],
				'L_FONT_NORMAL' => $lang['font_normal'],
				'L_FONT_LARGE' => $lang['font_large'],
				'L_FONT_HUGE' => $lang['font_huge'],
				'L_BBCODE_CLOSE_TAGS' => $lang['Close_Tags'],
				'L_STYLES_TIP' => $lang['Styles_tip'],

				'U_INDEX' => append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD_HOME' => append_sid( pa_this_mxurl() ),
				'U_FILE_NAME' => append_sid( pa_this_mxurl( 'action=file&item_id=' . $item_id ) ),

				'S_POST_ACTION' => append_sid( pa_this_mxurl() ),
				'S_HIDDEN_FORM_FIELDS' => $hidden_form_fields ) 
			); 
			
			//	
			// Show preview stuff if user clicked preview
			//
			if ( $preview )
			{
				$orig_word = array();
				$replacement_word = array();
				obtain_word_list( $orig_word, $replacement_word );

				$comment_bbcode_uid = ( $bbcode_on ) ? make_bbcode_uid() : '';
				$comments_text = stripslashes( prepare_message( addslashes( unprepare_message( $message ) ), $html_on, $bbcode_on, $smilies_on, $comment_bbcode_uid ) );

				$title = $subject;

				if ( !$html_on )
				{
					//
					// 
					//
					$comments_text = preg_replace( '#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $comments_text );
				}

				// Now we run comment suite before checking for smilies
				// so admins can add them in messages if they like
				// and so smilies are not counted as images in sigs.
				// this is done here again incase above conditions are
				// not met.
				if (!$pafiledb_config['allow_comment_images'] || !$pafiledb_config['allow_comment_links'])
				{
					$comments_text = $mx_kb_text_tools->remove_images_links( $comments_text, $pafiledb_config['allow_comment_images'], $pafiledb_config['no_comment_image_message'], $pafiledb_config['allow_comment_links'], $pafiledb_config['no_comment_link_message'] );
				}

				if ( $bbcode_on )
				{
					//
					// 
					//
					$comments_text = bbencode_second_pass( $comments_text, $comment_bbcode_uid ); // : preg_replace('/\:[0-9a-z\:]+\]/si', ']', $comments_text);
				}

				if ( !empty( $orig_word ) )
				{
					$title = ( !empty( $title ) ) ? preg_replace( $orig_word, $replacement_word, $title ) : '';
					$comments_text = ( !empty( $comments_text ) ) ? preg_replace( $orig_word, $replacement_word, $comments_text ) : '';
				} 
				
				//
				// Make Clickable
				//
				$comments_text = make_clickable( $comments_text ); 
				
				//
				// Parse smilies
				//
				if ( $smilies_on )
				{
					$comments_text = smilies_pass( $comments_text );
				}

				$comments_text = str_replace( "\n", '<br />', $comments_text );

				$pafiledb_template->assign_vars( array( 
					'PREVIEW' => true,
					'COMMENT' => stripslashes( $_POST['message'] ),
					'SUBJECT' => stripslashes( $_POST['subject'] ),
					'PRE_COMMENT' => $comments_text ) 
				);
			}
		}

		// =======================================================
		// Submit
		// =======================================================		
		if ( $submit )
		{
			//
			// vars
			//
			$length = strlen( $_POST['message'] );
			
			$title = ( !empty( $_POST['subject'] ) ) ? htmlspecialchars( trim ( $_POST['subject'] ) ) : '';

			$comment_bbcode_uid = make_bbcode_uid();
			$comments_text = str_replace( '<br />', "\n", $_POST['message'] );
			$comments_text = prepare_message( trim($comments_text), $html_on, $bbcode_on, $smilies_on, $comment_bbcode_uid );
			$comments_text = bbencode_first_pass( $comments_text, $comment_bbcode_uid );
			
			if ( $length > $pafiledb_config['max_comment_chars'] )
			{
				mx_message_die( GENERAL_ERROR, 'Your comment is too long!<br/>The maximum length allowed in characters is ' . $pafiledb_config['max_comment_chars'] . '' );
			}

			if ( $mx_request_vars->is_request('cid') )
			{
				if ( $this->comments[$file_data['file_catid']]['internal_comments'] )
				{
					$sql = "UPDATE " . PA_COMMENTS_TABLE . "
						SET comments_text = '" . str_replace( "\'", "''", $comments_text ) . "', 
					          comments_title = '" . str_replace( "\'", "''", $title ) . "', 
					          comment_bbcode_uid = '" . $comment_bbcode_uid . "'
					    WHERE comments_id = " . $mx_request_vars->request('cid', MX_TYPE_INT, 0) . "
							AND file_id = ". $item_id;						
				}		
				else 
				{		
					include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
					$pafiledb_comments = new pafiledb_comments();
					$pafiledb_comments->init( $item_id );	
									
					$pafiledb_comments->post( 'update', $cid, $title, $comments_text, $userdata['user_id'], $userdata['username'], 0, '', '', $comment_bbcode_uid);
					//$pafiledb_comments->insert_phpbb_post( $title, $comments_text, $forum_id, $userdata['user_id'], $userdata['username'], 0, $topic_id, $cid,  '', '', $comment_bbcode_uid );
				}
	
			}
			else 
			{
				if ( $this->comments[$file_data['file_catid']]['internal_comments'] )
				{
					$time = time();
					$poster_id = intval( $userdata['user_id'] );				
					$sql = "INSERT INTO " . PA_COMMENTS_TABLE . "(file_id, comments_text, comments_title, comments_time, comment_bbcode_uid, poster_id) 
						VALUES('$item_id','" . str_replace( "\'", "''", $comments_text ) . "','" . str_replace( "\'", "''", $title ) . "','$time', '$comment_bbcode_uid','$poster_id')";
				}
				else 
				{	
					include( $module_root_path . 'pafiledb/includes/functions_comment.' . $phpEx );
					$pafiledb_comments = new pafiledb_comments();
					$pafiledb_comments->init( $item_id );	
										
					$pafiledb_comments->post( 'insert', '', $title, $comments_text, $userdata['user_id'], $userdata['username'], 0, '', '', $comment_bbcode_uid);
					//$pafiledb_comments->insert_phpbb_post( $title, $comments_text, $forum_id, $userdata['user_id'], $userdata['username'], 0, $topic_id, '',  '', '', $comment_bbcode_uid );

				}

			}
			
			//
			// Notification
			//
			
			
			//
			// Done
			//
			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt insert comments', '', __LINE__, __FILE__, $sql );
			}

			$message = $lang['Comment_posted'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $item_id ) ) . '">', '</a>' );
			mx_message_die( GENERAL_MESSAGE, $message );
		}
		
		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );
					
		$this->display( $lang['Download'], 'pa_comment_posting.tpl' );
	}
	
}

?>