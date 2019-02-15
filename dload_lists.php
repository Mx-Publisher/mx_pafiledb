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
 *    $Id: dload_lists.php,v 1.14 2005/12/08 15:15:11 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */

function this_pa_mxurl( $args = '', $force_standalone_mode = false, $page_id = 1 )
{
	global $mx_root_path, $module_root_path, $phpEx, $is_block;

	if ( $force_standalone_mode || !$is_block )
	{
		$mxurl = $module_root_path . 'dload.' . $phpEx . ( $args == '' ? '' : '?' . $args );
	}
	else
	{
		$mxurl = $mx_root_path . 'index.' . $phpEx;
		if ( is_numeric( $page_id ) )
		{
			$mxurl .= '?page=' . $page_id . ( $args == '' ? '' : '&amp;' . $args );
		}
		else
		{
			$mxurl .= ( $args == '' ? '' : '?' . $args );
		}
	}
	return $mxurl;
}

function paImageRating( $rating )
{
	global $db, $album_sp_config, $module_root_path;

	if ( !$rating )
		return( "<i>Not Rated</i>" );
	else
		return ( round( $rating, 2 ) );
}

// MX
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
		$sql = "SELECT * FROM " . BLOCK_TABLE . "  WHERE block_title = 'PafileDB_toplist' LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, "Could not query PafileDB_toplist module information", "", __LINE__, __FILE__, $sql );
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

define( 'MXBB_MODULE', true );
define( 'MXBB_27x', file_exists( $mx_root_path . 'mx_login.php' ) );

include_once( $module_root_path . 'pafiledb/includes/pafiledb_constants.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/functions_auth.' . $phpEx );
include_once( $module_root_path . 'pafiledb/includes/functions_pafiledb.' . $phpEx );

//
// Read block Configuration
//
$album_sp_config['img_rows'] = $mx_block->get_parameters( 'num_of_rows' );
$album_sp_config['img_cols'] = $mx_block->get_parameters( 'num_of_cols' );

$album_sp_config['disp_late'] = $mx_block->get_parameters( 'display_latest_posts' ) == 'TRUE' ? 1 : 0;
$album_sp_config['disp_high'] = $mx_block->get_parameters( 'display_top_ranked' ) == 'TRUE' ? 1 : 0;
$album_sp_config['disp_rand'] = $mx_block->get_parameters( 'display_random_posts' ) == 'TRUE' ? 1 : 0;
$album_sp_config['disp_most'] = $mx_block->get_parameters( 'display_most_posts' ) == 'TRUE' ? 1 : 0;

$album_config['rate'] = 1;
$album_config['fullpic_popup'] = 0;
$album_config['comment'] = 1;

$album_sp_config['rate_type'] = 1;

//
// Get pafiledb target block
//
$pafiledb_block_id = $mx_block->get_parameters( 'target_block' );
$pafiledb_page_id = $pafiledb_block_id > 0 ? get_page_id( $pafiledb_block_id ) : get_page_id( 'dload.php', true );

/*
+----------------------------------------------------------
| Build Categories Index
+----------------------------------------------------------
*/

$sql = "SELECT c.*, COUNT(p.file_id) AS count
 		FROM " . PA_CATEGORY_TABLE . " AS c
 			LEFT JOIN " . PA_FILES_TABLE . " AS p ON c.cat_id = p.file_catid
 		WHERE cat_id <> 0
 		GROUP BY cat_id
 		ORDER BY cat_order ASC";
if ( !( $result = $db->sql_query( $sql ) ) )
{
	mx_message_die( GENERAL_ERROR, 'Could not query categories list', '', __LINE__, __FILE__, $sql );
}

$catrows = array();

$pafiledb = new pafiledb_public();
$pafiledb->init();

while ( $row = $db->sql_fetchrow( $result ) )
{
	// $album_user_access = album_user_access($row['cat_id'], $row, 1, 0, 0, 0, 0, 0); // VIEW
	if ( $pafiledb->auth[$row['cat_id']]['auth_view'] )
	{
		$catrows[] = $row;
	}
}

