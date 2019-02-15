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
 *    $Id: functions_comment.php,v 1.15 2005/12/08 15:15:12 jonohlsson Exp $
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

class pafiledb_comments
{
	//
	// Comments
	//
	var $cid = ''; // comment id
	var $comments_type = ''; // internal or phpbb
	
	//
	// phpBB comments
	//
	var $forum_id = ''; 
	var $topic_id = ''; 
	
	// 
	// Module vars
	//
	var $cat_id = ''; 
	var $item_id = ''; // Article or file id etc

	//
	// General
	//
	var $auth = array();
	
	var $pagination_action = '';
	var $pagination_target = '';
	
	var $start = 0;
	var $pagination_num = 5; // number of comments per page
	var $total_comments = ''; // total number of comments
	
	var $comments_row = array();
	
	// ===================================================
	// Init Comment vars
	// ===================================================
	function init( $item_data, $comments_type = 1 )
	{
		global $pafiledb, $pafiledb_config, $db;
		
		if ( !is_object($pafiledb) || empty($pafiledb_config) )
		{
			mx_message_die(GENERAL_ERROR, 'Bad global arguments');
		}
		
		if (!is_array($item_data) && !empty($item_data))
		{
			$sql = 'SELECT *
				FROM ' . PA_FILES_TABLE . "
				WHERE file_id = $item_data";
			
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
			}
			
			$item_data = $db->sql_fetchrow( $result );			
		}
		
		$this->comments_type = $comments_type == 1 ? 'internal' : 'phpbb';
		$this->cat_id = $item_data['file_catid'];
		$this->item_id = $item_data['file_id'];
		
		$this->topic_id = $item_data['topic_id'];
		
		//
		// This is specific for pafileDB
		//
		$this->forum_id = $pafiledb->modules[$pafiledb->module_name]->cat_rowset[$this->cat_id]['comments_forum_id'];
	
		$this->auth['auth_view'] = $pafiledb->modules[$pafiledb->module_name]->auth[$this->cat_id]['auth_view_comment'];
		$this->auth['auth_post'] = $pafiledb->modules[$pafiledb->module_name]->auth[$this->cat_id]['auth_post_comment'];
		$this->auth['auth_edit'] = $pafiledb->modules[$pafiledb->module_name]->auth[$this->cat_id]['auth_edit_comment'];
		$this->auth['auth_delete'] = $pafiledb->modules[$pafiledb->module_name]->auth[$this->cat_id]['auth_delete_comment'];
		$this->auth['auth_mod'] = $pafiledb->modules[$pafiledb->module_name]->auth[$this->cat_id]['auth_mod'];	
		
		//
		// Pagination
		//
		$this->pagination_action = 'action=file';
		$this->pagination_target = 'file_id=';
		
