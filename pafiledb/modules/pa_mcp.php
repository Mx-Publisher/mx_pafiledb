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
 *    $Id: pa_mcp.php,v 1.10 2005/12/08 15:15:13 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */

class pafiledb_mcp extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $lang, $board_config, $phpEx, $pafiledb_config, $db, $images, $debug;;
		global $_REQUEST, $phpbb_root_path, $userdata, $db, $pafiledb_functions; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx;
		
		$this->init();

		$file_id = ( isset( $_REQUEST['file_id'] ) ) ? intval( $_REQUEST['file_id'] ) : 0;
		$file_ids = ( isset( $_POST['file_ids'] ) ) ? array_map( 'intval', $_POST['file_ids'] ) : array();
		$start = ( isset( $_REQUEST['start'] ) ) ? intval( $_REQUEST['start'] ) : 0;

		$mode = ( isset( $_REQUEST['mode'] ) ) ? htmlspecialchars( $_REQUEST['mode'] ) : '';
		$mode_js = ( isset( $_REQUEST['mode_js'] ) ) ? htmlspecialchars( $_REQUEST['mode_js'] ) : '';
		$mode = ( isset( $_POST['approve'] ) ) ? 'do_approve' : $mode;
		$mode = ( isset( $_POST['unapprove'] ) ) ? 'do_unapprove' : $mode;

		if ( empty( $mode ) )
		{
			$mode = $mode_js;
			$cat_id = ( isset( $_REQUEST['cat_js_id'] ) ) ? intval( $_REQUEST['cat_js_id'] ) : intval( $_REQUEST['cat_id'] );
		}
		else
		{
			$cat_id = ( isset( $_REQUEST['cat_id'] ) ) ? intval( $_REQUEST['cat_id'] ) : 0;
		}

		$mirrors = ( isset( $_POST['mirrors'] ) ) ? true : 0; 
		// ===================================================
		// Pafiledb auth for mcp
		// ===================================================
		if ( !($this->auth[$cat_id]['auth_mod'] && $userdata['session_logged_in']) )
		{
			$message = sprintf( $lang['Sorry_auth_mcp'], $this->auth[$cat_id]['auth_mod'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		if ( isset( $_REQUEST['sort_method'] ) )
		{
			switch ( $_REQUEST['sort_method'] )
			{
				case 'file_name':
					$sort_method = 'file_name';
					break;
				case 'file_time':
					$sort_method = 'file_time';
					break;
				case 'file_dls':
					$sort_method = 'file_dls';
					break;
				case 'file_rating':
					$sort_method = 'rating';
					break;
				case 'file_update_time':
					$sort_method = 'file_update_time';
					break;
				default:
					$sort_method = $pafiledb_config['sort_method'];
			}
		}
		else
		{
			$sort_method = $pafiledb_config['sort_method'];
		}

		if ( isset( $_REQUEST['sort_order'] ) )
		{
			switch ( $_REQUEST['sort_order'] )
			{
				case 'ASC':
					$sort_order = 'ASC';
					break;
				case 'DESC':
					$sort_order = 'DESC';
					break;
				default:
					$sort_order = $pafiledb_config['sort_order'];
			}
		}
		else
		{
			$sort_order = $pafiledb_config['sort_order'];
		}

		$s_file_actions = array( 'approved' => $lang['Approved_files'],
			'broken' => $lang['Broken_files'],
			'file_cat' => $lang['File_cat'],
			'all_file' => $lang['All_files'] );

		switch ( $mode )
		{
			case '':
			case 'approved':
			case 'broken':
			case 'do_approve':
			case 'do_unapprove':
			// case 'delete':
			case 'file_cat':
			case 'all_file':
			default:
				$template_file = 'pa_mcp.tpl';
				$l_title = $lang['MCP_title'];
				$l_explain = $lang['MCP_title_explain']; 
				// $s_hidden_fields = '<input type="hidden" name="mode" value="add">';
				break;
		}

		if ( $mode == 'do_approve' || $mode == 'do_unapprove' )
		{
			if ( $this->auth[$cat_id]['auth_mod'] || $userdata['user_level'] == ADMIN )
			{
				$mode_temp = $mode == 'do_approve' ? 'approved' : 'unapproved';
				
				if ( is_array( $file_ids ) && !empty( $file_ids ) )
				{
					foreach( $file_ids as $temp_file_id )
					{
						$this->file_approve( $mode, $temp_file_id );
						
						if ( $pafiledb_config['notify'] )
						{
							$pa_pm = array();
							
							//
							// Populate the pa_pm variable
							//
							$pa_pm = pa_get_pm_data( $temp_file_id );
						
							//
							// Compose post header
							//
							$pa_message_tmp = pa_compose_pm( $pa_pm );
							$pa_message = $pa_message_tmp['message'];
							$pa_update_message = $pa_message_tmp['update_message'];
											
							//
							// PM notify
							//
							$pa_admins = pa_get_admins( true );		
							
							for ($i = 0; $i < count($pa_admins) || $i < 10; $ii)
							{
								pa_notify( $pafiledb_config['notify'], $pa_message, $pa_admins[$i]['user_id'], $pa_pm['file_editor_id'], $info = $mode_temp );
							}	
						}									
					}
				}
				else
				{
					$this->file_approve( $mode, $file_id );
					
					if ( $pafiledb_config['notify'] )
					{
						$pa_pm = array();
						
						//
						// Populate the pa_pm variable
						//
						$pa_pm = pa_get_pm_data( $file_id );
					
						//
						// Compose post header
						//
						$pa_message_tmp = pa_compose_pm( $pa_pm );
						$pa_message = $pa_message_tmp['message'];
						$pa_update_message = $pa_message_tmp['update_message'];
										
						//
						// PM notify
						//
						$pa_admins = pa_get_admins( true );	
						
						for ($i = 0; $i < count($pa_admins) || $i < 10; $ii)
						{
							pa_notify( $pafiledb_config['notify'], $pa_message, $pa_admins[$i]['user_id'], $pa_pm['file_editor_id'], $info = $mode_temp );
						}	
					}			
				}
				
				$this->_pafiledb();
			}
			else
			{
				$message = sprintf( $lang['Sorry_auth_approve'], $this->auth[$cat_id]['auth_mod'] );
				mx_message_die( GENERAL_MESSAGE, $message );
			}
		}

		$pafiledb_template->set_filenames( array( 'admin' => $template_file ) );

		$s_hidden_fields = '<input type="hidden" name="cat_id" value="' . $cat_id . '">';

		$pafiledb_template->assign_vars( array( 'L_INDEX' => "<<",
				'U_INDEX' => append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD_HOME' => append_sid( pa_this_mxurl() ),
				'U_DOWNLOAD' => append_sid( pa_this_mxurl() ),
				'DOWNLOAD' => $pafiledb_config['module_name'],
				'L_MCP_TITLE' => $l_title,
				'L_MCP_EXPLAIN' => $l_explain,
				'L_ADD_FILE' => $lang['Afiletitle'],

				'S_HIDDEN_FIELDS' => $s_hidden_fields,
				'S_FILE_ACTION' => append_sid( pa_this_mxurl( "action=mcp" ) ) ) 
			);

		if ( in_array( $mode, array( '', 'approved', 'broken', 'do_approve', 'do_unapprove', 'file_cat', 'all_file' ) ) )
		{
			$mode = ( in_array( $mode, array( 'do_approve', 'do_unapprove' ) ) ) ? '' : $mode;

			if ( $mode != 'approved' && $mode != 'broken' )
			{
				// $where_sql = ($mode == 'file_cat') ? "AND file_catid = '$cat_id'" : '';
				$where_sql = "AND file_catid = '$cat_id'" ;
				$sql = "SELECT file_name, file_approved, file_id, file_broken
					FROM " . PA_FILES_TABLE . " as f1
					WHERE file_approved = '1'
					$where_sql
					ORDER BY file_time DESC";

				if ( $mode == '' || $mode == 'file_cat' || $mode == 'all_file' )
				{
					if ( ( !$result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
					}

					$total_files = $db->sql_numrows( $result );
				}

				if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $pafiledb_config['pagination'], $start ) ) )
				{
					mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
				}
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$all_file_rowset[] = $row;
				}
			}

			if ( $mode == '' || $mode == 'approved' || $mode == 'broken' || $mode == 'file_cat' || $mode == 'all_file' )
			{
				if ( $mode == '' )
				{
					$limit = 5;
					$temp_start = 0;
				}
				else
				{
					$limit = $pafiledb_config['pagination'];
					$temp_start = $start;
				}

				if ( $mode == '' || $mode == 'approved' )
				{
					$sql = "SELECT file_name, file_approved, file_id, file_broken
						FROM " . PA_FILES_TABLE . "
						WHERE file_approved = '0'
						AND file_catid = '$cat_id'
						ORDER BY file_time DESC";

					if ( $mode == 'approved' )
					{
						if ( ( !$result = $db->sql_query( $sql ) ) )
						{
							mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
						}

						$total_files = $db->sql_numrows( $result );
					}

					if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $limit, $temp_start ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
					}

					while ( $row = $db->sql_fetchrow( $result ) )
					{
						$approved_file_rowset[] = $row;
					}
				}

				if ( $mode == '' || $mode == 'broken' )
				{
					$sql = "SELECT file_name, file_approved, file_id, file_broken
						FROM " . PA_FILES_TABLE . "
						WHERE file_broken = '1'
						AND file_catid = '$cat_id'
						ORDER BY file_time DESC";

					if ( $mode == 'broken' )
					{
						if ( ( !$result = $db->sql_query( $sql ) ) )
						{
							mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
						}

						$total_files = $db->sql_numrows( $result );
					}

					if ( !( $result = $pafiledb_functions->sql_query_limit( $sql, $limit, $temp_start ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldn\'t get file info', '', __LINE__, __FILE__, $sql );
					}

					while ( $row = $db->sql_fetchrow( $result ) )
					{
						$broken_file_rowset[] = $row;
					}
				}

				if ( $mode == '' )
				{
					$global_array = array( 0 => array( 'lang_var' => $lang['Approved_files'],
							'row_set' => $approved_file_rowset,
							'approval' => 'approve' ),
						1 => array( 'lang_var' => $lang['Broken_files'],
							'row_set' => $broken_file_rowset,
							'approval' => 'both' ),
						2 => array( 'lang_var' => $lang['All_files'],
							'row_set' => $all_file_rowset,
							'approval' => 'unapprove' ) );
				}
				elseif ( $mode == 'all_file' )
				{
					$global_array = array( 0 => array( 'lang_var' => $lang['Approved_files'],
							'row_set' => $approved_file_rowset,
							'approval' => 'approve' ),
						1 => array( 'lang_var' => $lang['Broken_files'],
							'row_set' => $broken_file_rowset,
							'approval' => 'both' ),
						2 => array( 'lang_var' => $lang['All_files'],
							'row_set' => $all_file_rowset,
							'approval' => 'unapprove' ) );
				}
				elseif ( $mode == 'file_cat' )
				{
					$global_array = array( 0 => array( 'lang_var' => $lang['All_files'],
							'row_set' => $all_file_rowset,
							'approval' => 'unapprove' ) );
				}
				elseif ( $mode == 'approved' )
				{
					$global_array = array( 0 => array( 'lang_var' => $lang['Approved_files'],
							'row_set' => $approved_file_rowset,
							'approval' => 'approve' ) );
				}
				elseif ( $mode == 'broken' )
				{
					$global_array = array( 0 => array( 'lang_var' => $lang['Broken_files'],
							'row_set' => $broken_file_rowset,
							'approval' => 'both' ) );
				}
			}

			$s_file_list = '';
			foreach( $s_file_actions as $file_mode => $lang_var )
			{
				$s = '';
				if ( $mode == $file_mode )
				{
					$s = ' selected="selected"';
				}
				$s_file_list .= '<option value="' . $file_mode . '"' . $s . '>' . $lang_var . '</option>';
			}

			$cat_list = '<select name="cat_js_id">';
			if ( !$this->cat_rowset[$cat_id]['cat_parent'] )
			{
				$cat_list .= '<option value="0" selected>' . $lang['None'] . '</option>\n';
			}
			else
			{
				$cat_list .= '<option value="0">' . $lang['None'] . '</option>\n';
			}
			$cat_list .= $this->generate_jumpbox( 0, 0, array( $cat_id => 1 ), true );
			$cat_list .= '</select>';

			$pafiledb_template->assign_vars( array( 
				'L_EDIT' => $lang['Editfile'],
				'L_DELETE' => $lang['Delete'],
				'L_CATEGORY' => $lang['Category'],
				'L_MODE' => $lang['View'],
				'L_GO' => $lang['Go'],
				'L_DELETE_FILE' => $lang['Delete_selected'],
				'L_APPROVE' => $lang['Approve'],
				'L_UNAPPROVE' => $lang['Unapprove'],
				'L_APPROVE_FILE' => $lang['Approve_selected'],
				'L_UNAPPROVE_FILE' => $lang['Unapprove_selected'],
				'L_NO_FILES' => $lang['No_file'],

				'PAGINATION' => generate_pagination( append_sid( pa_this_mxurl( "action=mcp&amp;mode=$mode&amp;sort_method=$sort_method&amp;sort_order=$sort_order&cat_id=$cat_id" ) ), $total_files, $pafiledb_config['pagination'], $start ),
				'PAGE_NUMBER' => sprintf( $lang['Page_of'], ( floor( $start / $pafiledb_config['pagination'] ) + 1 ), ceil( $total_files / $pafiledb_config['pagination'] ) ),

				'S_CAT_LIST' => $cat_list,
				'S_MODE_SELECT' => $s_file_list ) 
			);

			foreach( $global_array as $files_data )
			{
				$approve = false;
				$unapprove = false;
				if ( $files_data['approval'] == 'both' )
				{
					$approve = $unapprove = true;
				}
				elseif ( $files_data['approval'] == 'approve' )
				{
					$approve = true;
				}
				elseif ( $files_data['approval'] == 'unapprove' )
				{
					$unapprove = true;
				}

				$pafiledb_template->assign_block_vars( 'file_mode', array( 
					'L_FILE_MODE' => $files_data['lang_var'],
					'DATA' => ( isset( $files_data['row_set'] ) ) ? true : false,
					'APPROVE' => $approve,
					'UNAPPROVE' => $unapprove ) 
				);

				if ( isset( $files_data['row_set'] ) )
				{
					$i = $start + 1;
					foreach( $files_data['row_set'] as $file_data )
					{
						$approve_mode = ( $file_data['file_approved'] ) ? 'do_unapprove' : 'do_approve';
						$pafiledb_template->assign_block_vars( 'file_mode.file_row', array( 
							'FILE_NAME' => $file_data['file_name'],
							'FILE_NUMBER' => $i++,
							'FILE_ID' => $file_data['file_id'],
							'U_FILE_EDIT' => append_sid( pa_this_mxurl( "action=user_upload&amp;mode=edit&amp;file_id={$file_data['file_id']}" ) ),
							'U_FILE_DELETE' => append_sid( pa_this_mxurl( "action=user_upload&amp;do=delete&amp;file_id={$file_data['file_id']}" ) ),
							'U_FILE_APPROVE' => append_sid( pa_this_mxurl( "action=mcp&amp;mode=$approve_mode&amp;cat_id=$cat_id&amp;file_id={$file_data['file_id']}" ) ),
							'L_APPROVE' => ( $file_data['file_approved'] ) ? $lang['Unapprove'] : $lang['Approve'] ) 
						);
					}
				}
			}
		}

		$pafiledb_template->assign_vars( array( 'ERROR' => ( sizeof( $this->error ) ) ? implode( '<br />', $this->error ) : '' ) );

		$this->display( $lang['MCP'], $template_file );
		$this->_pafiledb();
	}
}

?>