$allowed_cat = ''; // For Recent Public Pics below 
// $catrows now stores all categories which this user can view. Dump them out!
for ( $i = 0; $i < count( $catrows ); $i++ )
{ 
	// --------------------------------
	// Build allowed category-list (for recent pics after here)
	// --------------------------------
	$allowed_cat .= ( $allowed_cat == '' ) ? $catrows[$i]['cat_id'] : ',' . $catrows[$i]['cat_id'];
}
// END of Categories Index
/*
+----------------------------------------------------------
| Recent Public Pics
+----------------------------------------------------------
*/
if ( $album_sp_config['disp_late'] == 1 )
{
	if ( $allowed_cat != '' )
	{
		$sql = "SELECT p.file_ssurl, p.file_id, p.file_name, p.file_desc, p.user_id, p.poster_ip, p.file_creator, p.file_time, p.file_update_time, p.file_catid, p.file_dls, u.user_id, u.username, r.votes_file, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comments_id) AS comments
				FROM " . PA_FILES_TABLE . " AS p
					LEFT JOIN " . USERS_TABLE . " AS u ON p.user_id = u.user_id
					LEFT JOIN " . PA_CATEGORY_TABLE . " AS ct ON p.file_catid = ct.cat_id
					LEFT JOIN " . PA_VOTES_TABLE . " AS r ON p.file_id = r.votes_file
					LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON p.file_id = c.file_id
				WHERE p.file_catid IN ($allowed_cat) AND p.file_approved = 1 
				GROUP BY p.file_id
				ORDER BY file_time DESC
				LIMIT " . $album_sp_config['img_cols'] * $album_sp_config['img_rows'];
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not query recent pics information', '', __LINE__, __FILE__, $sql );
		}

		$recentrow = array();

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$recentrow[] = $row;
		}

		$template->assign_block_vars( 'recent_pics_block', array() );

		if ( count( $recentrow ) > 0 )
		{
			for ( $i = 0; $i < count( $recentrow ); $i += $album_sp_config['img_cols'] )
			{
				$template->assign_block_vars( 'recent_pics_block.recent_pics', array() );

				for ( $j = $i; $j < ( $i + $album_sp_config['img_cols'] ); $j++ )
				{
					if ( $j >= count( $recentrow ) )
					{
						break;
					}
					$file_screenshot_url = trim( $recentrow[$j]['file_ssurl'] );
					$template->assign_block_vars( 'recent_pics_block.recent_pics.recent_col', array( 
							// 'U_PIC' => ($album_config['fullpic_popup']) ? append_sid(this_smartor_mxurl("smartor_mode=album_pic&pic_id=". $recentrow[$j]['pic_id'])) : append_sid(this_smartor_mxurl("smartor_mode=album_showpage&pic_id=". $recentrow[$j]['pic_id'])),
							// 'THUMBNAIL' => append_sid(this_smartor_mxurl("smartor_mode=album_thumbnail&pic_id=". $recentrow[$j]['pic_id'], TRUE)),
							// 'DESC' => $recentrow[$j]['pic_desc']
							'SS' => ( !empty( $file_screenshot_url ) ) ? '<hr><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $recentrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '"><img src="' . $file_screenshot_url . '" width="100" border="0"></a><br /><span class="genmed"><i><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $recentrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $recentrow[$j]['file_name'] . '</a></i></span>' : '' 
							) 
						);

					if ( ( $recentrow[$j]['user_id'] == ALBUM_GUEST ) or ( $recentrow[$j]['username'] == '' ) )
					{
						$recent_poster = ( $recentrow[$j]['file_creator'] == '' ) ? $lang['Guest'] : $recentrow[$j]['file_creator'];
					}
					else
					{
						$recent_poster = '<a href="' . append_sid( PHPBB_URL . "profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '=' . $recentrow[$j]['user_id'] ) . '">' . $recentrow[$j]['username'] . '</a>';
					}

					$rating_image = paImageRating( $recentrow[$j]['rating'] );

					$template->assign_block_vars( 'recent_pics_block.recent_pics.recent_detail', array( 'TITLE' => ( empty( $file_screenshot_url ) ) ? '<b>' . $lang['File_Title'] . ': <a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $recentrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $recentrow[$j]['file_name'] . '</a></b><br />' : '',
							'DESC' => $recentrow[$j]['file_desc'],
							'POSTER' => $recent_poster,
							'TIME' => create_date( $board_config['default_dateformat'], $recentrow[$j]['file_time'], $board_config['board_timezone'] ),
							'UPDATED' => create_date( $board_config['default_dateformat'], $recentrow[$j]['file_update_time'], $board_config['board_timezone'] ),

							'VIEW' => $recentrow[$j]['file_dls'],

							'RATING' => ( $album_config['rate'] == 1 ) ? ( $lang['Rating'] . ': ' . $rating_image . ',&nbsp;' ) : '',
							'COMMENTS' => ( $album_config['comment'] == 1 ) ? ( '<a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $recentrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $lang['Comments'] . '</a>: ' . $recentrow[$j]['comments'] . '<br />' ) : ''
							// 'IP' => ($userdata['user_level'] == ADMIN) ? $lang['IP_Address'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . decode_ip($recentrow[$j]['pic_user_ip']) . '" target="_blank">' . decode_ip($recentrow[$j]['pic_user_ip']) .'</a><br />' : '' 
							) 
						);
				}
			}
		}
		else
		{ 
			
			// No Pics Found
			
			$template->assign_block_vars( 'recent_pics_block.no_pics', array() );
		}
	}
	else
	{ 
		
		// No Cats Found
		
		$template->assign_block_vars( 'recent_pics_block.no_pics', array() );
	}
}

