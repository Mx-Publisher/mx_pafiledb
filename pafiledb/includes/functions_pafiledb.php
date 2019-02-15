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
 *    $Id: functions_pafiledb.php,v 1.22 2005/12/08 15:15:12 jonohlsson Exp $
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

// ===================================================
// public pafiledb class
// ===================================================
class pafiledb_public extends mx_pafiledb
{
	var $modules = array();
	var $module_name = ''; 
	
	// ===================================================
	// load module
	// $module name : send module name to load it
	// ===================================================
	function module( $module_name )
	{
		if ( !class_exists( 'pafiledb_' . $module_name ) )
		{
			global $phpbb_root_path, $phpEx; 
			global $mx_root_path, $module_root_path, $is_block, $phpEx;

			$this->module_name = $module_name;

			require_once( $module_root_path . 'pafiledb/modules/pa_' . $module_name . '.' . $phpEx );
			eval( '$this->modules[' . $module_name . '] = new pafiledb_' . $module_name . '();' );

			if ( method_exists( $this->modules[$module_name], 'init' ) )
			{
				$this->modules[$module_name]->init();
			}
		}
	} 
	
	// ===================================================
	// this will be replaced by the loaded module
	// ===================================================
	function main( $module_id = false )
	{
		return false;
	} 
	
	// ===================================================
	// go ahead and output the page
	// $page title : send page title
	// $tpl_name : template file name
	// ===================================================
	function display( $page_title, $tpl_name )
	{
		global $pafiledb_template;

		pafiledb_page_header( $page_title );

		$pafiledb_template->set_filenames( array( 'body' => $tpl_name ) );

		pafiledb_page_footer();
	}
}

// ===================================================
// pafiledb class
// ===================================================
class mx_pafiledb extends mx_pafiledb_auth
{
	var $cat_rowset = array();
	var $subcat_rowset = array();
	var $total_cat = 0;
	
	var $comments = array();
	var $ratings = array();
	var $information = array();
	var $notification = array();
	
	var $modified = false;
	var $error = array(); 
	
	var $page_title = '';
	var $jumpbox = '';
	var $auth_can_list = '';
	var $navigation = '';	
	
	var $debug = true;
	var $debug_msg = '';	
	