		$this->pagination_num = empty($show_num_comments) ? $this->pagination_num : $show_num_comments;
		
	}

	// ===================================================
	// Get all internal comments
	// ===================================================
	function display_comments()
	{
		switch ($this->comments_type)
		{
			case 'internal':
				$this->display_internal_comments();
			break;
			
			case 'phpbb':
				$this->display_phpbb_comments();
			break;
			
			default:
			mx_message_die(GENERAL_ERROR, 'Bad display comment arg');			
		}
	}
		
	// ===================================================
	// Get all internal comments
	// ===================================================
	function display_internal_comments()
	{
		global $pafiledb_template, $pafiledb, $lang, $board_config, $phpEx, $pafiledb_config, $db, $userdata, $images;
		global $mx_root_path, $module_root_path, $phpbb_root_path, $is_block, $phpEx, $mx_request_vars;

		//
		// Request vars
		//		
		$this->start = $mx_request_vars->get('start', MX_TYPE_INT, 0);
		$page_num = $mx_request_vars->get('page_num', MX_TYPE_INT, '');

		//
		// page number (only used for kb articles)
		//
		if ( !empty( $page_num ) )
		{
			$page_num = "&page_num=" . ( $page_num + 1 ) ;
		}
		else
		{
			$page_num = '';
		}

		//
		// Instatiate text tools
		//
		$mx_pa_text_tools = new mx_pa_text_tools();
		
		//
		// Includes
		//
		include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx); 

		$pafiledb_template->assign_block_vars( 'use_comments', array( 
			'L_COMMENTS' => $lang['Comments'],
		));
				
		//
		// Define censored word matches
		//
		$orig_word = array();
		$replacement_word = array();
		obtain_word_list( $orig_word, $replacement_word );
	
		//
		// Get all comments
		//
		$this->get_internal_comments();
	
		$ranksrow = array();
		$this->obtain_ranks( $ranksrow );
	
		while ( $this->comments_row = $db->sql_fetchrow( $result ) )
		{
			$time = create_date( $board_config['default_dateformat'], $this->comments_row['comments_time'], $board_config['board_timezone'] );
			$comments_text = $this->comments_row['comments_text'];
	
			if ( !$pafiledb_config['allow_html'] )
			{
				if ( $comments_text != '' && $userdata['user_allowhtml'] )
				{
					$comments_text = preg_replace( '#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $comments_text );
				}
			}

			//
			// Remove Images and/or links
			//
			if (!$pafiledb_config['allow_comment_images'] || !$pafiledb_config['allow_comment_links'])
			{

				$comments_text = $mx_pa_text_tools->remove_images_links( $comments_text, $pafiledb_config['allow_comment_images'], $pafiledb_config['no_comment_image_message'], $pafiledb_config['allow_comment_links'], $pafiledb_config['no_comment_link_message'] );
			}
			
			if ( $pafiledb_config['allow_bbcode'] )
			{
				if ( $comments_text != '' && $this->comments_row['comment_bbcode_uid'] != '' )
				{
					$comments_text = ( $pafiledb_config['allow_bbcode'] ) ? bbencode_second_pass( $comments_text, $this->comments_row['comment_bbcode_uid'] ) : preg_replace( '/\:[0-9a-z\:]+\]/si', ']', $comments_text );
				}
			}
						
			$comments_text = make_clickable( $comments_text );
	
			if ( count( $orig_word ) )
			{
				if ( $comments_text != '' )
				{
					$comments_text = preg_replace( $orig_word, $replacement_word, $comments_text );
				}
			}
	
			if ( $pafiledb_config['allow_smilies'] )
			{
				if ( $userdata['user_allowsmile'] && $comments_text != '' )
				{
					$comments_text = mx_smilies_pass( $comments_text );
				}
			}
	
			$poster = ( $this->comments_row['user_id'] == ANONYMOUS ) ? $lang['Guest'] : $this->comments_row['username'];
			$poster_avatar = '';
			
			if ( $this->comments_row['user_avatar_type'] && $poster_id != ANONYMOUS && $this->comments_row['user_allowavatar'] )
			{
				switch ( $this->comments_row['user_avatar_type'] )
				{
					case USER_AVATAR_UPLOAD:
						$poster_avatar = ( $board_config['allow_avatar_upload'] ) ? '<img src="' . $phpbb_root_path . $board_config['avatar_path'] . '/' . $this->comments_row['user_avatar'] . '" alt="" border="0" />' : '';
						break;
					case USER_AVATAR_REMOTE:
						$poster_avatar = ( $board_config['allow_avatar_remote'] ) ? '<img src="' . $this->comments_row['user_avatar'] . '" alt="" border="0" />' : '';
						break;
					case USER_AVATAR_GALLERY:
						$poster_avatar = ( $board_config['allow_avatar_local'] ) ? '<img src="' . $phpbb_root_path . $board_config['avatar_gallery_path'] . '/' . $this->comments_row['user_avatar'] . '" alt="" border="0" />' : '';
						break;
				}
			} 
			
			//
			// Generate ranks, set them to empty string initially.
			//
			$poster_rank = '';
			$rank_image = '';
			if ( $this->comments_row['user_id'] == ANONYMOUS )
			{
			}
			else if ( $this->comments_row['user_rank'] )
			{
				for( $j = 0; $j < count( $ranksrow ); $j++ )
				{
					if ( $this->comments_row['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'] )
					{
						$poster_rank = $ranksrow[$j]['rank_title'];
						$rank_image = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $phpbb_root_path . $ranksrow[$j]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
					}
				}
			}
			else
			{
				for( $j = 0; $j < count( $ranksrow ); $j++ )
				{
					if ( $this->comments_row['user_posts'] >= $ranksrow[$j]['rank_min'] && !$ranksrow[$j]['rank_special'] )
					{
						$poster_rank = $ranksrow[$j]['rank_title'];
						$rank_image = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $phpbb_root_path . $ranksrow[$j]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
					}
				}
			}
	
			$comments_title = $this->comments_row['comments_title'];
			$comments_text = str_replace( "\n", "\n<br />\n", $comments_text );
	
			//
			// Text formatting
			//
			if ( $pafiledb_config['max_comment_subject_chars'] > 0 )
			{
				$comments_title = $mx_pa_text_tools->truncate_text( $comments_title, $pafiledb_config['max_comment_subject_chars'], true );
			}
	
			if ( $pafiledb_config['max_comment_chars'] > 0 )
			{
				$comments_text = $mx_pa_text_tools->truncate_text( $comments_text, $pafiledb_config['max_comment_chars'], true );
			}		
			
			if ( $pafiledb_config['formatting_comment_truncate_links'] || $pafiledb_config['formatting_comment_image_resize'] > 0 || $pafiledb_config['formatting_comment_wordwrap'] ) 
			{
				$comments_text = $mx_pa_text_tools->decode( $comments_text, $pafiledb_config['formatting_comment_truncate_links'], intval($pafiledb_config['formatting_comment_image_resize']), $pafiledb_config['formatting_comment_wordwrap'] );
			}
						
			$pafiledb_template->assign_block_vars( 'use_comments.text', array( 
				'L_POSTED' => $lang['Posted'],
				'L_COMMENT_SUBJECT' => $lang['Comment_subject'],
				'L_COMMENTS_NAME' => $lang['Name'],			
				'POSTER' => $poster,
				'ICON_MINIPOST_IMG' => $phpbb_root_path . $images['icon_minipost'],
				'ICON_SPACER' => $phpbb_root_path . "templates/subSilver/images/spacer.gif", 
				'POSTER_RANK' => $poster_rank,
				'RANK_IMAGE' => $rank_image,
				'POSTER_AVATAR' => $poster_avatar,
				'TITLE' => $comments_title,
				'TIME' => $time,
				'TEXT' => $comments_text 
			));

			if ( ( $this->auth['auth_edit'] && $this->comments_row['user_id'] == $userdata['user_id'] ) || $this->auth['auth_mod'] )
			{
				$pafiledb_template->assign_block_vars( 'use_comments.text.auth_edit', array(
					'L_COMMENT_EDIT' => $lang['Comment_edit'],
					'U_COMMENT_EDIT' => append_sid( pa_this_mxurl( 'action=post_comment&item_id=' . $this->item_id . '&cat_id=' . $this->cat_id . '&cid='.$this->comments_row['comments_id'] ) ),
					'EDIT_IMG' => $phpbb_root_path . $images['icon_edit'], 
				));
			}
						
			if ( ( $this->auth['auth_delete'] && $this->comments_row['user_id'] == $userdata['user_id'] ) || $this->auth['auth_mod'] )
			{
				$pafiledb_template->assign_block_vars( 'use_comments.text.auth_delete', array(
					'L_COMMENT_DELETE' => $lang['Comment_delete'],
					'U_COMMENT_DELETE' => append_sid( pa_this_mxurl( "action=post_comment&cid=".$this->comments_row['comments_id']."&delete=do&item_id=".$this->item_id . '&cat_id=' . $this->cat_id )),
					'DELETE_IMG' => $phpbb_root_path . $images['icon_delpost'], 
				));
			}
			
		}
		
		if ( ( $this->auth['auth_post'] ) || $this->auth['auth_mod'] )
		{
			$pafiledb_template->assign_block_vars( 'use_comments.auth_post', array(
				'L_COMMENT_ADD' => $lang['Comment_add'],
				'U_COMMENT_POST' => append_sid( pa_this_mxurl( 'action=post_comment&item_id=' . $this->item_id . '&cat_id=' . $this->cat_id) ),
				'REPLY_IMG' => $images['pa_comment_post'],
			));
		}

		$num_of_replies = intval( $this->total_comments );
		$pagination = generate_pagination( pa_this_mxurl( $this->pagination_action . "&" . $this->pagination_target . $this->item_id . $page_num ), $num_of_replies, $this->pagination_num, $this->start ) . '&nbsp;';
		if ($num_of_replies > 0)
		{
			$pafiledb_template->assign_block_vars( 'use_comments.comments_pag', array(
				'PAGINATION' => $pagination,
				'PAGE_NUMBER' => sprintf( $lang['Page_of'], ( floor( $this->start / $this->pagination_num ) + 1 ), ceil( $num_of_replies / $this->pagination_num ) ),
				'L_GOTO_PAGE' => $lang['Goto_page'],				
			));	
		}
						
		$db->sql_freeresult( $result );
	}

	// ===================================================
	// Get all phpBB comments in the comments topic
	// ===================================================	
	function display_phpbb_comments( )
	{
		global $pafiledb_template, $pafiledb, $lang, $board_config, $phpEx, $pafiledb_config, $db, $userdata, $images;
		global $mx_root_path, $module_root_path, $phpbb_root_path, $is_block, $phpEx, $mx_request_vars;

		if ( empty($this->topic_id) || $this->topic_id < 0 )
		{
			mx_message_die( GENERAL_MESSAGE, 'no or bad topic id' );
		}

		//
		// Request vars
		//		
		$this->start = $mx_request_vars->get('start', MX_TYPE_INT, 0);
		$page_num = $mx_request_vars->get('page_num', MX_TYPE_INT, '');

		//
		// page number (only used for kb articles)
		//
		if ( !empty( $page_num ) )
		{
			$page_num = "&page_num=" . ( $page_num + 1 ) ;
		}
		else
		{
			$page_num = '';
		}

		//
		// Instatiate text tools
		//
		$mx_pa_text_tools = new mx_pa_text_tools();

		//
		// Includes
		//
		include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx); 

		$pafiledb_template->assign_block_vars( 'use_comments', array( 
			'L_COMMENTS' => $lang['Comments'],
		));

		//
		// Define censored word matches
		//
		$orig_word = array();
		$replacement_word = array();
		obtain_word_list( $orig_word, $replacement_word );

		//
		// Get all comments
		//
		$this->get_phpbb_comments();
	
		$ranksrow = array();
		$this->obtain_ranks( $ranksrow );
	
		while ( $this->comments_row = $db->sql_fetchrow( $result ) )
		{
			$poster_id = $this->comments_row['user_id'];
			$poster = ( $poster_id == ANONYMOUS ) ? $lang['Guest'] : $this->comments_row['username'];
			$time = create_date( $board_config['default_dateformat'], $this->comments_row['post_time'], $board_config['board_timezone'] );
			$poster_posts = ( $this->comments_row['user_id'] != ANONYMOUS ) ? $lang['Posts'] . ': ' . $this->comments_row['user_posts'] : '';
			$poster_from = ( $this->comments_row['user_from'] && $this->comments_row['user_id'] != ANONYMOUS ) ? $lang['Location'] . ': ' . $this->comments_row['user_from'] : '';
			$poster_joined = ( $this->comments_row['user_id'] != ANONYMOUS ) ? $lang['Joined'] . ': ' . create_date( $lang['DATE_FORMAT'], $this->comments_row['user_regdate'], $board_config['board_timezone'] ) : ''; 
			
			//
			// Handle anon users posting with usernames
			//
			if ( $poster_id == ANONYMOUS && $this->comments_row['post_username'] != '' )
			{
				$poster = $this->comments_row['post_username'];
				$poster_rank = $lang['Guest'];
			}
			$comments_title = ( $this->comments_row['post_subject'] != '' ) ? $this->comments_row['post_subject'] : '';
	
			$comments_text = $this->comments_row['post_text'];
			$bbcode_uid = $this->comments_row['bbcode_uid']; 
			
			//
			// If the board has HTML off but the post has HTML
			// on then we process it, else leave it alone
			//
			if ( !$board_config['allow_html'] )
			{
				if ( $user_sig != '' && $userdata['user_allowhtml'] )
				{
					$user_sig = preg_replace( '#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $user_sig );
				}
	
				if ( $this->comments_row['enable_html'] )
				{
					$comments_text = preg_replace( '#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $comments_text );
				}
			} 
			
			//
			// Remove Images and/or links
			//
			if (!$pafiledb_config['allow_comment_images'] || !$pafiledb_config['allow_comment_links'])
			{

				$comments_text = $mx_pa_text_tools->remove_images_links( $comments_text, $pafiledb_config['allow_comment_images'], $pafiledb_config['no_comment_image_message'], $pafiledb_config['allow_comment_links'], $pafiledb_config['no_comment_link_message'] );
			}
			
			//
			// Parse message and/or sig for BBCode if reqd
			//
			if ( $board_config['allow_bbcode'] )
			{
				if ( $user_sig != '' && $user_sig_bbcode_uid != '' )
				{
					$user_sig = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass( $user_sig, $user_sig_bbcode_uid ) : preg_replace( '/\:[0-9a-z\:]+\]/si', ']', $user_sig );
				}
	
				if ( $bbcode_uid != '' )
				{
					$comments_text = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass( $comments_text, $bbcode_uid ) : preg_replace( '/\:[0-9a-z\:]+\]/si', ']', $comments_text );
				}
			}
	
			if ( $user_sig != '' )
			{
				$user_sig = make_clickable( $user_sig );
			}
			$comments_text = make_clickable( $comments_text ); 
			
			//
			// Parse smilies
			//
			if ( $board_config['allow_smilies'] )
			{
				if ( $this->comments_row['user_allowsmile'] && $user_sig != '' )
				{
					$user_sig = mx_smilies_pass( $user_sig );
				}
	
				if ( $this->comments_row['enable_smilies'] )
				{
					$comments_text = mx_smilies_pass( $comments_text );
				}
			}
			$comments_text = str_replace( "\n", "\n<br />\n", $comments_text ); 
			
			//
			// Text formatting
			//
			if ( $pafiledb_config['max_comment_subject_chars'] > 0 )
			{
				$comments_title = $mx_pa_text_tools->truncate_text( $comments_title, $pafiledb_config['max_comment_subject_chars'], true );
			}
	
			if ( $pafiledb_config['max_comment_chars'] > 0 )
			{
				$comments_text = $mx_pa_text_tools->truncate_text( $comments_text, $pafiledb_config['max_comment_chars'], true );
			}		
			
			if ( $pafiledb_config['formatting_comment_truncate_links'] || $pafiledb_config['formatting_comment_image_resize'] > 0 || $pafiledb_config['formatting_comment_wordwrap'] ) 
			{
				$comments_text = $mx_pa_text_tools->decode( $comments_text, $pafiledb_config['formatting_comment_truncate_links'], intval($pafiledb_config['formatting_comment_image_resize']), $pafiledb_config['formatting_comment_wordwrap'] );
			}
						
			//
			// Editing information
			//
			if ( $this->comments_row['post_edit_count'] )
			{
				$l_edit_time_total = ( $this->comments_row['post_edit_count'] == 1 ) ? $lang['Edited_time_total'] : $lang['Edited_times_total'];
	
				$l_edited_by = '<br /><br />' . sprintf( $l_edit_time_total, $poster, create_date( $board_config['default_dateformat'], $this->comments_row['post_edit_time'], $board_config['board_timezone'] ), $this->comments_row['post_edit_count'] );
			}
			else
			{
				$l_edited_by = '';
			}

			$poster_avatar = '';
			
			if ( $this->comments_row['user_avatar_type'] && $poster_id != ANONYMOUS && $this->comments_row['user_allowavatar'] )
			{
				switch ( $this->comments_row['user_avatar_type'] )
				{
					case USER_AVATAR_UPLOAD:
						$poster_avatar = ( $board_config['allow_avatar_upload'] ) ? '<img src="' . $phpbb_root_path . $board_config['avatar_path'] . '/' . $this->comments_row['user_avatar'] . '" alt="" border="0" />' : '';
						break;
					case USER_AVATAR_REMOTE:
						$poster_avatar = ( $board_config['allow_avatar_remote'] ) ? '<img src="' . $this->comments_row['user_avatar'] . '" alt="" border="0" />' : '';
						break;
					case USER_AVATAR_GALLERY:
						$poster_avatar = ( $board_config['allow_avatar_local'] ) ? '<img src="' . $phpbb_root_path . $board_config['avatar_gallery_path'] . '/' . $this->comments_row['user_avatar'] . '" alt="" border="0" />' : '';
						break;
				}
			} 
			
			//
			// Generate ranks, set them to empty string initially.
			//
			$poster_rank = '';
			$rank_image = '';
			if ( $this->comments_row['user_id'] == ANONYMOUS )
			{
			}
			else if ( $this->comments_row['user_rank'] )
			{
				for( $j = 0; $j < count( $ranksrow ); $j++ )
				{
					if ( $this->comments_row['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'] )
					{
						$poster_rank = $ranksrow[$j]['rank_title'];
						$rank_image = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $phpbb_root_path . $ranksrow[$j]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
					}
				}
			}
			else
			{
				for( $j = 0; $j < count( $ranksrow ); $j++ )
				{
					if ( $this->comments_row['user_posts'] >= $ranksrow[$j]['rank_min'] && !$ranksrow[$j]['rank_special'] )
					{
						$poster_rank = $ranksrow[$j]['rank_title'];
						$rank_image = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $phpbb_root_path . $ranksrow[$j]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
					}
				}
			}
						
			$pafiledb_template->assign_block_vars( 'use_comments.text', array( 
				'L_POSTED' => $lang['Posted'],
				'L_COMMENT_SUBJECT' => $lang['Comment_subject'],
				'L_COMMENTS_NAME' => $lang['Name'],			
				'POSTER' => $poster,
				'ICON_MINIPOST_IMG' => $phpbb_root_path . $images['icon_minipost'],
				'ICON_SPACER' => $phpbb_root_path . "templates/subSilver/images/spacer.gif", 
				'POSTER_RANK' => $poster_rank,
				'RANK_IMAGE' => $rank_image,
				'POSTER_AVATAR' => $poster_avatar,
				'TITLE' => $comments_title,
				'TIME' => $time,
				'TEXT' => $comments_text 
			));

			if ( ( $this->auth['auth_edit'] && $this->comments_row['user_id'] == $userdata['user_id'] ) || $this->auth['auth_mod'] )
			{
				$pafiledb_template->assign_block_vars( 'use_comments.text.auth_edit', array(
					'L_COMMENT_EDIT' => $lang['Comment_edit'],
					'U_COMMENT_EDIT' => append_sid( pa_this_mxurl( 'action=post_comment&item_id=' . $this->item_id . '&cat_id=' . $this->cat_id . '&cid='.$this->comments_row['post_id'] ) ),
					'EDIT_IMG' => $phpbb_root_path . $images['icon_edit'], 
				));
			}
						
			if ( ( $this->auth['auth_delete'] && $this->comments_row['user_id'] == $userdata['user_id'] ) || $this->auth['auth_mod'] )
			{
				$pafiledb_template->assign_block_vars( 'use_comments.text.auth_delete', array(
					'L_COMMENT_DELETE' => $lang['Comment_delete'],
					'U_COMMENT_DELETE' => append_sid( pa_this_mxurl( "action=post_comment&cid=".$this->comments_row['post_id']."&delete=do&item_id=".$this->item_id . '&cat_id=' . $this->cat_id )),
					'DELETE_IMG' => $phpbb_root_path . $images['icon_delpost'], 
				));
			}
			
		}
		
		if ( ( $this->auth['auth_post'] ) || $this->auth['auth_mod'] )
		{
			$pafiledb_template->assign_block_vars( 'use_comments.auth_post', array(
				'L_COMMENT_ADD' => $lang['Comment_add'],
				'U_COMMENT_POST' => append_sid( pa_this_mxurl( 'action=post_comment&item_id=' . $this->item_id . '&cat_id=' . $this->cat_id ) ),
				'REPLY_IMG' => $images['pa_comment_post'],
			));
		}
			
		$num_of_replies = intval( $this->total_comments );
		$pagination = generate_pagination( pa_this_mxurl( $this->pagination_action . "&" . $this->pagination_target . $this->item_id . $page_num ), $num_of_replies, $this->pagination_num, $this->start ) . '&nbsp;';

		if ($num_of_replies > 0)
		{
			$pafiledb_template->assign_block_vars( 'use_comments.comments_pag', array(
				'PAGINATION' => $pagination,
				'PAGE_NUMBER' => sprintf( $lang['Page_of'], ( floor( $this->start / $this->pagination_num ) + 1 ), ceil( $num_of_replies / $this->pagination_num ) ),
				'L_GOTO_PAGE' => $lang['Goto_page'],				
			));	
		}
						
		$db->sql_freeresult( $result );
	}
		
	function obtain_ranks( &$ranks )
	{
		global $db, $pafiledb_cache;

		if ( $pafiledb_cache->exists( 'ranks' ) )
		{
			$ranks = $pafiledb_cache->get( 'ranks' );
		}
		else
		{
			$sql = "SELECT *
				FROM " . RANKS_TABLE . "
				ORDER BY rank_special, rank_min";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Could not obtain ranks information.", '', __LINE__, __FILE__, $sql );
			}

			$ranks = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$ranks[] = $row;
			}

			$db->sql_freeresult( $result );
			$pafiledb_cache->put( 'ranks', $ranks );
		}
	}

	function get_internal_comments()
	{
		global $db, $pafiledb_template;
		
		$sql = "SELECT COUNT(file_id) AS number
			FROM " . PA_COMMENTS_TABLE . " 
			WHERE file_id = " . $this->item_id;
		
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Could not obtain number of comments", '', __LINE__, __FILE__, $sql);
		}

		$this->total_comments = ( $row = $db->sql_fetchrow($result) ) ? intval($row['number']) : 0;
				
		$sql = 'SELECT c.*, u.*
			FROM ' . PA_COMMENTS_TABLE . ' AS c 
				LEFT JOIN ' . USERS_TABLE . " AS u ON c.poster_id = u.user_id
			WHERE c.file_id = '" . $this->item_id . "'
			ORDER BY c.comments_id DESC";

		if ( $this->start > -1 && $this->pagination_num > 0 )
		{
			$sql .= " LIMIT $this->start, $this->pagination_num ";
		}
				
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt select comments', '', __LINE__, __FILE__, $sql );
		}

		if ( !( $comment_number = $db->sql_numrows( $result ) ) )
		{
			$pafiledb_template->assign_block_vars( 'use_comments.no_comments', array( 
				'L_NO_COMMENTS' => $lang['No_comments'],
			));
		}			
	}

	function get_phpbb_comments()
	{
		global $db, $pafiledb_template;

		$sql = "SELECT COUNT(post_id) AS number
			FROM " . POSTS_TABLE . " 
			WHERE topic_id = " . $this->topic_id;
		
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Could not obtain number of comments", '', __LINE__, __FILE__, $sql);
		}

		$this->total_comments = ( $row = $db->sql_fetchrow($result) ) ? intval($row['number']) : 0;
				
		//
		// Go ahead and pull all data for this topic
		//
		$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, u.user_allowsmile, p.*,  pt.post_text, pt.post_subject, pt.bbcode_uid
			FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
			WHERE p.topic_id = '" . $this->topic_id . "'
				AND pt.post_id = p.post_id
				AND u.user_id = p.poster_id
				ORDER BY p.post_id DESC";
	
		if ( $this->start > -1 && $this->pagination_num > 0 )
		{
			$sql .= " LIMIT $this->start, $this->pagination_num ";
		}
	
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, "Could not obtain post/user information.", '', __LINE__, __FILE__, $sql );
		}	

		if ( !( $comment_number = $db->sql_numrows( $result ) ) )
		{
			$pafiledb_template->assign_block_vars( 'use_comments.no_comments', array( 
				'L_NO_COMMENTS' => $lang['No_comments'],
			));
		}				
	}
	
	/*
	 *   Description    :   This functions is used to insert a post into your phpbb forums. 
	 *                      It handles all the related bits like updating post counts, 
	 *                      indexing search words, etc.
	 *                      The post is inserted for a specific user, so you will have to 
	 *                      already have a user setup which you want to use with it.
	 *
	 *                      If you're using the POST method to input data then you should call addslashes on
	 *                      your subject and message before calling insert_post - see test_insert_post for example.
	 *
	 *   Parameters     :   $subject            - the subject of the post (required)
	 *                      $message            - the message that will form the body of the post (required)
	 *                      $forum_id           - the forum the post is to be added to (required)
	 *                      $user_id            - the id of the user for the post (required)
	 *                      $user_name          - the username of the user for the post (required)
	 *                      $user_attach_sig    - should the user's signature be attached to the post (required)
	 *
	 *   Options Params :   $topic_id           - if topic_id is empty we 'newtopic', else
	 *                      $post_id         	- if post_id is passed then we 'editpost', if not we reply
	 *                      $subject_update_first   - if (this and next) not empty first topic post is updated 
	 *                      $message_update_first   - if (this and previous) not empty first topic post is updated
	 *
	 *                      $topic_type         - defaults to POST_NORMAL, can also be POST_STICKY, POST_ANNOUNCE or POST_GLOBAL_ANNOUNCE
	 *                      $do_notification    - should users be notified of new posts (only valid for replies)
	 *                      $notify_user        - should the 'posting' user be signed up for notifications of this topic
	 *                      $current_time       - should the current time be used, if not then you should supply a posting time
	 *                      $error_die_function - can be used to supply a custom error function.
	 *                      $html_on = false    - should html be allowed (parsed) in the post text.
	 *                      $bbcode_on = true   - should bbcode be allowed (parsed) in the post text.
	 *                      $smilies_on = true  - should smilies be allowed (parsed) in the post text.
	 *
	 *   Returns        :   If the function succeeds without an error it will return an array containing
	 *                      the post id and the topic id of the new post. Any error along the way will result in either
	 *                      the normal phpbb message_die function being called or a custom die function determined
	 *                      by the $error_die_function parameter.
	 */
	 
	//
	// insert post for site updates, by netclectic - Adrian Cockburn & Jon Ohlsson
	//
	function insert_phpbb_post( 
		$subject,	
	    $message, 
	    $forum_id, 
	    $user_id, 
	    $user_name, 
	    $user_attach_sig, 
	    $topic_id = '', 
	    $post_id = '',
	    $subject_update_first = '',	     
	    $message_update_first = '',	
	    $bbcode_uid = '',     
	    $topic_type = POST_NORMAL, 
	    $do_notification = false, 
	    $notify_user = false, 
	    $current_time = 0, 
	    $error_die_function = '', 
	    $html_on = 0, 
	    $bbcode_on = 1, 
	    $smilies_on = 1)
	{
		global $db, $phpbb_root_path, $phpEx, $board_config, $user_ip, $kb_config, $lang, $userdata; 

		//
		// Includes
		//
		include_once($phpbb_root_path . 'includes/functions_search.'.$phpEx); 
		
		//
		// initialise some variables
		//
		$topic_vote = 0;
		$poll_title = '';
		$poll_options = '';
		$poll_length = '';
	
	    //$bbcode_uid = ($bbcode_on) ? make_bbcode_uid() : ''; 
	    $error_die_function = ($error_die_function == '') ? "mx_message_die" : $error_die_function;
	    $current_time = ($current_time == 0) ? time() : $current_time;
	    
	    $subject = addslashes(trim($subject));
	    //
	    // parse the message and the subject (belt & braces :)
	    //
	    /*
	    $subject = addslashes(unprepare_message(trim($subject)));
	    
	    $message = addslashes(unprepare_message($message));
	    $message = prepare_message(trim($message), $html_on, $bbcode_on, $smilies_on, $bbcode_uid);
	    
	    if (!empty($message_update_text))
	    {
		    $message_update_text = addslashes(unprepare_message($message_update_text));
		    $message_update_text = prepare_message(trim($message_update_text), $html_on, $bbcode_on, $smilies_on, $bbcode_uid);
	    }
	    */
	    
	    $username = addslashes(unprepare_message(trim($user_name)));
	    $username = phpbb_clean_username( $username );  
		 
	    //
	    // We always require the forum_id
	    //
	    if ( empty( $topic_id ) )
	    {
	    	$error_die_function( GENERAL_ERROR, 'no forum id');
	    }	
	    	    
		//
		// Validate vars and find correct $mode
		//
	    if ( empty( $topic_id ) )
	    {
		    //
			// If $topic_id is empty we assume you want a new topic
			//
			$mode = 'newtopic';
	    }
		else if ( empty($post_id) )
		{
			//
			// If $post_id is empty we assume you want a 'reply'
			//
			$mode = 'reply';	
				
		}
		else 
		{
			//
			// So this must be a 'editpost'
			// but is this first topic post or last post
			//
			$sql = "SELECT topic_first_post_id, topic_last_post_id  
		       		FROM " . TOPICS_TABLE . " 
		      		WHERE topic_id = '$topic_id'";
				
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Could not obtain first_post_id data", '', __LINE__, __FILE__, $sql );
			}
				
			$row_tmp = $db->sql_fetchrow( $result );
			$first_post_id = $row_tmp['topic_first_post_id'];	
			$last_post_id = $row_tmp['topic_last_post_id'];	

			$is_first_post = ($first_post_id == $post_id) ? true : false;
			$is_last_post = ($last_post_id == $post_id) ? true : false;
			
			$mode = 'editpost';
		}
		
		//
		// Now we have validated we have correct $mode and all required vars are set :-)
		// Lets start
		//
		
		//
		// New topic or updated first topic post
		//
		if ( $mode == 'newtopic' || ($mode == 'editpost' && $is_first_post) )
		{
			$mode = 'newtopic';
			
			if ( $mode == 'newtopic' )
			{	
				//
				// Inserting new topic
				//						
				$sql = "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote) VALUES ('$subject', " . $user_id . ", $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_vote)";
			}
			else 
			{
				//
				// Updating topic
				//
				$sql = "UPDATE " . TOPICS_TABLE . " SET topic_title = '$subject', topic_type = $topic_type WHERE topic_id = $topic_id";
			}
			
			if ( !$db->sql_query( $sql, BEGIN_TRANSACTION ) )
			{
				$error_die_function( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
			}
			
			$topic_id = $mode == 'newtopic' ? $db->sql_nextid() : $topic_id;
		} 

		//
		// remove search words for our edited post
		//		
		if ($mode == 'editpost')
		{
			remove_search_post($post_id);
		}
			
		//
		// insert/update the post details using the topic id
		//
		if ( $mode == 'newtopic' || $mode == 'reply' )
		{
			$sql = "INSERT INTO " . POSTS_TABLE . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, enable_bbcode, enable_html, enable_smilies, enable_sig) VALUES ($topic_id, $forum_id, " . $user_id . ", '$username', $current_time, '$user_ip', $bbcode_on, $html_on, $smilies_on, $user_attach_sig)";
		}
		else 
		{
			$edited_sql = !$is_last_post ? ", post_edit_time = $current_time, post_edit_count = post_edit_count + 1 " : "";
			$sql = "UPDATE " . POSTS_TABLE . " SET post_username = '$username', enable_bbcode = $bbcode_on, enable_html = $html_on, enable_smilies = $smilies_on, enable_sig = $user_attach_sig" . $edited_sql . " WHERE post_id = $post_id";
		}
			
		if ( !$db->sql_query( $sql, BEGIN_TRANSACTION ) )
		{
			$error_die_function( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
		}
			
		$post_id = $mode == 'newtopic' || $mode == 'reply' ? $db->sql_nextid() : $post_id; 
			
		//
		// insert the actual post text for our new post
		//
		if ( $mode == 'newtopic' || $mode == 'reply' )
		{			
			$sql = "INSERT INTO " . POSTS_TEXT_TABLE . " (post_id, post_subject, bbcode_uid, post_text) VALUES ($post_id, '$subject', '$bbcode_uid', '$message')";
		}
		else 
		{			
			$sql = "UPDATE " . POSTS_TEXT_TABLE . " SET post_text = '$message',  bbcode_uid = '$bbcode_uid', post_subject = '$subject' WHERE post_id = $post_id";
		}
			
		if ( !$db->sql_query( $sql, BEGIN_TRANSACTION ) )
		{
			$error_die_function( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
		} 
			
		//
		// update the post counts etc.
		//
		$newpostsql = ( $mode == 'newtopic' ) ? ',forum_topics = forum_topics + 1' : '';
			
		$sql = "UPDATE " . FORUMS_TABLE . " SET 
	               forum_posts = forum_posts + 1,
	               forum_last_post_id = $post_id
	               $newpostsql 	
	           WHERE forum_id = $forum_id";
			
		if ( !$db->sql_query( $sql, BEGIN_TRANSACTION ) )
		{
			$error_die_function( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
		} 
			
		//
		// update the first / last post ids for the topic
		//
		$first_post_sql = ( $mode == 'newtopic' ) ? ", topic_first_post_id = $post_id  " : ' , topic_replies=topic_replies+1';
			
		$sql = "UPDATE " . TOPICS_TABLE . " SET 
	               topic_last_post_id = $post_id 
	               $first_post_sql
	           WHERE topic_id = $topic_id";
			
		if ( !$db->sql_query( $sql, BEGIN_TRANSACTION ) )
		{
			$error_die_function( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
		} 
			
		//
		// update the user's post count and commit the transaction
		//
		$sql = "UPDATE " . USERS_TABLE . " SET 
	               user_posts = user_posts + 1
	           WHERE user_id = $user_id";
			
		if ( !$db->sql_query( $sql, END_TRANSACTION ) )
		{
			$error_die_function( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
		} 
			
		//
		// add the search words for our new/edited post
		//
		add_search_words('single', $post_id, stripslashes($message), stripslashes($subject));
			
		//
		// update first topic post
		//
		if (!empty($topic_id) && !empty($subject_update_first) && !empty($message_update_first) )
		{
			if (empty($first_post_id))
			{
				$sql = "SELECT topic_first_post_id  
			       		FROM " . TOPICS_TABLE . " 
			      		WHERE topic_id = '$topic_id'";
				
				if ( !( $result = $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, "Could not obtain first_post_id data", '', __LINE__, __FILE__, $sql );
				}
				
				$row_tmp = $db->sql_fetchrow( $result );
				$first_post_id = $row_tmp['topic_first_post_id'];
			}
			
			//
			// Remove search words
			//
			remove_search_post($first_post_id);
			
			$sql = "UPDATE " . TOPICS_TABLE . " SET 
		                topic_title = '$subject_update_first'
						WHERE topic_id = '$topic_id'";
			
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
			} 
				
			$sql = "UPDATE " . POSTS_TEXT_TABLE . " SET 
		                post_subject = '$subject_update_first', 
						bbcode_uid = '$bbcode_uid', 
						post_text = '$message_update_first' 
						WHERE post_id = '$first_post_id'";
			
			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql );
			} 
			
			//
			// Add search words
			//
			add_search_words('single', $first_post_id, stripslashes($message_update_first), stripslashes($subject_update_first));			
		}
		
		//
		// do we need to do user notification
		//
	    if ( ($mode != 'newtopic') && $do_notification )
		{
			$post_data = array();
			user_notification( $mode, $post_data, $subject, $forum_id, $topic_id, $post_id, $notify_user );
		} 
				
		//
		// if all is well then return the id of our new post
		//
		return array( 'post_id' => $post_id, 'topic_id' => $topic_id, 'notify' => $message_tmp );
	}	

	//
	// Delete a post/poll
	//
	function delete_phpbb_post($forum_id, $topic_id, $post_id)
	{
		global $board_config, $lang, $db, $phpbb_root_path, $phpEx;
		global $userdata, $user_ip;
	
		include($phpbb_root_path . 'includes/functions_search.'.$phpEx);

		$forum_update_sql = "forum_posts = forum_posts - 1";
		$topic_update_sql = '';
			
		//
		// is this first topic post or last topic post
		//
		$sql = "SELECT topic_first_post_id, topic_last_post_id  
		   		FROM " . TOPICS_TABLE . " 
		   		WHERE topic_id = '$topic_id'";
				
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, "Could not obtain first_post_id data", '', __LINE__, __FILE__, $sql );
		}
				
		$row_tmp = $db->sql_fetchrow( $result );
		$first_post_id = $row_tmp['topic_first_post_id'];	
		$last_post_id = $row_tmp['topic_last_post_id'];	

		$is_first_post = ($first_post_id == $post_id) ? true : false;
		$is_last_post = ($last_post_id == $post_id) ? true : false;
					
		//
		// Start delete
		//
		$sql = "DELETE FROM " . POSTS_TABLE . " 
			WHERE post_id = $post_id";
		
		if (!$db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}
	
		$sql = "DELETE FROM " . POSTS_TEXT_TABLE . " 
			WHERE post_id = $post_id";
		
		if (!$db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
		}
	
		if ($is_last_post && $is_first_post)
		{
			$sql = "DELETE FROM " . TOPICS_TABLE . " 
				WHERE topic_id = $topic_id 
					OR topic_moved_id = $topic_id";

			if (!$db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
			}
	
			$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
				WHERE topic_id = $topic_id";
				
			if (!$db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
			}
		}
	
		remove_search_post($post_id);
		
		//
		// Update stats
		//
		if ($is_last_post)
		{
			if ($is_first_post)
			{
				$forum_update_sql .= ', forum_topics = forum_topics - 1';
			}
			else
			{
				$topic_update_sql .= 'topic_replies = topic_replies - 1';

				$sql = "SELECT MAX(post_id) AS last_post_id
					FROM " . POSTS_TABLE . " 
					WHERE topic_id = $topic_id";
				
				if (!($result = $db->sql_query($sql)))
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ($row = $db->sql_fetchrow($result))
				{
					$topic_update_sql .= ', topic_last_post_id = ' . $row['last_post_id'];
				}
			}

			/*
			if ($post_data['last_topic'])
			{
			*/
				$sql = "SELECT MAX(post_id) AS last_post_id
					FROM " . POSTS_TABLE . " 
					WHERE forum_id = $forum_id"; 
				
				if (!($result = $db->sql_query($sql)))
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ($row = $db->sql_fetchrow($result))
				{
					$forum_update_sql .= ($row['last_post_id']) ? ', forum_last_post_id = ' . $row['last_post_id'] : ', forum_last_post_id = 0';
				}
			/*
			}
			*/
							
		}
		else if ($is_first_post) 
		{
			$sql = "SELECT MIN(post_id) AS first_post_id
				FROM " . POSTS_TABLE . " 
				WHERE topic_id = $topic_id";
			
			if (!($result = $db->sql_query($sql)))
			{
				message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
			}

			if ($row = $db->sql_fetchrow($result))
			{
				$topic_update_sql .= 'topic_replies = topic_replies - 1, topic_first_post_id = ' . $row['first_post_id'];
			}
		}
		else
		{
			$topic_update_sql .= 'topic_replies = topic_replies - 1';
		}	
		
		$sql = "UPDATE " . FORUMS_TABLE . " SET 
			$forum_update_sql 
			WHERE forum_id = $forum_id";
		
		if (!$db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
	
		if ($topic_update_sql != '')
		{
			$sql = "UPDATE " . TOPICS_TABLE . " SET 
				$topic_update_sql 
				WHERE topic_id = $topic_id";
			
			if (!$db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
			}
		}
	
		$sql = "UPDATE " . USERS_TABLE . "
			SET user_posts = user_posts - 1 
			WHERE user_id = $user_id";
			
		if (!$db->sql_query($sql, END_TRANSACTION))
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
		
	}	
	
	function post( $mode, $cid, $title = '', $comments_text = '', $user_id = '', $username = '', $user_attach_sig = '', $title_first = '', $comments_text_first = '', $comment_bbcode_uid = '')
	{
		switch ($mode)
		{
			case 'delete':
				$this->delete_phpbb_post($this->forum_id, $this->topic_id, $cid );
			break;
			
			case 'insert':
				$this->insert_phpbb_post( $title, $comments_text, $this->forum_id, $user_id, $username, $user_attach_sig, $this->topic_id, '',  $title_first, $comments_text_first, $comment_bbcode_uid );
			break;
			
			case 'update':
				$this->insert_phpbb_post( $title, $comments_text, $this->forum_id, $user_id, $username, $user_attach_sig, $this->topic_id, $cid, $title_first, $comments_text_first, $comment_bbcode_uid );
			break;
			
			default:
				mx_message_die(GENERAL_ERROR, 'bad post mode');
			
			
		}
	}
}

?>