/*
+----------------------------------------------------------
| Most downloaded/viewed
+----------------------------------------------------------
*/
if ( $album_sp_config['disp_most'] == 1 )
{
	if ( $allowed_cat != '' )
	{
		$sql = "SELECT p.file_ssurl, p.file_id, p.file_name, p.file_desc, p.user_id, p.poster_ip, p.file_creator, p.file_time, p.file_update_time, p.file_catid, p.file_dls, u.user_id, u.username, r.votes_file, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comments_id) AS comments
				FROM " . PA_FILES_TABLE . " AS p
					LEFT JOIN " . USERS_TABLE . " AS u ON p.user_id = u.user_id
					LEFT JOIN " . PA_CATEGORY_TABLE . " AS ct ON p.file_catid = ct.cat_id
					LEFT JOIN " . PA_VOTES_TABLE . " AS r ON p.file_id = r.votes_file
					LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON p.file_id = c.file_id
				WHERE p.file_catid IN ($allowed_cat) AND p.file_approved = 1 
				GROUP BY p.file_id
				ORDER BY file_dls DESC
				LIMIT " . $album_sp_config['img_cols'] * $album_sp_config['img_rows'];
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not query recent pics information', '', __LINE__, __FILE__, $sql );
		}

		$mostrow = array();

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$mostrow[] = $row;
		}

		$template->assign_block_vars( 'most_pics_block', array() );

		if ( count( $mostrow ) > 0 )
		{
			for ( $i = 0; $i < count( $mostrow ); $i += $album_sp_config['img_cols'] )
			{
				$template->assign_block_vars( 'most_pics_block.most_pics', array() );

				for ( $j = $i; $j < ( $i + $album_sp_config['img_cols'] ); $j++ )
				{
					if ( $j >= count( $mostrow ) )
					{
						break;
					}
					$file_screenshot_url = trim( $mostrow[$j]['file_ssurl'] );
					$template->assign_block_vars( 'most_pics_block.most_pics.most_col', array( 
							// 'U_PIC' => ($album_config['fullpic_popup']) ? append_sid(this_smartor_mxurl("smartor_mode=album_pic&pic_id=". $recentrow[$j]['pic_id'])) : append_sid(this_smartor_mxurl("smartor_mode=album_showpage&pic_id=". $recentrow[$j]['pic_id'])),
							// 'THUMBNAIL' => append_sid(this_smartor_mxurl("smartor_mode=album_thumbnail&pic_id=". $recentrow[$j]['pic_id'], TRUE)),
							// 'DESC' => $recentrow[$j]['pic_desc']
							'SS' => ( !empty( $file_screenshot_url ) ) ? '<hr><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $mostrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '"><img src="' . $file_screenshot_url . '" width="100" border="0"></a><br /><span class="genmed"><i><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $mostrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $mostrow[$j]['file_name'] . '</a></i></span>' : '' 
							) 
						);

					if ( ( $mostrow[$j]['user_id'] == ALBUM_GUEST ) or ( $mostrow[$j]['username'] == '' ) )
					{
						$most_poster = ( $mostrow[$j]['file_creator'] == '' ) ? $lang['Guest'] : $mostrow[$j]['file_creator'];
					}
					else
					{
						$most_poster = '<a href="' . append_sid( PHPBB_URL . "profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '=' . $mostrow[$j]['user_id'] ) . '">' . $mostrow[$j]['username'] . '</a>';
					}

					$rating_image = paImageRating( $mostrow[$j]['rating'] );

					$template->assign_block_vars( 'most_pics_block.most_pics.most_detail', array( 'TITLE' => ( empty( $file_screenshot_url ) ) ? '<b>' . $lang['File_Title'] . ': <a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $mostrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $mostrow[$j]['file_name'] . '</a></b><br />' : '',
							'DESC' => $mostrow[$j]['file_desc'],
							'POSTER' => $most_poster,
							'TIME' => create_date( $board_config['default_dateformat'], $mostrow[$j]['file_time'], $board_config['board_timezone'] ),
							'UPDATED' => create_date( $board_config['default_dateformat'], $mostrow[$j]['file_update_time'], $board_config['board_timezone'] ),

							'VIEW' => $mostrow[$j]['file_dls'],

							'RATING' => ( $album_config['rate'] == 1 ) ? ( $lang['Rating'] . ': ' . $rating_image . ',&nbsp;' ) : '',
							'COMMENTS' => ( $album_config['comment'] == 1 ) ? ( '<a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $mostrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $lang['Comments'] . '</a>: ' . $mostrow[$j]['comments'] . '<br />' ) : ''
							// 'IP' => ($userdata['user_level'] == ADMIN) ? $lang['IP_Address'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . decode_ip($recentrow[$j]['pic_user_ip']) . '" target="_blank">' . decode_ip($recentrow[$j]['pic_user_ip']) .'</a><br />' : '' 
							) 
						);
				}
			}
		}
		else
		{ 
			
			// No Pics Found
			
			$template->assign_block_vars( 'most_pics_block.no_pics', array() );
		}
	}
	else
	{ 
		
		// No Cats Found
		
		$template->assign_block_vars( 'most_pics_block.no_pics', array() );
	}
}