	// ===================================================
	// Prepare data
	// ===================================================
	function init()
	{
		global $db, $userdata, $debug, $pafiledb_config;

		unset( $this->cat_rowset );
		unset( $this->subcat_rowset );
		unset( $this->comments );
		unset( $this->ratings );
		unset( $this->information );
		unset( $this->notification );

		$sql = 'SELECT * 
			FROM ' . PA_CATEGORY_TABLE . '
			ORDER BY cat_order ASC';

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql );
		}
		$cat_rowset = $db->sql_fetchrowset( $result );

		$db->sql_freeresult( $result );

		$this->auth( $cat_rowset );

		for( $i = 0; $i < count( $cat_rowset ); $i++ )
		{
			if ( $this->auth[$cat_rowset[$i]['cat_id']]['auth_view'] )
			{
				$this->cat_rowset[$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->subcat_rowset[$cat_rowset[$i]['cat_parent']][$cat_rowset[$i]['cat_id']] = $cat_rowset[$i];
				$this->total_cat++;
			}
			
			//
			// Comments
			// Note: some settings are category dependent, but may use default config settings
			//
			$this->comments[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_comments'] == -1 ? ($pafiledb_config['use_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_comments'] == 1 ? true : false );
			$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] = $cat_rowset[$i]['internal_comments'] == -1 ? ($pafiledb_config['internal_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['internal_comments'] == 1 ? true : false ); // phpBB or internal comments
			$this->comments[$cat_rowset[$i]['cat_id']]['autogenerate_comments'] = $cat_rowset[$i]['autogenerate_comments'] == -1 ? ($pafiledb_config['autogenerate_comments'] == 1 ? true : false ) : ( $cat_rowset[$i]['autogenerate_comments'] == 1 ? true : false ); // autocreate comments when updated
			$this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id'] = $cat_rowset[$i]['comments_forum_id'] == -1 ? ($pafiledb_config['comments_forum_id'] == 1 ? true : false ) : ( $cat_rowset[$i]['comments_forum_id'] == 1 ? true : false ); // phpBB target forum (only used for phpBB comments)
			
			if (!$this->comments[$cat_rowset[$i]['cat_id']]['internal_comments'] && intval($this->comments[$cat_rowset[$i]['cat_id']]['comments_forum_id']) < 1)
			{
				// mx_message_die(GENERAL_ERROR, 'Init Failure, phpBB comments with no target forum_id :(');
			}
			
			//
			// Ratings
			//
			$this->ratings[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['cat_allow_ratings'] == -1 ? ($pafiledb_config['use_ratings'] == 1 ? true : false ) : ( $cat_rowset[$i]['cat_allow_ratings'] == 1 ? true : false );
			
			//
			// Information
			// 
			$this->information[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['show_pretext'] == -1 ? ($pafiledb_config['show_pretext'] == 1 ? true : false ) : ( $cat_rowset[$i]['show_pretext'] == 1 ? true : false ); // phpBB or internal ratings
			
			//
			// Notification
			//
			$this->notification[$cat_rowset[$i]['cat_id']]['activated'] = $cat_rowset[$i]['notify'] == -1 ? (intval($pafiledb_config['nofity'])) : ( intval($cat_rowset[$i]['nofity']) ); // -1, 0, 1, 2
			$this->notification[$cat_rowset[$i]['cat_id']]['nofity_group'] = $cat_rowset[$i]['nofity_group'] == -1 ? (intval($pafiledb_config['nofity_group'])) : ( intval($cat_rowset[$i]['nofity_group']) ); // Group_id
			
		}
	} 
	
	function _pafiledb()
	{
		if ( $this->modified )
		{
			$this->sync_all();
		}
	}

	function sync_all()
	{
		foreach( $this->cat_rowset as $cat_id => $void )
		{
			$this->sync( $cat_id, false );
		}
		$this->init();
	}

	function sync( $cat_id, $init = true )
	{
		global $db;

		$cat_nav = array();
		$this->category_nav( $this->cat_rowset[$cat_id]['cat_parent'], &$cat_nav );

		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
			SET parents_data = ''
			WHERE cat_parent = " . $this->cat_rowset[$cat_id]['cat_parent'];

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql );
		}

		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
				SET cat_files = '-1',
				cat_last_file_id = '0', 
				cat_last_file_name = '', 
				cat_last_file_time = '0'
				WHERE cat_id = '" . $cat_id . "'";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql );
		}
		if ( $init )
		{
			$this->init();
		}
		return;
	}

	function category_nav( $parent_id, &$cat_nav )
	{
		if ( !empty( $this->cat_rowset[$parent_id] ) )
		{
			$this->category_nav( $this->cat_rowset[$parent_id]['cat_parent'], &$cat_nav );
			$cat_nav[$parent_id] = $this->cat_rowset[$parent_id]['cat_name'];
		}
		return;
	}
		
	// ===================================================
	// if there is no cat
	// ===================================================
	function cat_empty()
	{
		return ( $this->total_cat == 0 ) ? true : false;
	}

	function modified( $true_false = false )
	{
		$this->modified = $true_false;
	} 

	function file_in_cat( $cat_id )
	{
		if ( $this->cat_rowset[$cat_id]['cat_files'] == -1 || $this->modified )
		{
			global $db, $db;

			$sql = 'SELECT COUNT(file_id) as total_files
				FROM ' . PA_FILES_TABLE . " 
				WHERE file_approved = '1' 
				AND file_catid IN (" . $this->gen_cat_ids( $cat_id ) . ')
				ORDER BY file_time DESC';

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query Files info', '', __LINE__, __FILE__, $sql );
			}

			$files_no = 0;
			if ( $row = $db->sql_fetchrow( $result ) )
			{
				$files_no = $row['total_files'];
			}

			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
					SET cat_files = $files_no
					WHERE cat_id = $cat_id";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query Files info', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
			$files_no = $this->cat_rowset[$cat_id]['cat_files'];
		}

		return $files_no;
	}
		
	// ===================================================
	// Jump menu function
	// $cat_id : to handle parent cat_id
	// $depth : related to function to generate tree
	// $default : the cat you wanted to be selected
	// $for_file: TRUE high category ids will be -1
	// $check_upload: if true permission for upload will be checked
	// ===================================================
	function generate_jumpbox( $cat_id = 0, $depth = 0, $default = '', $for_file = false, $check_upload = false )
	{
		global $page_id;
		static $cat_rowset = false;

		if ( !is_array( $cat_rowset ) )
		{
			if ( $check_upload )
			{
				if ( !empty( $this->cat_rowset ) )
				{
					foreach( $this->cat_rowset as $row )
					{
						if ( $this->auth[$row['cat_id']]['auth_upload'] )
						{
							$cat_rowset[$row['cat_id']] = $row;
						}
					}
				}
			}
			else
			{
				$cat_rowset = $this->cat_rowset;
			}
		}

		$cat_list .= '';

		$pre = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth );

		$temp_cat_rowset = $cat_rowset;

		if ( !empty( $temp_cat_rowset ) )
		{
			foreach ( $temp_cat_rowset as $temp_cat_id => $cat )
			{
				if ( $cat['cat_parent'] == $cat_id )
				{
					if ( is_array( $default ) )
					{
						if ( isset( $default[$cat['cat_id']] ) )
						{
							$sel = ' selected="selected"';
						}
						else
						{
							$sel = '';
						}
					}
					$cat_pre = ( !$cat['cat_allow_file'] ) ? '+ ' : '- ';
					$sub_cat_id = ( $for_file ) ? ( ( !$cat['cat_allow_file'] ) ? -1 : $cat['cat_id'] ) : $cat['cat_id'];
					$cat_class = ( !$cat['cat_allow_file'] ) ? 'class="greyed"' : '';
					$cat_list .= '<option value="' . $sub_cat_id . '"' . $sel . ' ' . $cat_class . ' />' . $pre . $cat_pre . $cat['cat_name'] . '</option>';
					$cat_list .= $this->generate_jumpbox( $cat['cat_id'], $depth + 1, $default, $for_file, $check_upload );
				}
			}
			return $cat_list;
		}
		else
		{
			return;
		}
	} 

	// ===================================================
	// get all sub category in side certain category
	// $cat_id : category id
	// ===================================================
	function get_sub_cat( $cat_id )
	{ 
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$cat_sub .= '';
		if ( !empty( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $cat_id => $cat_row )
			{
				if ( $cat_row['cat_allow_file'] )
				{
					$cat_sub .= '<a href="' . append_sid( pa_this_mxurl( 'action=category&cat_id=' . $cat_row['cat_id'] ) ) . '">' . $cat_row['cat_name'] . '</a>, ';
				}
				else
				{
					if ( !empty( $this->subcat_rowset[$cat_row['cat_id']] ) )
					{
						foreach( $this->subcat_rowset[$cat_row['cat_id']] as $sub_cat_id => $sub_cat_row )
						{
							if ( $sub_cat_row['cat_allow_file'] )
							{
								$cat_sub .= '<a href="' . append_sid( pa_this_mxurl( 'action=category&cat_id=' . $sub_cat_row['cat_id'] ) ) . '">' . $sub_cat_row['cat_name'] . '</a>, ';
							}
						}
					}
				}
			}
		}
		return $cat_sub;
	}

	function generate_navigation( $cat_id )
	{
		global $pafiledb_template, $db; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		if ( $this->cat_rowset[$cat_id]['parents_data'] == '' )
		{
			$cat_nav = array();
			$this->category_nav( $this->cat_rowset[$cat_id]['cat_parent'], &$cat_nav );

			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
				SET parents_data = '" . addslashes( serialize( $cat_nav ) ) . "'
				WHERE cat_parent = " . $this->cat_rowset[$cat_id]['cat_parent'];

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query categories info', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
			$cat_nav = unserialize( stripslashes( $this->cat_rowset[$cat_id]['parents_data'] ) );
		}

		if ( !empty( $cat_nav ) )
		{
			foreach ( $cat_nav as $parent_cat_id => $parent_name )
			{
				$pafiledb_template->assign_block_vars( 'navlinks', array( 'CAT_NAME' => $parent_name,
						'U_VIEW_CAT' => append_sid( pa_this_mxurl( 'action=category&cat_id=' . $parent_cat_id ) ) ) 
					);
			}
		}

		$pafiledb_template->assign_block_vars( 'navlinks', array( 'CAT_NAME' => $this->cat_rowset[$cat_id]['cat_name'],
				'U_VIEW_CAT' => append_sid( pa_this_mxurl( 'action=category&cat_id=' . $this->cat_rowset[$cat_id]['cat_id'] ) ) ) 
			);

		return;
	}

	function new_file_in_cat_old( $cat_id )
	{
		global $pafiledb_config, $board_config, $db, $_COOKIE;

		$files_new = 0;

		$time = time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 );

		$sql = 'SELECT file_time, file_catid
			FROM ' . PA_FILES_TABLE . " 
			WHERE file_approved = '1'
			AND file_catid IN (" . $this->gen_cat_ids( $cat_id ) . ')
			AND file_time > ' . $time . '
			ORDER BY file_time DESC';

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query Files info', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $this->auth[$row['file_catid']]['auth_read'] )
			{
				$files_new++;
			}
		}

		return $files_new;
	}

	function new_file_in_cat( $cat_id )
	{
		global $pafiledb_config, $board_config, $db, $_COOKIE;

		$cat_array = explode(',', $this->gen_cat_ids( $cat_id ));
		
		$files_new = 0;

		$time = time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 );

		foreach ( $cat_array as $key => $cat_id )
		{
			if ( $this->auth[$cat_id]['auth_read'] )
			{
				$files_new++;
			}
		}

		return $files_new;
	}

	function last_file_in_cat( $cat_id, &$file_info )
	{
		if ( ( empty( $this->cat_rowset[$cat_id]['cat_last_file_id'] ) && empty( $this->cat_rowset[$cat_id]['cat_last_file_name'] ) && empty( $this->cat_rowset[$cat_id]['cat_last_file_time'] ) ) || $this->modified )
		{
			global $db;

			$sql = 'SELECT file_time, file_id, file_name, file_catid
				FROM ' . PA_FILES_TABLE . " 
				WHERE file_approved = '1' 
				AND file_catid IN (" . $this->gen_cat_ids( $cat_id ) . ")
				ORDER BY file_time DESC";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query Files info', '', __LINE__, __FILE__, $sql );
			}

			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$temp_cat[] = $row;
			}

			$file_info = $temp_cat[0];
			if ( !empty( $file_info ) )
			{
				$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
					SET cat_last_file_id = " . intval( $file_info['file_id'] ) . ", 
					cat_last_file_name = '" . addslashes( $file_info['file_name'] ) . "', 
					cat_last_file_time = " . intval( $file_info['file_time'] ) . "
					WHERE cat_id = $cat_id";

				if ( !( $db->sql_query( $sql ) ) )
				{
					mx_message_die( GENERAL_ERROR, 'Couldnt Query Files info', '', __LINE__, __FILE__, $sql );
				}
			}
		}
		else
		{
			$file_info['file_id'] = $this->cat_rowset[$cat_id]['cat_last_file_id'];
			$file_info['file_name'] = $this->cat_rowset[$cat_id]['cat_last_file_name'];
			$file_info['file_time'] = $this->cat_rowset[$cat_id]['cat_last_file_time'];
		}
	}
		
	function gen_cat_ids( $cat_id, $cat_ids = '' )
	{
		if ( !empty( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $cat_row )
			{
				$cat_ids = $this->gen_cat_ids( $subcat_id, $cat_ids );
			}
		}

		if ( !empty( $this->cat_rowset[$cat_id] ) )
		{
			$cat_ids .= ( ( $cat_ids != '' ) ? ', ' : '' ) . $cat_id;
		}
		return $cat_ids;
	}

	function display_categories( $cat_id = PA_ROOT_CAT )
	{
		global $db, $pafiledb_template, $lang, $userdata, $phpEx, $images;
		global $pafiledb_config, $board_config, $debug; 
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $phpEx;

		if ( $this->cat_empty() )
		{
			mx_message_die( GENERAL_ERROR, 'Either you are not allowed to view any category, or there is no category in the database' );
		}

		$pafiledb_template->assign_vars( array( 
			'CAT_PARENT' => true,
			'L_SUB_CAT' => $lang['Sub_category'],
			'L_CATEGORY' => $lang['Category'],
			'L_LAST_FILE' => $lang['Last_file'],
			'L_FILES' => $lang['Files'] ) 
		); 
			
		//
		// output the root level category that allow file
		//
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $subcat_row )
			{
				if ( ( $subcat_row['cat_allow_file'] == PA_CAT_ALLOW_FILE ) )
				{
					$last_file_info = array();
					$this->last_file_in_cat( $subcat_id, $last_file_info );

					if ( !empty( $last_file_info['file_id'] ) && $this->auth[$subcat_id]['auth_read'] )
					{
						$last_file_time = create_date( $board_config['default_dateformat'], $last_file_info['file_time'], $board_config['board_timezone'] );
						$last_file = $last_file_time . '<br />';
						$last_file_name = ( strlen( stripslashes( $last_file_info['file_name'] ) ) > 20 ) ? substr( stripslashes( $last_file_info['file_name'] ), 0, 20 ) . '...' : stripslashes( $last_file_info['file_name'] );
						$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '" alt="' . stripslashes( $last_file_info['file_name'] ) . '" title="' . stripslashes( $last_file_info['file_name'] ) . '">' . $last_file_name . '</a> ';
						$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '"><img src="' . $phpbb_root_path . $images['icon_latest_reply'] . '" border="0" alt="' . $lang['View_latest_file'] . '" title="' . $lang['View_latest_file'] . '" /></a>';
					}
					else
					{
						$last_file = $lang['No_file'];
					}
					$is_new = false;

					if ( $this->new_file_in_cat( $subcat_id ) )
					{
						$is_new = true;
					}

					$sub_cat = $this->get_sub_cat( $subcat_id );

					$pafiledb_template->assign_block_vars( 'no_cat_parent', array( 'IS_HIGHER_CAT' => false,
							'U_CAT' => append_sid( pa_this_mxurl( 'action=category&cat_id=' . $subcat_id ) ),
							'SUB_CAT' => ( !empty( $sub_cat ) ) ? '<b>' . $lang['Sub_category'] . ': </b>' . $sub_cat :  '',
							'CAT_IMAGE' => ( $is_new ) ? $phpbb_root_path . $images['folder_new'] : $phpbb_root_path . $images['folder'],
							'CAT_NEW_FILE' => ( $is_new ) ? $lang['New_file'] : $lang['No_new_file'],
							'CAT_NAME' => $subcat_row['cat_name'],
							'FILECAT' => $this->file_in_cat( $subcat_id ),
							'LAST_FILE' => $last_file,
							'CAT_DESC' => $subcat_row['cat_desc'] ) 
						);
				}
			}
		}
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $subcat_row )
			{
				$total_sub_cat = 0;
				if ( isset( $this->subcat_rowset[$subcat_id] ) )
				{
					foreach( $this->subcat_rowset[$subcat_id] as $sub_no_cat_id => $sub_no_cat_row )
					{
						if ( $sub_no_cat_row['cat_allow_file'] == PA_CAT_ALLOW_FILE )
						{
							$sub_cat_rowset[$total_sub_cat] = $sub_no_cat_row;
							$total_sub_cat++;
						}
					}
				}

				if ( ( $subcat_row['cat_allow_file'] != PA_CAT_ALLOW_FILE ) )
				{
					if ( $total_sub_cat )
					{
						$pafiledb_template->assign_block_vars( 'no_cat_parent', array( 'IS_HIGHER_CAT' => true,
								'U_CAT' => append_sid( pa_this_mxurl( 'action=category&cat_id=' . $subcat_id ) ),
								'CAT_NAME' => $subcat_row['cat_name'] ) 
							);
					}
					for( $k = 0; $k < $total_sub_cat; $k++ )
					{
						$last_file_info = array();
						$this->last_file_in_cat( $sub_cat_rowset[$k]['cat_id'], $last_file_info );

						if ( $sub_cat_rowset[$k]['cat_parent'] == $subcat_id )
						{
							if ( !empty( $last_file_info['file_id'] ) && $this->auth[$sub_cat_rowset[$k]['cat_id']]['auth_read'] )
							{
								$last_file_time = create_date( $board_config['default_dateformat'], $last_file_info['file_time'], $board_config['board_timezone'] );
								$last_file = $last_file_time . '<br />';
								$last_file_name = ( strlen( $last_file_info['file_name'] ) > 20 ) ? substr( $last_file_info['file_name'], 0, 20 ) . '...' : $last_file_info['file_name'];
								$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '">' . $last_file_name . '</a> ';
								$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '"><img src="' . $phpbb_root_path . $images['icon_latest_reply'] . '" border="0" alt="' . $lang['View_latest_file'] . '" title="' . $lang['View_latest_file'] . '" /></a>';
							}
							else
							{
								$last_file = $lang['No_file'];
							}

							$is_new = false;
							
							if ( $this->new_file_in_cat( $sub_cat_rowset[$k]['cat_id'] ) )
							{
								$is_new = true;
							}

							$sub_cat = $this->get_sub_cat( $sub_cat_rowset[$k]['cat_id'] );

							$pafiledb_template->assign_block_vars( 'no_cat_parent', array( 'IS_HIGER_CAT' => false,
									'U_CAT' => append_sid( pa_this_mxurl( 'action=category&cat_id=' . $sub_cat_rowset[$k]['cat_id'] ) ),
									'SUB_CAT' => ( !empty( $sub_cat ) ) ? '<b>' . $lang['Sub_category'] . ': </b>' . $sub_cat : '',
									'CAT_IMAGE' => ( $is_new ) ? $phpbb_root_path . $images['folder_new'] : $phpbb_root_path . $images['folder'],
									'CAT_NEW_FILE' => ( $is_new ) ? $lang['New_file'] : $lang['No_new_file'],
									'CAT_NAME' => $sub_cat_rowset[$k]['cat_name'],
									'FILECAT' => $this->file_in_cat( $sub_cat_rowset[$k]['cat_id'] ),
									'LAST_FILE' => $last_file,
									'CAT_DESC' => $sub_cat_rowset[$k]['cat_desc'] ) 
								);
						} // Have a permission to view the category
					} // It is not parent category
				}
			}
		} //higher Category
	}

	function display_categories_quickdl( $cat_id = PA_ROOT_CAT, $pa_get_dynamic = array() )
	{
		global $db, $pafiledb_template, $lang, $userdata, $phpEx, $images;
		global $pafiledb_config, $board_config, $debug; 
		// MX
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $phpEx;

		if ( $this->cat_empty() )
		{
			mx_message_die( GENERAL_ERROR, 'Either you are not allowed to view any category, or there is no category in the database' );
		}

		$pafiledb_template->assign_vars( array( 'CAT_PARENT' => true,
				'L_SUB_CAT' => $lang['Sub_category'],
				'L_CATEGORY' => $lang['Category'],
				'L_LAST_FILE' => $lang['Last_file'],
				'L_FILES' => $lang['Files'] ) 
			); 
		// output the root level category that allow file
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $subcat_row )
			{
				if ( ( $subcat_row['cat_allow_file'] == PA_CAT_ALLOW_FILE ) )
				{
					$last_file_info = array();
					$this->last_file_in_cat( $subcat_id, $last_file_info );

					if ( !empty( $last_file_info['file_id'] ) && $this->auth[$subcat_id]['auth_read'] )
					{
						$last_file_time = create_date( $board_config['default_dateformat'], $last_file_info['file_time'], $board_config['board_timezone'] );
						$last_file = $last_file_time . '<br />';
						$last_file_name = ( strlen( stripslashes( $last_file_info['file_name'] ) ) > 20 ) ? substr( stripslashes( $last_file_info['file_name'] ), 0, 20 ) . '...' : stripslashes( $last_file_info['file_name'] );
						$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=download&file_id=' . $last_file_info['file_id'], true ) ) . '" alt="' . stripslashes( $last_file_info['file_name'] ) . '" title="' . stripslashes( $last_file_info['file_name'] ) . '">' . $last_file_name . '</a> ';
						$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=download&file_id=' . $last_file_info['file_id'], true ) ) . '"><img src="' . $phpbb_root_path . $images['icon_latest_reply'] . '" border="0" alt="' . $lang['View_latest_file'] . '" title="' . $lang['View_latest_file'] . '" /></a>';
					}
					else
					{
						$last_file = $lang['No_file'];
					}
					$is_new = false;

					if ( $this->new_file_in_cat( $subcat_id ) )
					{
						$is_new = true;
					}

					$sub_cat = $this->get_sub_cat( $subcat_id );

					$map_xtra = !empty( $pa_get_dynamic[$subcat_id] ) ? '&dynamic_block=' . $pa_get_dynamic[$subcat_id]  : '';
					
					$pafiledb_template->assign_block_vars( 'no_cat_parent', array( 'IS_HIGHER_CAT' => false,
							'U_CAT' => append_sid( pa_this_mxurl( 'action=quickdl&cat_id=' . $subcat_id . $map_xtra ) ),
							'SUB_CAT' => ( !empty( $sub_cat ) ) ? '<b>' . $lang['Sub_category'] . ': </b>' . $sub_cat :  '',
							'CAT_IMAGE' => ( $is_new ) ? $phpbb_root_path . $images['folder_new'] : $phpbb_root_path . $images['folder'],
							'CAT_NEW_FILE' => ( $is_new ) ? $lang['New_file'] : $lang['No_new_file'],
							'CAT_NAME' => $subcat_row['cat_name'],
							'FILECAT' => $this->file_in_cat( $subcat_id ),
							'LAST_FILE' => $last_file,
							'CAT_DESC' => $subcat_row['cat_desc'] ) 
						);
				}
			}
		}
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $subcat_id => $subcat_row )
			{
				$total_sub_cat = 0;
				if ( isset( $this->subcat_rowset[$subcat_id] ) )
				{
					foreach( $this->subcat_rowset[$subcat_id] as $sub_no_cat_id => $sub_no_cat_row )
					{
						if ( $sub_no_cat_row['cat_allow_file'] == PA_CAT_ALLOW_FILE )
						{
							$sub_cat_rowset[$total_sub_cat] = $sub_no_cat_row;
							$total_sub_cat++;
						}
					}
				}

				if ( ( $subcat_row['cat_allow_file'] != PA_CAT_ALLOW_FILE ) )
				{
					if ( $total_sub_cat )
					{
						$pafiledb_template->assign_block_vars( 'no_cat_parent', array( 'IS_HIGHER_CAT' => true,
								'U_CAT' => append_sid( pa_this_mxurl( 'action=category&cat_id=' . $subcat_id ) ),
								'CAT_NAME' => $subcat_row['cat_name'] ) 
							);
					}
					for( $k = 0; $k < $total_sub_cat; $k++ )
					{
						$last_file_info = array();
						$this->last_file_in_cat( $sub_cat_rowset[$k]['cat_id'], $last_file_info );

						if ( $sub_cat_rowset[$k]['cat_parent'] == $subcat_id )
						{
							if ( !empty( $last_file_info['file_id'] ) && $this->auth[$sub_cat_rowset[$k]['cat_id']]['auth_read'] )
							{
								$last_file_time = create_date( $board_config['default_dateformat'], $last_file_info['file_time'], $board_config['board_timezone'] );
								$last_file = $last_file_time . '<br />';
								$last_file_name = ( strlen( $last_file_info['file_name'] ) > 20 ) ? substr( $last_file_info['file_name'], 0, 20 ) . '...' : $last_file_info['file_name'];
								$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '">' . $last_file_name . '</a> ';
								$last_file .= '<a href="' . append_sid( pa_this_mxurl( 'action=file&file_id=' . $last_file_info['file_id'] ) ) . '"><img src="' . $phpbb_root_path . $images['icon_latest_reply'] . '" border="0" alt="' . $lang['View_latest_file'] . '" title="' . $lang['View_latest_file'] . '" /></a>';
							}
							else
							{
								$last_file = $lang['No_file'];
							}

							$is_new = false;

							if ( $this->new_file_in_cat( $sub_cat_rowset[$k]['cat_id'] ) )
							{
								$is_new = true;
							}

							$sub_cat = $this->get_sub_cat( $sub_cat_rowset[$k]['cat_id'] );

							$pafiledb_template->assign_block_vars( 'no_cat_parent', array( 'IS_HIGER_CAT' => false,
									'U_CAT' => append_sid( pa_this_mxurl( 'action=category&cat_id=' . $sub_cat_rowset[$k]['cat_id'] ) ),
									'SUB_CAT' => ( !empty( $sub_cat ) ) ? '<b>' . $lang['Sub_category'] . ': </b>' . $sub_cat : '',
									'CAT_IMAGE' => ( $is_new ) ? $phpbb_root_path . $images['folder_new'] : $phpbb_root_path . $images['folder'],
									'CAT_NEW_FILE' => ( $is_new ) ? $lang['New_file'] : $lang['No_new_file'],
									'CAT_NAME' => $sub_cat_rowset[$k]['cat_name'],
									'FILECAT' => $this->file_in_cat( $sub_cat_rowset[$k]['cat_id'] ),
									'LAST_FILE' => $last_file,
									'CAT_DESC' => $sub_cat_rowset[$k]['cat_desc'] ) 
								);
						} // Have a permission to view the category
					} // It is not parent category
				}
			}
		} //higher Category
	}
		
	function display_files( $sort_method, $sort_order, $start, $show_file_message, $cat_id = false )
	{
		global $db, $pafiledb_config, $pafiledb_template, $board_config;
		global $images, $lang, $phpEx, $pafiledb_functions; 
		// MX
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $phpEx;

		$filelist = false;

		if ( empty( $cat_id ) )
		{
			$cat_where = '';
		}
		else
		{
			$cat_where = "AND f1.file_catid = $cat_id";
		}

		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) AS total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f1, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c, " . PA_CATEGORY_TABLE . " AS cat
					WHERE f1.file_id = r.votes_file(+)
					AND f1.user_id = u.user_id(+)
					AND f1.file_id = c.file_id(+)
					AND f1.file_pin = " . FILE_PINNED . "
					AND f1.file_approved = 1
					AND f1.file_catid = cat.cat_id
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;

			default:
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) AS total_comments, cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f1
						LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f1.file_id = r.votes_file 
						LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
						LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f1.file_id = c.file_id
						LEFT JOIN " . PA_CATEGORY_TABLE . " AS cat ON f1.file_catid = cat.cat_id
					WHERE f1.file_pin = " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;
		}

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info for this category', '', __LINE__, __FILE__, $sql );
		}

		$file_rowset = array();
		$total_file = 0;

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $this->auth[$row['file_catid']]['auth_read'] )
			{
				$file_rowset[] = $row;
			}
		}

		$db->sql_freeresult( $result );

		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id), cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f1, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c, " . PA_CATEGORY_TABLE . " AS cat
					WHERE f1.file_id = r.votes_file(+)
					AND f1.user_id = u.user_id(+)
					AND f1.file_id = c.file_id(+)
					AND f1.file_pin <> " . FILE_PINNED . "
					AND f1.file_approved = 1
					AND f1.file_catid = cat.cat_id
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;

			default:
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id), cat.cat_allow_ratings, cat.cat_allow_comments
					FROM " . PA_FILES_TABLE . " AS f1
						LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f1.file_id = r.votes_file 
						LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
						LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f1.file_id = c.file_id
						LEFT JOIN " . PA_CATEGORY_TABLE . " AS cat ON f1.file_catid = cat.cat_id
					WHERE f1.file_pin <> " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;
		}

		if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $pafiledb_config['pagination'], $start ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info for this category', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $this->auth[$row['file_catid']]['auth_read'] )
			{
				$file_rowset[] = $row;
			}
		}

		$db->sql_freeresult( $result );

		$where_sql = ( !empty( $cat_id ) ) ? "AND file_catid = $cat_id" : '';
		$sql = "SELECT COUNT(file_id) as total_file
			FROM " . PA_FILES_TABLE . " 
			WHERE file_approved='1'
			$where_sql";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldn\'t get number of file', '', __LINE__, __FILE__, $sql );
		}

		$row = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );

		$total_file = $row['total_file'];
		unset( $row );
		
		$pa_use_ratings = false;
		for ( $i = 0; $i < count( $file_rowset ); $i++ )
		{ 
			if ( $file_rowset[$i]['cat_allow_ratings'] )
			{
				$pa_use_ratings = true;
				break;
			}
		}
		
		for ( $i = 0; $i < count( $file_rowset ); $i++ )
		{ 
			// ===================================================
			// Format the date for the given file
			// ===================================================
			$date = create_date( $board_config['default_dateformat'], $file_rowset[$i]['file_time'], $board_config['board_timezone'] ); 
			$date_updated = create_date( $board_config['default_dateformat'], $file_rowset[$i]['file_update_time'], $board_config['board_timezone'] ); 
			// ===================================================
			// Get rating for the file and format it
			// ===================================================
			$rating = ( $file_rowset[$i]['rating'] != 0 ) ? round( $file_rowset[$i]['rating'], 2 ) . ' / 10' : $lang['Not_rated']; 
			// ===================================================
			// If the file is new then put a new image in front of it
			// ===================================================
			$is_new = false;
			if ( time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 ) < $file_rowset[$i]['file_time'] )
			{
				$is_new = true;
			}

			$cat_name = ( empty( $cat_id ) ) ? $this->cat_rowset[$file_rowset[$i]['file_catid']]['cat_name'] : '';
			$cat_url = append_sid( pa_this_mxurl( 'action=category&cat_id=' . $file_rowset[$i]['file_catid'] ) ); 
			// ===================================================
			// Get the post icon fot this file
			// ===================================================
			if ( $file_rowset[$i]['file_pin'] != FILE_PINNED )
			{
				if ( $file_rowset[$i]['file_posticon'] == 'none' || $file_rowset[$i]['file_posticon'] == 'none.gif' )
				{
					$posticon = $module_root_path . 'pafiledb/images/spacer.gif';
				}
				else
				{
					$posticon = $module_root_path . ICONS_DIR . $file_rowset[$i]['file_posticon'];
				}
			}
			else
			{
				$posticon = $phpbb_root_path . $images['folder_sticky'];
			} 
			// ===================================================
			// Assign Vars
			// ===================================================
			
			$pafiledb_template->assign_block_vars( "file_rows", array( 'L_NEW_FILE' => $lang['New_file'],

					'PIN_IMAGE' => $posticon, 
					// 'FILE_NEW_IMAGE' => $phpbb_root_path . $images['pa_file_new'],
					'FILE_NEW_IMAGE' => $images['pa_file_new'],
					'HAS_SCREENSHOTS' => ( !empty( $file_rowset[$i]['file_ssurl'] ) ) ? true : false,
					'SS_AS_LINK' => ( $file_rowset[$i]['file_sshot_link'] ) ? true : false,
					'FILE_SCREENSHOT' => $file_rowset[$i]['file_ssurl'], 
					// Added by Haplo
					'FILE_SCREENSHOT_URL' => $module_root_path . 'pafiledb/images/lwin.gif',
					'FILE_NAME' => $file_rowset[$i]['file_name'],
					'FILE_DESC' => $file_rowset[$i]['file_desc'],
					'DATE' => $date,
					'UPDATED' => $date_updated,
					'RATING' => ( $file_rowset[$i]['cat_allow_ratings'] ? $rating : $lang['kb_no_ratings'] ),
					'FILE_DLS' => $file_rowset[$i]['file_dls'],
					'CAT_NAME' => $cat_name,
					'IS_NEW_FILE' => $is_new,

					'U_CAT' => $cat_url,
					'SHOW_RATINGS' => ( $pa_use_ratings ?  true : false ),
					'U_FILE' => append_sid( pa_this_mxurl( 'action=file&file_id=' . $file_rowset[$i]['file_id'] ) ) ) 
				);
			$filelist = true;
			$pa_use_ratings = $file_rowset[$i]['cat_allow_ratings'];
		}

		if ( $filelist )
		{
			$action = ( empty( $cat_id ) ) ? 'viewall' : 'category&amp;cat_id=' . $cat_id;
			$pafiledb_template->assign_vars( array( 
					'L_CATEGORY' => $lang['Category'],
					'L_RATING' => $lang['DlRating'],
					'L_DOWNLOADS' => $lang['Dls'],
					'L_DATE' => $lang['Date'],
					'L_NAME' => $lang['Name'],
					'L_FILE' => $lang['File'],
					'L_UPDATE_TIME' => $lang['Update_time'],
					'L_SCREENSHOTS' => $lang['Scrsht'],

					'L_SELECT_SORT_METHOD' => $lang['Select_sort_method'],
					'L_ORDER' => $lang['Order'],
					'L_SORT' => $lang['Sort'],

					'L_ASC' => $lang['Sort_Ascending'],
					'L_DESC' => $lang['Sort_Descending'],

					'SORT_NAME' => ( $sort_method == 'file_name' ) ? 'selected="selected"' : '',
					'SORT_TIME' => ( $sort_method == 'file_time' ) ? 'selected="selected"' : '',
					'SORT_RATING' => ( $sort_method == 'rating' ) ? 'selected="selected"' : '',
					'SORT_DOWNLOADS' => ( $sort_method == 'file_dls' ) ? 'selected="selected"' : '',
					'SORT_UPDATE_TIME' => ( $sort_method == 'file_update_time' ) ? 'selected="selected"' : '',

					'SORT_ASC' => ( $sort_order == 'ASC' ) ? 'selected="selected"' : '',
					'SORT_DESC' => ( $sort_order == 'DESC' ) ? 'selected="selected"' : '',
					'PAGINATION' => generate_pagination( append_sid( pa_this_mxurl( "action=$action&amp;sort_method=$sort_method&amp;sort_order=$sort_order" ) ), $total_file, $pafiledb_config['pagination'], $start ),
					'PAGE_NUMBER' => sprintf( $lang['Page_of'], ( floor( $start / $pafiledb_config['pagination'] ) + 1 ), ceil( $total_file / $pafiledb_config['pagination'] ) ),
					'FILELIST' => $filelist,
					'ID' => $cat_id,
					'START' => $start,
					'SHOW_RATINGS' => ( $pa_use_ratings ) ? true : false,

					'S_ACTION_SORT' => append_sid( pa_this_mxurl( "action=$action" ) ) ) 
				);
		}
		else
		{
			$pafiledb_template->assign_vars( array( 
					'L_CATEGORY' => $lang['Category'],
					'L_RATING' => $lang['DlRating'],
					'L_DOWNLOADS' => $lang['Dls'],
					'L_DATE' => $lang['Date'],
					'L_NAME' => $lang['Name'],
					'L_FILE' => $lang['File'],
					'L_UPDATE_TIME' => $lang['Update_time'],
					'L_SCREENSHOTS' => $lang['Scrsht'],			
					'NO_FILE' => $show_file_message,
					'L_NO_FILES' => $lang['No_files'],
					'L_NO_FILES_CAT' => $lang['No_files_cat'] ) 
				);
		}
	} 

	function display_files_quickdl( $sort_method, $sort_order, $start, $show_file_message, $cat_id = false )
	{
		global $db, $pafiledb_config, $pafiledb_template, $board_config;
		global $images, $lang, $phpEx, $pafiledb_functions; 
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $phpEx;

		$filelist = false;

		if ( empty( $cat_id ) )
		{
			$cat_where = '';
		}
		else
		{
			$cat_where = "AND f1.file_catid = $cat_id";
		}

		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) AS total_comments
					FROM " . PA_FILES_TABLE . " AS f1, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c
					WHERE f1.file_id = r.votes_file(+)
					AND f1.user_id = u.user_id(+)
					AND f1.file_id = c.file_id(+)
					AND f1.file_pin = " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;

			default:
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id) AS total_comments
					FROM " . PA_FILES_TABLE . " AS f1
						LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f1.file_id = r.votes_file 
						LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
						LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f1.file_id = c.file_id
					WHERE f1.file_pin = " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;
		}

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info for this category', '', __LINE__, __FILE__, $sql );
		}

		$file_rowset = array();
		$total_file = 0;

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $this->auth[$row['file_catid']]['auth_read'] )
			{
				$file_rowset[] = $row;
			}
		}

		$db->sql_freeresult( $result );

		switch ( SQL_LAYER )
		{
			case 'oracle':
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id)
					FROM " . PA_FILES_TABLE . " AS f1, " . PA_VOTES_TABLE . " AS r, " . USERS_TABLE . " AS u, " . PA_COMMENTS_TABLE . " AS c
					WHERE f1.file_id = r.votes_file(+)
					AND f1.user_id = u.user_id(+)
					AND f1.file_id = c.file_id(+)
					AND f1.file_pin <> " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;

			default:
				$sql = "SELECT f1.*, f1.file_id, r.votes_file, AVG(r.rate_point) AS rating, COUNT(r.votes_file) AS total_votes, u.user_id, u.username, COUNT(c.comments_id)
					FROM " . PA_FILES_TABLE . " AS f1
						LEFT JOIN " . PA_VOTES_TABLE . " AS r ON f1.file_id = r.votes_file 
						LEFT JOIN " . USERS_TABLE . " AS u ON f1.user_id = u.user_id
						LEFT JOIN " . PA_COMMENTS_TABLE . " AS c ON f1.file_id = c.file_id
					WHERE f1.file_pin <> " . FILE_PINNED . "
					AND f1.file_approved = 1
					$cat_where
					GROUP BY f1.file_id 
					ORDER BY $sort_method $sort_order";
				break;
		}

		if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $pafiledb_config['pagination'], $start ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info for this category', '', __LINE__, __FILE__, $sql );
		}

		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( $this->auth[$row['file_catid']]['auth_read'] )
			{
				$file_rowset[] = $row;
			}
		}

		$db->sql_freeresult( $result );

		$where_sql = ( !empty( $cat_id ) ) ? "AND file_catid = $cat_id" : '';
		$sql = "SELECT COUNT(file_id) as total_file
			FROM " . PA_FILES_TABLE . " 
			WHERE file_approved='1'
			$where_sql";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldn\'t get number of file', '', __LINE__, __FILE__, $sql );
		}

		$row = $db->sql_fetchrow( $result );
		$db->sql_freeresult( $result );

		$total_file = $row['total_file'];
		unset( $row );

		for ( $i = 0; $i < count( $file_rowset ); $i++ )
		{ 
			// ===================================================
			// Format the date for the given file
			// ===================================================
			$date = create_date( $board_config['default_dateformat'], $file_rowset[$i]['file_time'], $board_config['board_timezone'] ); 
			$date_updated = create_date( $board_config['default_dateformat'], $file_rowset[$i]['file_update_time'], $board_config['board_timezone'] ); 
			// ===================================================
			// Get rating for the file and format it
			// ===================================================
			$rating = ( $file_rowset[$i]['rating'] != 0 ) ? round( $file_rowset[$i]['rating'], 2 ) . ' / 10' : $lang['Not_rated']; 
			// ===================================================
			// If the file is new then put a new image in front of it
			// ===================================================
			$is_new = false;
			if ( time() - ( $pafiledb_config['settings_newdays'] * 24 * 60 * 60 ) < $file_rowset[$i]['file_time'] )
			{
				$is_new = true;
			}

			$cat_name = ( empty( $cat_id ) ) ? $this->cat_rowset[$file_rowset[$i]['file_catid']]['cat_name'] : '';
			$cat_url = append_sid( pa_this_mxurl( 'action=category&cat_id=' . $file_rowset[$i]['file_catid'] ) ); 
			// ===================================================
			// Get the post icon fot this file
			// ===================================================
			if ( $file_rowset[$i]['file_pin'] != FILE_PINNED )
			{
				if ( $file_rowset[$i]['file_posticon'] == 'none' || $file_rowset[$i]['file_posticon'] == 'none.gif' )
				{
					$posticon = $module_root_path . 'pafiledb/images/spacer.gif';
				}
				else
				{
					$posticon = $module_root_path . ICONS_DIR . $file_rowset[$i]['file_posticon'];
				}
			}
			else
			{
				$posticon = $phpbb_root_path . $images['folder_sticky'];
			} 
			// ===================================================
			// Assign Vars
			// ===================================================
			$pafiledb_template->assign_block_vars( "file_rows", array( 'L_NEW_FILE' => $lang['New_file'],

					'PIN_IMAGE' => $posticon, 
					// 'FILE_NEW_IMAGE' => $phpbb_root_path . $images['pa_file_new'],
					'FILE_NEW_IMAGE' => $images['pa_file_new'],
					'HAS_SCREENSHOTS' => ( !empty( $file_rowset[$i]['file_ssurl'] ) ) ? true : false,
					'SS_AS_LINK' => ( $file_rowset[$i]['file_sshot_link'] ) ? true : false,
					'FILE_SCREENSHOT' => $file_rowset[$i]['file_ssurl'], 
					// Added by Haplo
					'FILE_SCREENSHOT_URL' => $module_root_path . 'pafiledb/images/lwin.gif',
					'FILE_NAME' => $file_rowset[$i]['file_name'],
					'FILE_DESC' => $file_rowset[$i]['file_desc'],
					'DATE' => $date,
					'UPDATED' => $date_updated,
					'RATING' => $rating,
					'FILE_DLS' => $file_rowset[$i]['file_dls'],
					'CAT_NAME' => $cat_name,
					'IS_NEW_FILE' => $is_new,

					'U_CAT' => $cat_url,
					'U_FILE' => append_sid( pa_this_mxurl( 'action=download&file_id=' . $file_rowset[$i]['file_id'], true ) ) ) 
				);
			$filelist = true;
		}

		if ( $filelist )
		{
			$action = ( empty( $cat_id ) ) ? 'viewall' : 'category&amp;cat_id=' . $cat_id;
			$pafiledb_template->assign_vars( array( 'L_CATEGORY' => $lang['Category'],
					'L_RATING' => $lang['DlRating'],
					'L_DOWNLOADS' => $lang['Dls'],
					'L_DATE' => $lang['Date'],
					'L_NAME' => $lang['Name'],
					'L_FILE' => $lang['File'],
					'L_UPDATE_TIME' => $lang['Update_time'],
					'L_SCREENSHOTS' => $lang['Scrsht'],

					'L_SELECT_SORT_METHOD' => $lang['Select_sort_method'],
					'L_ORDER' => $lang['Order'],
					'L_SORT' => $lang['Sort'],

					'L_ASC' => $lang['Sort_Ascending'],
					'L_DESC' => $lang['Sort_Descending'],

					'SORT_NAME' => ( $sort_method == 'file_name' ) ? 'selected="selected"' : '',
					'SORT_TIME' => ( $sort_method == 'file_time' ) ? 'selected="selected"' : '',
					'SORT_RATING' => ( $sort_method == 'rating' ) ? 'selected="selected"' : '',
					'SORT_DOWNLOADS' => ( $sort_method == 'file_dls' ) ? 'selected="selected"' : '',
					'SORT_UPDATE_TIME' => ( $sort_method == 'file_update_time' ) ? 'selected="selected"' : '',

					'SORT_ASC' => ( $sort_order == 'ASC' ) ? 'selected="selected"' : '',
					'SORT_DESC' => ( $sort_order == 'DESC' ) ? 'selected="selected"' : '',
					'PAGINATION' => generate_pagination( append_sid( pa_this_mxurl( "action=$action&amp;sort_method=$sort_method&amp;sort_order=$sort_order" ) ), $total_file, $pafiledb_config['pagination'], $start ),
					'PAGE_NUMBER' => sprintf( $lang['Page_of'], ( floor( $start / $pafiledb_config['pagination'] ) + 1 ), ceil( $total_file / $pafiledb_config['pagination'] ) ),
					'FILELIST' => $filelist,
					'ID' => $cat_id,
					'START' => $start,

					'S_ACTION_SORT' => append_sid( pa_this_mxurl( "action=$action" ) ) ) 
				);
		}
		else
		{
			$pafiledb_template->assign_vars( array( 'NO_FILE' => $show_file_message,
					'L_NO_FILES' => $lang['No_files'],
					'L_NO_FILES_CAT' => $lang['No_files_cat'] ) 
				);
		}
	} 
		
	// =============================================
	// Admin and mod functions
	// =============================================
	function update_add_cat( $cat_id = false )
	{
		global $db, $_POST, $lang;

		$cat_name = ( isset( $_POST['cat_name'] ) ) ? htmlspecialchars( $_POST['cat_name'] ) : '';
		$cat_desc = ( isset( $_POST['cat_desc'] ) ) ? htmlspecialchars( $_POST['cat_desc'] ) : '';
		$cat_parent = ( isset( $_POST['cat_parent'] ) ) ? intval( $_POST['cat_parent'] ) : 0;
		$cat_allow_file = ( isset( $_POST['cat_allow_file'] ) ) ? intval( $_POST['cat_allow_file'] ) : 0;

		$cat_use_comments = ( isset( $_POST['cat_allow_comments'] ) ) ? intval( $_POST['cat_allow_comments'] ) : 0;
		$cat_internal_comments = ( isset( $_POST['internal_comments'] ) ) ? intval( $_POST['internal_comments'] ) : 0;
		$cat_autogenerate_comments = ( isset( $_POST['autogenerate_comments'] ) ) ? intval( $_POST['autogenerate_comments'] ) : 0;
		$comments_forum_id = intval( $_POST['forum_id'] );

		$cat_show_pretext = ( isset( $_POST['show_pretext'] ) ) ? intval( $_POST['show_pretext'] ) : 0;
		
		$cat_use_ratings = ( isset( $_POST['cat_allow_ratings'] ) ) ? intval( $_POST['cat_allow_ratings'] ) : 0;
		
		$cat_notify = ( isset( $_POST['notify'] ) ) ? intval( $_POST['notify'] ) : 0;
		$cat_notify_group = ( isset( $_POST['notify_group'] ) ) ? intval( $_POST['notify_group'] ) : 0;

		if ( empty( $cat_name ) )
		{
			$this->error[] = $lang['Cat_name_missing'];
		}

		if ( $cat_parent )
		{
			if ( !$this->cat_rowset[$cat_parent]['cat_allow_file'] && !$cat_allow_file )
			{
				$this->error[] = $lang['Cat_conflict'];
			}
		}

		if ( sizeof( $this->error ) )
		{
			return;
		}

		$cat_name = str_replace( "\'", "''", $cat_name );
		$cat_desc = str_replace( "\'", "''", $cat_desc );

		if ( !$cat_id )
		{
			$cat_order = 0;
			if ( !empty( $this->subcat_rowset[$cat_parent] ) )
			{
				foreach( $this->subcat_rowset[$cat_parent] as $cat_data )
				{
					if ( $cat_order < $cat_data['cat_order'] )
					{
						$cat_order = $cat_data['cat_order'];
					}
				}
			}

			$cat_order += 10;

			$sql = 'INSERT INTO ' . PA_CATEGORY_TABLE . " (cat_name, cat_desc, cat_parent, cat_order, cat_allow_file, cat_allow_ratings, cat_allow_comments, internal_comments, autogenerate_comments, comments_forum_id, show_pretext, notify, notify_group) 
				VALUES('$cat_name', '$cat_desc', $cat_parent, $cat_order, $cat_allow_file, $cat_use_ratings, $cat_use_comments, $cat_internal_comments, $cat_autogenerate_comments, $comments_forum_id, $cat_show_pretext, $cat_notify, $cat_notify_group)";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t add a new category', '', __LINE__, __FILE__, $sql );
			}
		}
		else
		{
			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . " 
				SET cat_name = '$cat_name', cat_desc = '$cat_desc', cat_parent = $cat_parent, cat_allow_file = $cat_allow_file, cat_allow_ratings = $cat_use_ratings, cat_allow_comments = $cat_use_comments, internal_comments = $cat_internal_comments, autogenerate_comments = $cat_autogenerate_comments, comments_forum_id = $comments_forum_id, show_pretext = $cat_show_pretext, notify = $cat_notify, notify_group = $cat_notify_group 
				WHERE cat_id = $cat_id";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldn\'t Edit this category', '', __LINE__, __FILE__, $sql );
			}

			if ( $cat_parent != $this->cat_rowset[$cat_id]['cat_parent'] )
			{
				$this->reorder_cat( $this->cat_rowset[$cat_id]['cat_parent'] );
				$this->reorder_cat( $cat_parent );
			}
			$this->modified( true );
		}

		if ( $cat_id )
		{
			return $cat_id;
		}
		else
		{
			return $db->sql_nextid();
		}
	}

	function delete_cat( $cat_id = false )
	{
		global $db, $_POST, $lang;

		$file_to_cat_id = ( isset( $_POST['file_to_cat_id'] ) ) ? intval( $_POST['file_to_cat_id'] ) : '';
		$subcat_to_cat_id = ( isset( $_POST['subcat_to_cat_id'] ) ) ? intval( $_POST['subcat_to_cat_id'] ) : '';
		$file_mode = ( isset( $_POST['file_mode'] ) ) ? htmlspecialchars( $_POST['file_mode'] ) : 'move';
		$subcat_mode = ( isset( $_POST['subcat_mode'] ) ) ? htmlspecialchars( $_POST['subcat_mode'] ) : 'move';

		if ( empty( $cat_id ) )
		{
			$this->error[] = $lang['Cdelerror'];
		}
		else
		{
			if ( ( $file_to_cat_id == -1 || empty( $file_to_cat_id ) ) && $file_mode == 'move' )
			{
				$this->error[] = $lang['Cdelerror'];
			}

			if ( $subcat_mode == 'move' && empty( $subcat_to_cat_id ) )
			{
				$this->error[] = $lang['Cdelerror'];
			}

			if ( sizeof( $this->error ) )
			{
				return;
			}

			$sql = 'DELETE FROM ' . PA_CATEGORY_TABLE . " 
				WHERE cat_id = $cat_id";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query Info', '', __LINE__, __FILE__, $sql );
			}

			$this->reorder_cat( $this->cat_rowset[$cat_id]['cat_parent'] );

			if ( $file_mode == 'delete' )
			{
				$this->delete_files( $cat_id, 'category' );
			}
			else
			{
				$this->move_files( $cat_id, $file_to_cat_id );
			}

			if ( $subcat_mode == 'delete' )
			{
				$this->delete_subcat( $cat_id, $file_mode, $file_to_cat_id );
			}
			else
			{
				$this->move_subcat( $cat_id, $subcat_to_cat_id );
			}
			$this->modified( true );
		}
	}

	function delete_files( $id, $mode = 'file' )
	{
		global $db, $phpbb_root_path, $pafiledb_functions;

		if ( $mode == 'category' )
		{
			$file_ids = array();
			$files_data = array();

			$sql = 'SELECT file_id, unique_name, file_dir 
				FROM ' . PA_FILES_TABLE . "
				WHERE file_catid = $id";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt select files', '', __LINE__, __FILE__, $sql );
			}

			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$file_ids[] = $row['file_id'];
				$files_data[] = $row;
			}

			$where_sql = "WHERE file_catid = $id";
		}
		else
		{
			$sql = 'SELECT file_id, unique_name, file_dir 
				FROM ' . PA_FILES_TABLE . "
				WHERE file_id = $id";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt select files', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $db->sql_fetchrow( $result );

			$where_sql = "WHERE file_id = $id";
		}

		$sql = 'DELETE FROM ' . PA_FILES_TABLE . "
			$where_sql";

		unset( $where_sql );

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt delete files', '', __LINE__, __FILE__, $sql );
		}

		$where_sql = ( $mode != 'file' && !empty( $file_ids ) ) ? ' IN (' . implode( ', ', $file_ids ) . ') ' : " = $id";

		$sql = 'DELETE FROM ' . PA_CUSTOM_DATA_TABLE . "
			WHERE customdata_file$where_sql";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt delete custom data', '', __LINE__, __FILE__, $sql );
		}

		$sql = 'DELETE FROM ' . PA_MIRRORS_TABLE . "
			WHERE file_id$where_sql";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt delete mirror for this file', '', __LINE__, __FILE__, $sql );
		}

		if ( $mode == 'category' )
		{
			foreach( $files_data as $file_data )
			{
				if ( !empty( $file_data['unique_name'] ) )
				{
					$pafiledb_functions->pafiledb_unlink( $phpbb_root_path . $file_data['file_dir'] . $file_data['unique_name'] );
				}
			}
		}
		else
		{
			if ( !empty( $file_data['unique_name'] ) )
			{
				$pafiledb_functions->pafiledb_unlink( $phpbb_root_path . $file_data['file_dir'] . $file_data['unique_name'] );
			}
		}

		if ( $mode == 'file' )
		{
			$this->modified( true );
		}

		return;
	}

	function move_files( $from_cat, $to_cat )
	{
		global $db;

		$sql = 'UPDATE ' . PA_FILES_TABLE . "
			SET file_catid = $to_cat
			WHERE file_catid = $from_cat";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt move files', '', __LINE__, __FILE__, $sql );
		}

		$this->modified( true );
		return;
	}

	function delete_subcat( $cat_id, $file_mode = 'delete', $to_cat = false )
	{
		global $db;

		if ( count( $this->subcat_rowset[$cat_id] ) <= 0 )
		{
			return;
		}

		foreach( $this->subcat_rowset[$cat_id] as $sub_cat_id => $subcat_data )
		{
			$this->delete_subcat( $sub_cat_id, $file_mode, $to_cat );

			$sql = 'DELETE FROM ' . PA_CATEGORY_TABLE . " 
				WHERE cat_id = $sub_cat_id";

			if ( !( $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt Query Info', '', __LINE__, __FILE__, $sql );
			}

			if ( $file_mode == 'delete' )
			{
				$this->delete_files( $sub_cat_id, 'category' );
			}
			else
			{
				$this->move_files( $sub_cat_id, $to_cat );
			}
		}
		$this->modified( true );
		return;
	}

	function move_subcat( $from_cat, $to_cat )
	{
		global $db;

		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
			SET cat_parent = $to_cat
			WHERE cat_parent = $from_cat";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt move Sub Category', '', __LINE__, __FILE__, $sql );
		}
		$this->modified( true );
		return;
	}

	function reorder_cat( $cat_parent )
	{
		global $db;

		$sql = 'SELECT cat_id, cat_order
			FROM ' . PA_CATEGORY_TABLE . "
			WHERE cat_parent = $cat_parent
			ORDER BY cat_order ASC";

		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not get list of Categories', '', __LINE__, __FILE__, $sql );
		}

		$i = 10;
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$cat_id = $row['cat_id'];

			$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
					SET cat_order = $i
					WHERE cat_id = $cat_id";
			if ( !$db->sql_query( $sql ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not update order fields', '', __LINE__, __FILE__, $sql );
			}
			$i += 10;
		}
	}

	function order_cat( $cat_id )
	{
		global $db, $_GET;

		$move = ( isset( $_GET['move'] ) ) ? intval( $_GET['move'] ) : 15;
		$cat_parent = $this->cat_rowset[$cat_id]['cat_parent'];

		$sql = 'UPDATE ' . PA_CATEGORY_TABLE . "
				SET cat_order = cat_order + $move
				WHERE cat_id = $cat_id";

		if ( !$result = $db->sql_query( $sql ) )
		{
			mx_message_die( GENERAL_ERROR, 'Could not change category order', '', __LINE__, __FILE__, $sql );
		}

		$this->reorder_cat( $cat_parent );
		$this->init();
	}

	function update_add_file( $file_id = false )
	{
		global $db, $phpbb_root_path, $db, $_POST, $userdata, $pafiledb_config, $_FILES, $_REQUEST, $pafiledb_functions, $user_ip, $auth, $module_root_path, $pafiledb;

		$ss_upload = ( empty( $_POST['screen_shot_url'] ) ) ? true : false;
		$ss_remote_url = ( !empty( $_POST['screen_shot_url'] ) ) ? $_POST['screen_shot_url'] : '';
		$ss_local = ( $_FILES['screen_shot']['tmp_name'] !== 'none' ) ? $_FILES['screen_shot']['tmp_name'] : '';
		$ss_name = ( $_FILES['screen_shot']['name'] !== 'none' ) ? $_FILES['screen_shot']['name'] : '';
		$ss_size = ( !empty( $_FILES['screen_shot']['size'] ) ) ? $_FILES['screen_shot']['size'] : '';

		$file_upload = ( empty( $_POST['download_url'] ) ) ? true : false;
		$file_remote_url = ( !empty( $_POST['download_url'] ) ) ? $_POST['download_url'] : '';
		$file_local = ( $_FILES['userfile']['tmp_name'] !== 'none' ) ? $_FILES['userfile']['tmp_name'] : '';
		$file_realname = ( $_FILES['userfile']['name'] !== 'none' ) ? $_FILES['userfile']['name'] : '';
		$file_size = ( !empty( $_FILES['userfile']['size'] ) ) ? $_FILES['userfile']['size'] : '';
		$file_type = ( !empty( $_FILES['userfile']['type'] ) ) ? $_FILES['userfile']['type'] : '';

		$cat_id = ( isset( $_REQUEST['cat_id'] ) ) ? intval( $_REQUEST['cat_id'] ) : 0;

		$file_name = ( isset( $_POST['name'] ) ) ? htmlspecialchars( $_POST['name'] ) : '';

		$file_long_desc = ( isset( $_POST['long_desc'] ) ) ? $_POST['long_desc'] : '';

		$file_short_desc = ( isset( $_POST['short_desc'] ) ) ? $_POST['short_desc'] : ( ( !empty( $_POST['long_desc'] ) ) ? substr( $_POST['long_desc'], 0, 50 ) . '...' : '' );

		$file_author = ( isset( $_POST['author'] ) ) ? htmlspecialchars( $_POST['author'] ) : ( ( $userdata['user_id'] != ANONYMOUS ) ? $userdata['username'] : '' );

		$file_version = ( isset( $_POST['version'] ) ) ? htmlspecialchars( $_POST['version'] ) : '';

		$file_website = ( isset( $_POST['website'] ) ) ? htmlspecialchars( $_POST['website'] ) : '';
		if ( !empty( $file_website ) )
		{
			$file_website = ( !preg_match( '#^http[s]?:\/\/#i', $file_website ) ) ? 'http://' . $file_website : $file_website;
			$file_website = ( preg_match( '#^http[s]?\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $file_website ) ) ? $file_website : '';
		}

		$file_posticon = ( isset( $_POST['posticon'] ) ) ? htmlspecialchars( $_POST['posticon'] ) : '';

		$file_license = ( isset( $_POST['license'] ) ) ? intval( $_POST['license'] ) : 0;
		$file_pin = ( isset( $_POST['pin'] ) ) ? intval( $_POST['pin'] ) : 0;
		$file_ss_link = ( isset( $_POST['sshot_link'] ) ) ? intval( $_POST['sshot_link'] ) : 0; 
		$file_dls = ( isset( $_POST['file_download'] ) ) ? intval( $_POST['file_download'] ) : 0;

		$file_time = time();

		if ( $cat_id == -1 )
		{
			$this->error[] = $lang['Missing_field'];
		}

		if ( empty( $file_name ) )
		{
			$this->error[] = $lang['Missing_field'];
		}

		if ( empty( $file_long_desc ) )
		{
			$this->error[] = $lang['Missing_field'];
		}

		if ( empty( $file_remote_url ) && empty( $file_local ) && !$file_id )
		{
			$this->error[] = $lang['Missing_field'];
		}

		$forbidden_extensions = array_map( 'trim', @explode( ',', $pafiledb_config['forbidden_extensions'] ) );

		$file_extension = $pafiledb_functions->get_extension( $file_realname );

		if ( in_array( $file_extension, $forbidden_extensions ) )
		{
			$this->error[] = 'You are not allowed to upload this type of files';
		}

		if ( sizeof( $this->error ) )
		{
			return;
		}

		$physical_file_name = '';

		if ( $file_id )
		{
			$sql = 'SELECT file_dlurl, file_size, unique_name, file_dir, real_name, file_approved
				FROM ' . PA_FILES_TABLE . " 
				WHERE file_id = '$file_id'";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt query Download URL', '', __LINE__, __FILE__, $sql );
			}

			$file_data = $db->sql_fetchrow( $result );

			$db->sql_freeresult( $result );

			if ( !empty( $file_remote_url ) || !empty( $file_local ) )
			{
				if ( !empty( $file_data['unique_name'] ) )
				{
					$pafiledb_functions->pafiledb_unlink( $module_root_path . $file_data['file_dir'] . $file_data['unique_name'] );
				}
			}
			else
			{
				$file_remote_url = $file_data['file_dlurl'];
				$physical_file_name = $file_data['unique_name'];
				$file_realname = $file_data['real_name'];

				if ( empty( $file_local ) )
				{
					$file_upload = false;
				}
			}
		}

		if ( $file_upload )
		{
			$physical_file_name = $pafiledb_functions->gen_unique_name( '.' . $file_extension );

			$file_info = $pafiledb_functions->upload_file( $file_local, $physical_file_name, $file_size, $pafiledb_config['upload_dir'] );

			if ( $file_info['error'] )
			{
				mx_message_die( GENERAL_ERROR, $file_info['message'] );
			}
		}

		if ( !empty( $ss_remote_url ) || !empty( $ss_local ) )
		{
			if ( $ss_upload )
			{
				$screen_shot_info = $pafiledb_functions->upload_file( $ss_local, $ss_name, $ss_size, $pafiledb_config['screenshots_dir'] );

				if ( $screen_shot_info['error'] )
				{
					mx_message_die( GENERAL_ERROR, $screen_shot_info['message'] );
				}
				$screen_shot_url = $screen_shot_info['url'];
			}
			else
			{
				$screen_shot_url = $ss_remote_url;
			}
		} 

		if ( $pafiledb->modules[$pafiledb->module_name]->auth[$cat_id]['auth_approval'] || ( $pafiledb->modules[$pafiledb->module_name]->auth[$cat_id]['auth_mod'] && $userdata['session_logged_in'] ) )
		{
			if ( !$file_id )
			{
				$file_approved = 1;
			}
			else
			{
				$file_approved = isset( $file_data['file_approved'] ) &&  !( $pafiledb->modules[$pafiledb->module_name]->auth[$cat_id]['auth_mod'] && $userdata['session_logged_in'] ) ? $file_data['file_approved'] : 1;
			}
		}
		else 
		{
			$file_approved = 0;	
		}
				
		if ( !$file_id )
		{
			$sql = 'INSERT INTO ' . PA_FILES_TABLE . " (user_id, poster_ip, file_name, file_size, unique_name, real_name, file_dir, file_desc, file_creator, file_version, file_longdesc, file_ssurl, file_sshot_link, file_dlurl, file_time, file_update_time, file_catid, file_posticon, file_license, file_dls, file_last, file_pin, file_docsurl, file_approved)
					VALUES('{$userdata['user_id']}', '$user_ip', '" . str_replace( "\'", "''", $file_name ) . "', '$file_size', '$physical_file_name', '$file_realname', '{$pafiledb_config['upload_dir']}', '" . str_replace( "\'", "''", $file_short_desc ) . "', '" . str_replace( "\'", "''", $file_author ) . "', '" . str_replace( "\'", "''", $file_version ) . "', '" . str_replace( "\'", "''", $file_long_desc ) . "', '$screen_shot_url', '$file_ss_link', '$file_remote_url', '$file_time', '$file_time', '$cat_id', '$file_posticon', '$file_license', '$file_dls', '0', '$file_pin', '$file_website', '$file_approved')";
		}
		else
		{
			$sql = "UPDATE " . PA_FILES_TABLE . " 
				SET file_name = '" . str_replace( "\'", "''", $file_name ) . "', 
				file_size = '$file_size',
				unique_name = '$physical_file_name',
				real_name = '$file_realname',
				file_dir = '{$pafiledb_config['upload_dir']}',
				file_desc = '" . str_replace( "\'", "''", $file_short_desc ) . "', 
				file_longdesc = '" . str_replace( "\'", "''", $file_long_desc ) . "', 
				file_creator = '" . str_replace( "\'", "''", $file_author ) . "', 
				file_version = '" . str_replace( "\'", "''", $file_version ) . "', 
				file_ssurl = '$screen_shot_url', 
				file_sshot_link = '$file_ss_link',  
				file_dlurl = '$file_remote_url', 
				file_update_time = '$file_time', 
				file_catid = '$cat_id', 
				file_posticon = '$file_posticon', 
				file_license = '$file_license', 
				file_pin = '$file_pin', 
				file_docsurl = '$file_website', 
				file_dls = '$file_dls', 
				file_approved = '$file_approved' 
				WHERE file_id = '$file_id'";
		}

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Add the file information to the database', '', __LINE__, __FILE__, $sql );
		}
		$this->modified( true );

		if ( $file_id )
		{
			return $file_id;
		}
		else
		{
			return $db->sql_nextid();
		}
	}

	function mirror_add_update( $file_id, $file_upload, $file_remote_url, $file_local, $file_realname, $file_size, $file_type, $mirror_location, $mirror_id = false )
	{
		global $db, $phpbb_root_path, $db, $_POST, $userdata, $pafiledb_config, $_FILES, $_REQUEST, $pafiledb_functions; 
		// MX
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		if ( empty( $file_remote_url ) && empty( $file_local ) && !$file_id )
		{
			$this->error[] = $lang['Missing_field'];
		}

		$forbidden_extensions = array_map( 'trim', @explode( ',', $pafiledb_config['forbidden_extensions'] ) );

		$file_extension = $pafiledb_functions->get_extension( $file_realname );

		if ( in_array( $file_extension, $forbidden_extensions ) )
		{
			$this->error[] = 'You are not allowed to upload this type of files';
		}

		if ( sizeof( $this->error ) )
		{
			return;
		}

		$physical_file_name = '';

		if ( $mirror_id )
		{
			$sql = 'SELECT file_dlurl, unique_name, file_dir 
				FROM ' . PA_MIRRORS_TABLE . " 
				WHERE mirror_id = $mirror_id";

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Couldnt query Download URL', '', __LINE__, __FILE__, $sql );
			}

			$mirror_data = $db->sql_fetchrow( $result );

			$db->sql_freeresult( $result );

			if ( !empty( $file_remote_url ) || !empty( $file_local ) )
			{
				if ( !empty( $mirror_data['unique_name'] ) )
				{
					$pafiledb_functions->pafiledb_unlink( $module_root_path . $mirror_data['file_dir'] . $mirror_data['unique_name'] );
				}
			}
			else
			{
				$file_remote_url = $mirror_data['file_dlurl'];
				$physical_file_name = $mirror_data['unique_name'];
				$file_dir = $mirror_data['file_dir'];

				if ( empty( $file_local ) )
				{
					$file_upload = false;
				}
			}
		}

		if ( $file_upload )
		{
			$physical_file_name = $pafiledb_functions->gen_unique_name( '.' . $file_extension );

			$file_info = $pafiledb_functions->upload_file( $file_local, $physical_file_name, $file_size, $module_root_path . $pafiledb_config['upload_dir'] );

			if ( $file_info['error'] )
			{
				mx_message_die( GENERAL_ERROR, $file_info['message'] );
			}
		}

		if ( !$mirror_id )
		{
			$sql = 'INSERT INTO ' . PA_MIRRORS_TABLE . " (file_id, unique_name, file_dir, file_dlurl, mirror_location)
					VALUES($file_id, '$physical_file_name', '{$pafiledb_config['upload_dir']}', '$file_remote_url', '" . str_replace( "\'", "''", $mirror_location ) . "')";
		}
		else
		{
			$sql = "UPDATE " . PA_MIRRORS_TABLE . " 
				SET file_id = $file_id,
				unique_name = '$physical_file_name',
				file_dir = '{$pafiledb_config['upload_dir']}',
				file_dlurl = '$file_remote_url', 
				mirror_location = '" . str_replace( "\'", "''", $mirror_location ) . "'
				WHERE mirror_id = '$mirror_id'";
		}

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Add the file information to the database', '', __LINE__, __FILE__, $sql );
		}
	}

	function delete_mirror( $mirror_id )
	{
		global $db;

		$where_sql = ( is_array( $mirror_id ) ) ? 'IN (' . implode( ', ', $mirror_id ) . ')' : "= $mirror_id";

		$sql = 'DELETE FROM ' . PA_MIRRORS_TABLE . "
			WHERE mirror_id $where_sql";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt delete mirror for this file', '', __LINE__, __FILE__, $sql );
		}
	}

	function file_mainenance()
	{
		return false;
	}

	function file_approve( $mode = 'do_approve', $file_id )
	{
		global $db;

		$file_approved = ( $mode == 'do_approve' ) ? 1 : 0;

		$sql = 'UPDATE ' . PA_FILES_TABLE . "
			SET file_approved = $file_approved
			WHERE file_id = $file_id";

		if ( !( $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Add the file information to the database', '', __LINE__, __FILE__, $sql );
		}

		$this->modified( true );
	}

	// MX addon - PM Notify
	function pm_notify()
	{
		global $lang, $emailer, $board_config, $kb_config, $db, $module_root_path, $phpbb_root_path, $mx_root_path, $phpEx, $is_block, $page_id, $images;

		if ( $action == 2 )
		{
			$email_body = $lang['Email_body'];

			include( $phpbb_root_path . 'includes/emailer.' . $phpEx );
			$emailer = new emailer( $board_config['smtp_delivery'] );
			$email_headers = 'From: ' . $board_config['board_email'] . "\nReturn-Path: " . $board_config['board_email'] . "\n";
			$emailer->email_address( $board_config['board_email'] );
			$emailer->set_subject( $lang['New_article'] );
			$emailer->extra_headers( $email_headers );
			$emailer->msg = $email_body;

			$emailer->send();
			$emailer->reset();
		}
		else if ( $action == 1 )
		{
			$sql = "UPDATE " . USERS_TABLE . " 
		   		SET user_new_privmsg = '1', user_last_privmsg = '9999999999'
				WHERE user_id = " . $kb_config['admin_id'];

			if ( !( $result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not update users table', '', __LINE__, __FILE__, $sql );
			} 
			// added by snake for extended PM
			$approve_pm_view = "<table width=" . "100%" . " border=" . "1" . " cellspacing=" . "0" . " cellpadding=" . "0" . "><tr><td>" . $lang['Category'] . "</td><td>" . $lang['Art_action'] . "</td></tr>";

			$sql = "SELECT * FROM " . KB_ARTICLES_TABLE . " WHERE approved = '2' ORDER BY article_id DESC LIMIT 1";
			if ( !( $article_result = $db->sql_query( $sql ) ) )
			{
				mx_message_die( GENERAL_ERROR, "Could not obtain article data", '', __LINE__, __FILE__, $sql );
			}

			while ( $article = $db->sql_fetchrow( $article_result ) )
			{
				$approved_yesno = $article['approved'];
				$article_description = $article['article_description'];
				$article_cat = $article['article_category_id'];
				$bbcode_uid = $article['bbcode_uid']; // to enadbe bbcode from article html seems to wolr by default even whwn off 
				$articlebody = "[quote:$bbcode_uid]" . $article['article_body'] . "<br>[/quote:$bbcode_uid]"; // include the post for approve..  
				// type
				$type_id = $article['article_type'];
				$article_type = get_kb_type( $type_id );
				$article_date = create_date( $board_config['default_dateformat'], $article['article_date'], $board_config['board_timezone'] ); 
				// author information
				$author_id = $article['article_author_id'];

				if ( $author_id == 0 )
				{
					$author = ( $username != '' ) ? $lang['Guest'] : $article['username'];
				}
				else
				{
					$author_name = get_kb_author( $author_id );
					$temp_url = append_sid( $phpbb_root_path . "profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$author_id" );
					$author = '<a href="' . $temp_url . '" class="gen">' . $author_name . '</a>';
				}

				$article_id = $article['article_id'];
				$views = $article['views'];
				$article_title = $article['article_title'];
				$temp_url = append_sid( this_kb_mxurl( "mode=article&amp;k=$article_id" ) );
				$article = '<a href="' . $temp_url . '" class="gen">' . $article_title . '</a>';

				$approve = '';
				$delete = '';
				$category_name = '';

				$category = get_kb_cat( $article_cat );
				$category_name = $category['category_name'];

				if ( $approved_yesno == 2 )
				{ 
					// approve
					$temp_url = append_sid( PORTAL_URL . $module_root_path . "admin/admin_kb_art.$phpEx?mode=approve&amp;a=$article_id" );
					$approve = '<a href="' . $temp_url . '"><img src="' . PORTAL_URL . $images['icon_approve'] . '" border="0" alt="' . $lang['Approve'] . '"></a>';
				}
				else
				{ 
					// unapprove
					$temp_url = append_sid( PORTAL_URL . $module_root_path . "admin/admin_kb_art.$phpEx?mode=unapprove&amp;a=$article_id" );
					$unapprove = '<a href="' . $temp_url . '"><img src="' . PORTAL_URL . $images['icon_unapprove'] . '" border="0" alt="' . $lang['Un_approve'] . '"></a>';
				}
				$temp_url = append_sid( PORTAL_URL . $module_root_path . "admin/admin_kb_art.$phpEx?mode=delete&amp;a=$article_id" );
				$delete = '<a href="' . $temp_url . '"><img src="' . $phpbb_root_path . $images['icon_delpost'] . '" border="0" alt="' . $lang['Delete'] . '"></a>';
				$approve_pm_view .= "<tr><td>$category_name</td><td>$approve ' ' $delete ' ' $unapprove</td></tr>"; // the action table 
			}

			$approve_pm_view .= "</table>"; // end action table 
			
			$user_id = $kb_config['admin_id'];
			$new_article_subject = $lang['New_article'];
			$new_article = $lang['Email_body']; // original code 
			$new_article .= $articlebody; // the extended Pm body 
			$new_article .= '<p>' . $approve_pm_view; // the extended Pm body 
			$new_article .= '<br><a href=' . PORTAL_URL . $module_root_path . 'admin/admin_kb_art.' . $phpEx . '>KB Admin page</a><br>'; // the extended Pm body 
			$new_article = addslashes( $new_article );

			$privmsgs_date = date( "U" ); 
			// End Snake Extend PM Mod
			$sql = "INSERT INTO " . PRIVMSGS_TABLE . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_enable_html, privmsgs_enable_bbcode, privmsgs_enable_smilies, privmsgs_attach_sig) VALUES ('5', '" . $new_article_subject . "', '" . $user_id . "', '" . $user_id . "', '" . $privmsgs_date . "', '0', '1', '1', '0')";
			if ( !$db->sql_query( $sql ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not insert private message sent info', '', __LINE__, __FILE__, $sql );
			}
			$privmsg_sent_id = $db->sql_nextid();
			$privmsgs_text = $lang['register_pm_subject'];

			$sql = "INSERT INTO " . PRIVMSGS_TEXT_TABLE . " (privmsgs_text_id, privmsgs_bbcode_uid, privmsgs_text) VALUES ($privmsg_sent_id, '" . $bbcode_uid . "', '" . $new_article . "')"; // need to aply the bbcode_uid for bbcode to work 
			if ( !$db->sql_query( $sql ) )
			{
				mx_message_die( GENERAL_ERROR, 'Could not insert private message sent text', '', __LINE__, __FILE__, $sql );
			}
		}
		return;
	}
	
	function auth_can($cat_id)
	{
		global $lang;
		
		$this->auth_can_list = '<br />' . ( ( $this->auth[$cat_id]['auth_upload'] ) ? $lang['PA_Rules_upload_can'] : $lang['PA_Rules_upload_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->auth[$cat_id]['auth_view_file'] ) ? $lang['PA_Rules_view_file_can'] : $lang['PA_Rules_view_file_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->auth[$cat_id]['auth_edit_file'] ) ? $lang['PA_Rules_edit_file_can'] : $lang['PA_Rules_edit_file_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->auth[$cat_id]['auth_delete_file'] ) ? $lang['PA_Rules_delete_file_can'] : $lang['PA_Rules_delete_file_cannot'] ) . '<br />';
		$this->auth_can_list .= ( ( $this->comments[$cat_id]['activated'] ? ( ( $this->auth[$cat_id]['auth_view_comment'] ? $lang['PA_Rules_view_comment_can'] : $lang['PA_Rules_view_comment_cannot'] ) . '<br />') : ''));
		$this->auth_can_list .= ( ( $this->comments[$cat_id]['activated'] ? ( ( $this->auth[$cat_id]['auth_post_comment'] ? $lang['PA_Rules_post_comment_can'] : $lang['PA_Rules_post_comment_cannot'] ) . '<br />') : ''));
		$this->auth_can_list .= ( ( $this->ratings[$cat_id]['activated'] ? ( ( $this->auth[$cat_id]['auth_rate'] ? $lang['PA_Rules_rate_can'] : $lang['PA_Rules_rate_cannot'] ) . '<br />') : ''));
		$this->auth_can_list .= ( ( $this->auth[$cat_id]['auth_download'] ) ? $lang['PA_Rules_download_can'] : $lang['PA_Rules_download_cannot'] ) . '<br />';
		
		if ( $this->auth[$cat_id]['auth_mod'] )
		{
			$this->auth_can_list .= $lang['PA_Rules_moderate_can'];
		}
	}	
}


?>