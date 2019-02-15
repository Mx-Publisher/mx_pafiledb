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
 *    $Id: pa_quickdl.php,v 1.5 2005/12/08 15:15:13 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
class pafiledb_quickdl extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $lang, $phpEx, $pafiledb_config, $_REQUEST, $userdata; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx, $pafiledb_quickdl, $page_id; 
		
		// =======================================================
		// Get the id
		// =======================================================
		$pa_mapping_list = !empty($pafiledb_quickdl['pa_mapping']) ? unserialize( stripslashes( $pafiledb_quickdl['pa_mapping'] )) : array();

		//
		// Setup mappings
		//
		for ( $i = 0; $i < count( $pa_mapping_list ); $i++ )	
		{
			$pa_get_dynamic[$pa_mapping_list[$i]['map_cat_id']] = $pa_mapping_list[$i]['map_dyn_id'];
			$pa_get_cat[$pa_mapping_list[$i]['map_dyn_id']] = $pa_mapping_list[$i]['map_cat_id'];
		}

		//
		// Get pafiledb cat id - either from cat_id (GET), mapping (GET) or default cat_id (PAR)
		//
		$pa_cat_id = isset( $_REQUEST['cat_id'] ) ? intval( $_REQUEST['cat_id'] ) : ( isset( $_REQUEST['dynamic_block'] ) && !empty( $pa_get_cat[$_REQUEST['dynamic_block']] ) ? intval( $pa_get_cat[$_REQUEST['dynamic_block']] ) : intval( $pafiledb_quickdl['pa_quick_cat'] ) );
		
		/*	
		if ( isset( $_REQUEST['dynamic_block'] ) )
		{
			for ( $i = 0; $i < count( $pa_mapping_list ); $i++ )	
			{
				if ( $pa_mapping_list[$i]['map_dyn_id'] == intval( $_REQUEST['dynamic_block'] ) )
				{
					if ( get_page_id( $pa_mapping_list[$i]['map_dyn_id'] ) == intval( $_REQUEST['page'] ) )
					{
						$map_cat_id = $pa_mapping_list[$i]['map_cat_id'];
					}
				}
			}
		}
		
		if ( empty( $map_cat_id ) )
		{
			$map_cat_id = intval( $pafiledb_quickdl['pa_quick_cat'] );
		}

		$pa_cat_id = isset( $_REQUEST['cat_id'] ) ? intval( $_REQUEST['cat_id'] ) : $map_cat_id;
		*/
		
		$start = ( isset( $_REQUEST['start'] ) ) ? intval( $_REQUEST['start'] ) : 0;

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
		// =======================================================
		// If user not allowed to view file listing (read) and there is no sub Category
		// or the user is not allowed to view these category we gave him a nice message.
		// =======================================================
		$show_category = false;
		if ( isset( $this->subcat_rowset[$pa_cat_id] ) )
		{
			foreach( $this->subcat_rowset[$pa_cat_id] as $sub_cat_id => $sub_cat_row )
			{
				if ( $this->auth[$sub_cat_id]['auth_view'] )
				{
					$show_category = true;
					break;
				}
			}
		}

		if ( ( !$this->auth[$pa_cat_id]['auth_read'] ) && ( !$show_category ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(append_sid($mx_root_path . "login.$phpEx?redirect=".pa_this_mxurl("action=category&cat_id=" . $cat_id), true));
			}

			$message = sprintf( $lang['Sorry_auth_view'], $this->auth[$pa_cat_id]['auth_read_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		if ( !isset( $this->cat_rowset[$pa_cat_id] ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['Cat_not_exist'] );
		} 

		$quickdl = $this->cat_rowset[$pa_cat_id];
		
		$quickdl_back = '';
		if ( $pa_cat_id != $pafiledb_quickdl['pa_quick_cat'] )
		{
			$quickdl_back = '&laquo; ';
		}
		
		$map_xtra = !empty( $pa_get_dynamic[$pafiledb_quickdl['pa_quick_cat']] ) ? '&dynamic_block=' . $pa_get_dynamic[$pafiledb_quickdl['pa_quick_cat']]  : '';

		$pafiledb_template->assign_vars( array( 
				'U_DOWNLOAD' => append_sid( pa_this_mxurl( 'action=quickdl&cat_id=' . $pafiledb_quickdl['pa_quick_cat'] . $map_xtra ) ),
				'DOWNLOAD' => $quickdl['cat_name'], 
				'BACK' => $quickdl_back 
				) 
		);
		
		$no_file_message = true;

		$filelist = false;

		if ( isset( $this->subcat_rowset[$pa_cat_id] ) )
		{
			$no_file_message = false;

			$this->display_categories_quickdl( $pa_cat_id, $pa_get_dynamic );
		}

		$this->display_files_quickdl( $sort_method, $sort_order, $start, $no_file_message, $pa_cat_id );

		$this->display( $lang['Download'], 'pa_quickdl_cat_body.tpl' );
	}
}

?>