/* 
+---------------------------------------------------------- 
| Highest Rated Pics 
| by MarkFulton.com 
+---------------------------------------------------------- 
*/
if ( $album_sp_config['disp_high'] == 1 )
{
	if ( $allowed_cat != '' )
	{
		$sql = "SELECT p.file_ssurl, p.file_id, p.file_name, p.file_desc, p.user_id, p.poster_ip, p.file_creator, p.file_time, p.file_update_time, p.file_catid, p.file_dls, u.user_id, u.username, r.votes_file, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comments_id) AS comments
				FROM " . PA_FILES_TABLE . " AS p
					LEFT JOIN " . USERS_TABLE . " AS u ON p.user_id = u.user_id
					LEFT JOIN " . PA_CATEGORY_TABLE . " AS ct ON p.file_catid = ct.cat_id
					LEFT JOIN " . PA_VOTES_TABLE . " AS r ON p.file_id = r.votes_file
					LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON p.file_id = c.file_id
				WHERE p.file_catid IN ($allowed_cat) AND p.file_approved = 1 
				GROUP BY p.file_id
	         ORDER BY rating DESC 
	         LIMIT " . $album_sp_config['img_cols'] * $album_sp_config['img_rows'];
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not query highest rated pics information', '', __LINE__, __FILE__, $sql );
		}

		$highestrow = array();

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$highestrow[] = $row;
		}

		$template->assign_block_vars( 'highest_pics_block', array() );

		if ( count( $highestrow ) > 0 )
		{
			for ( $i = 0; $i < count( $highestrow ); $i += $album_sp_config['img_cols'] )
			{
				$template->assign_block_vars( 'highest_pics_block.highest_pics', array() );

				for ( $j = $i; $j < ( $i + $album_sp_config['img_cols'] ); $j++ )
				{
					if ( $j >= count( $highestrow ) )
					{
						break;
					}
					$file_screenshot_url = trim( $highestrow[$j]['file_ssurl'] );
					$template->assign_block_vars( 'highest_pics_block.highest_pics.highest_col', array( 
							// 'U_PIC' => ($album_config['fullpic_popup']) ? append_sid(this_smartor_mxurl("smartor_mode=album_pic&pic_id=". $highestrow[$j]['pic_id'])) : append_sid(this_smartor_mxurl("smartor_mode=album_showpage&pic_id=". $highestrow[$j]['pic_id'])),
							// 'THUMBNAIL' => append_sid(this_smartor_mxurl("smartor_mode=album_thumbnail&pic_id=". $highestrow[$j]['pic_id'], TRUE)),
							// 'DESC' => $highestrow[$j]['pic_desc']
							'SS' => ( !empty( $file_screenshot_url ) ) ? '<hr><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $highestrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '"><img src="' . $file_screenshot_url . '" width="100" border="0"></a><br /><span class="genmed"><i><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $highestrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $highestrow[$j]['file_name'] . '</a></i></span>' : '' 
							) 
						);

					if ( ( $highestrow[$j]['user_id'] == ALBUM_GUEST ) or ( $highestrow[$j]['username'] == '' ) )
					{
						$highest_poster = ( $highestrow[$j]['file_creator'] == '' ) ? $lang['Guest'] : $highestrow[$j]['file_creator'];
					}
					else
					{
						$highest_poster = '<a href="' . append_sid( PHPBB_URL . "profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . '=' . $highestrow[$j]['user_id'] ) . '">' . $highestrow[$j]['username'] . '</a>';
					}

					$rating_image = paImageRating( $highestrow[$j]['rating'] );

					$template->assign_block_vars( 'highest_pics_block.highest_pics.highest_detail', array( 'H_TITLE' => ( empty( $file_screenshot_url ) ) ? '<b>' . $lang['File_Title'] . ': <a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $highestrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $highestrow[$j]['file_name'] . '</a></b><br />' : '',
							'H_DESC' => $highestrow[$j]['file_desc'],
							'H_POSTER' => $highest_poster,
							'H_TIME' => create_date( $board_config['default_dateformat'], $highestrow[$j]['file_time'], $board_config['board_timezone'] ),
							'UPDATED' => create_date( $board_config['default_dateformat'], $highestrow[$j]['file_update_time'], $board_config['board_timezone'] ),

							'H_VIEW' => $highestrow[$j]['file_dls'],

							'H_RATING' => ( $album_config['rate'] == 1 ) ? ( $lang['Rating'] . ': ' . $rating_image . ',&nbsp;' ) : '',
							'H_COMMENTS' => ( $album_config['comment'] == 1 ) ? ( '<a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $highestrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $lang['Comments'] . '</a>: ' . $highestrow[$j]['comments'] . '<br />' ) : ''
							// 'H_IP' => ($userdata['user_level'] == ADMIN) ? $lang['IP_Address'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . decode_ip($highestrow[$j]['pic_user_ip']) . '" target="_blank">' . decode_ip($highestrow[$j]['pic_user_ip']) .'</a><br />' : '' 
							) 
						);
				}
			}
		}
		else
		{ 
			// 
			// No Pics Found
			// 
			$template->assign_block_vars( 'highest_pics_block.no_pics', array() );
		}
	}
	else
	{ 
		// 
		// No Cats Found
		// 
		$template->assign_block_vars( 'highest_pics_block.no_pics', array() );
	}
}

/*
+----------------------------------------------------------
| Random Pics 
| by CLowN
+----------------------------------------------------------
*/
if ( $album_sp_config['disp_rand'] == 1 )
{
	if ( $allowed_cat != '' )
	{
		$sql = "SELECT p.file_id, p.file_name, p.file_desc, p.user_id, p.poster_ip, p.file_creator, p.file_time, p.file_update_time, p.file_catid, p.file_dls, p.file_ssurl, u.user_id, u.username, r.votes_file, AVG(r.rate_point) AS rating, COUNT(DISTINCT c.comments_id) AS comments
				FROM " . PA_FILES_TABLE . " AS p
					LEFT JOIN " . USERS_TABLE . " AS u ON p.user_id = u.user_id
					LEFT JOIN " . PA_CATEGORY_TABLE . " AS ct ON p.file_catid = ct.cat_id
					LEFT JOIN " . PA_VOTES_TABLE . " AS r ON p.file_id = r.votes_file
					LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON p.file_id = c.file_id
				WHERE p.file_catid IN ($allowed_cat) AND p.file_approved = 1 
				GROUP BY p.file_id
				ORDER BY RAND()
				LIMIT " . $album_sp_config['img_cols'] * $album_sp_config['img_rows'];
		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not query rand pics information', '', __LINE__, __FILE__, $sql );
		}

		$randrow = array();

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$randrow[] = $row;
		}

		$template->assign_block_vars( 'random_pics_block', array() );

		if ( count( $randrow ) > 0 )
		{
			for ( $i = 0; $i < count( $randrow ); $i += $album_sp_config['img_cols'] )
			{
				$template->assign_block_vars( 'random_pics_block.rand_pics', array() );

				for ( $j = $i; $j < ( $i + $album_sp_config['img_cols'] ); $j++ )
				{
					if ( $j >= count( $randrow ) )
					{
						break;
					}

					$file_screenshot_url = trim( $randrow[$j]['file_ssurl'] );
					$template->assign_block_vars( 'random_pics_block.rand_pics.rand_col', array( 
							// 'U_PIC' => ($album_config['fullpic_popup']) ? append_sid(this_pa_mxurl("smartor_mode=album_pic&pic_id=". $randrow[$j]['pic_id'])) : append_sid(this_pa_mxurl("smartor_mode=album_showpage&pic_id=". $randrow[$j]['pic_id'])),
							// 'THUMBNAIL' => append_sid(this_pa_mxurl("smartor_mode=album_thumbnail&pic_id=". $randrow[$j]['pic_id'], TRUE)),
							// 'DESC' => $randrow[$j]['file_desc']
							'SS' => ( !empty( $file_screenshot_url ) ) ? '<hr><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $randrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '"><img src="' . $file_screenshot_url . '" width="100" border="0"></a><br /><span class="genmed"><i><a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $randrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $randrow[$j]['file_name'] . '</a></i></span>' : '' 
							) );

					if ( ( $randrow[$j]['user_id'] == ALBUM_GUEST ) or ( $randrow[$j]['username'] == '' ) )
					{
						$rand_poster = ( $randrow[$j]['file_creator'] == '' ) ? $lang['Guest'] : $randrow[$j]['file_creator'];
					}
					else
					{
						$rand_poster = '<a href="' . append_sid( PHPBB_URL . "profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '=' . $randrow[$j]['user_id'] ) . '">' . $randrow[$j]['username'] . '</a>';
					}

					$rating_image = paImageRating( $randrow[$j]['rating'] );

					$template->assign_block_vars( 'random_pics_block.rand_pics.rand_detail', array( 'TITLE' => ( empty( $file_screenshot_url ) ) ? '<b>' . $lang['File_Title'] . ': <a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $randrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $randrow[$j]['file_name'] . '</a></b><br />' : '',
							'DESC' => $randrow[$j]['file_desc'],
							'POSTER' => $rand_poster,
							'TIME' => create_date( $board_config['default_dateformat'], $randrow[$j]['file_time'], $board_config['board_timezone'] ),
							'UPDATED' => create_date( $board_config['default_dateformat'], $randrow[$j]['file_update_time'], $board_config['board_timezone'] ),

							'VIEW' => $randrow[$j]['file_dls'],

							'RATING' => ( $album_config['rate'] == 1 ) ? ( $lang['Rating'] . ': ' . $rating_image . ',&nbsp;' ) : '',

							'COMMENTS' => ( $album_config['comment'] == 1 ) ? ( '<a href="' . append_sid( this_pa_mxurl( "action=file&file_id=" . $randrow[$j]['file_id'], false, $pafiledb_page_id ) ) . '">' . $lang['Comments'] . '</a>: ' . $randrow[$j]['comments'] . '<br />' ) : ''
							// 'IP' => ($userdata['user_level'] == ADMIN) ? $lang['IP_Address'] . ': <a href="http://www.nic.com/cgi-bin/whois.cgi?query=' . decode_ip($randrow[$j]['pic_user_ip']) . '" target="_blank">' . decode_ip($randrow[$j]['pic_user_ip']) .'</a><br />' : '' 
							) 
						);
				}
			}
		}
		else
		{ 
			
			// No Pics Found
			
			$template->assign_block_vars( 'random_pics_block.no_pics', array() );
		}
	}
	else
	{ 
		
		// No Cats Found
		
		$template->assign_block_vars( 'random_pics_block.no_pics', array() );
	}
}

/*
+----------------------------------------------------------
| Start output the page
+----------------------------------------------------------
*/

$page_title = $lang['Album'];
if ( !$is_block )
{
	include( $mx_root_path . 'includes/page_header.' . $phpEx );
}

$template->set_filenames( array( 'body' => 'pa_lists.tpl' ) 
	);

$template->assign_vars( array( 'L_CATEGORY' => $lang['Category'],
		'L_PICS' => $lang['Pics'],
		'L_LAST_PIC' => $lang['Last_Pic'],

		'U_YOUR_PERSONAL_GALLERY' => append_sid( this_pa_mxurl( "smartor_mode=album_personal&user_id=" . $userdata['user_id'] ) ),
		'L_YOUR_PERSONAL_GALLERY' => $lang['Your_Personal_Gallery'],

		'U_USERS_PERSONAL_GALLERIES' => append_sid( this_pa_mxurl( "smartor_mode=album_personal_index" ) ),
		'L_USERS_PERSONAL_GALLERIES' => $lang['Users_Personal_Galleries'],

		'S_COLS' => $album_sp_config['img_cols'],
		'S_COL_WIDTH' => ( 100 / $album_sp_config['img_cols'] ) . '%',
		'TARGET_BLANK' => ( $album_config['fullpic_popup'] ) ? 'target="_blank"' : '',
		'L_RECENT_PUBLIC_PICS' => $lang['Recent_Public_Files'],
		'L_TOPRATED_PUBLIC_PICS' => $lang['Toprated_Public_Files'],
		'L_RANDOM_PUBLIC_PICS' => $lang['Random_Public_Files'],
		'L_MOST_PUBLIC_PICS' => $lang['Most_Public_Files'],
		'L_NO_PICS' => $lang['No_Pics'],
		'L_FILE_TITLE' => $lang['File_Title'],
		'L_FILE_DESC' => $lang['File_Desc'],
		'L_VIEW' => $lang['Dls'],
		'L_POSTER' => $lang['Poster'],
		'L_POSTED' => $lang['Posted'],
		'L_UPDATE_TIME' => $lang['Update_time'],
		'L_PUBLIC_CATS' => $lang['Public_Categories'] ) 
	);

// Generate the page

$template->pparse( 'body' );

if ( !$is_block )
{
	include( $mx_root_path . 'includes/page_tail.' . $phpEx );
}
// +--------------------------------------------------------+
// |  Powered by Photo Album 2.x.x (c) 2002-2003 Smartor    |
// |  with Volodymyr (CLowN) Skoryk's Service Pack 1 © 2003 |
// +--------------------------------------------------------+

?>