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
 *    $Id: pa_category.php,v 1.11 2005/12/08 15:15:13 jonohlsson Exp $
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

class pafiledb_category extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $lang, $phpEx, $pafiledb_config, $_REQUEST, $userdata; 
		global $mx_root_path, $module_root_path, $is_block, $phpEx, $mx_request_vars; 
		
		// =======================================================
		// Request vars
		// =======================================================
		$start = $mx_request_vars->request('start', MX_TYPE_INT, 0);
		$cat_id = $mx_request_vars->request('cat_id', MX_TYPE_INT, '');
				
		if ( empty( $cat_id ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['Cat_not_exist'] );
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
		
		// =======================================================
		// If user not allowed to view file listing (read) and there is no sub Category
		// or the user is not allowed to view these category we gave him a nice message.
		// =======================================================
		$show_category = false;
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			foreach( $this->subcat_rowset[$cat_id] as $sub_cat_id => $sub_cat_row )
			{
				if ( $this->auth[$sub_cat_id]['auth_view'] )
				{
					$show_category = true;
					break;
				}
			}
		}

		if ( ( !$this->auth[$cat_id]['auth_read'] ) && ( !$show_category ) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				// mx_redirect(append_sid($mx_root_path . "login.$phpEx?redirect=". pa_this_mxurl("action=category&cat_id=" . $cat_id, true), true));
			}

			$message = sprintf( $lang['Sorry_auth_view'], $this->auth[$cat_id]['auth_read_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		if ( !isset( $this->cat_rowset[$cat_id] ) )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['Cat_not_exist'] );
		} 
		
		$pafiledb_template->assign_vars( array( 
				'L_INDEX' => "<<",

				'U_INDEX' => append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD' => append_sid( pa_this_mxurl() ),
				
				'DOWNLOAD' => $pafiledb_config['module_name'] ) 
			);

		$no_file_message = true;
		$filelist = false;

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $cat_id );
				
		if ( isset( $this->subcat_rowset[$cat_id] ) )
		{
			$no_file_message = false;

			$this->display_categories( $cat_id );
		}

		$this->display_files( $sort_method, $sort_order, $start, $no_file_message, $cat_id );
		
		//
		// User authorisation levels output
		//
		$this->auth_can($cat_id);		

		$this->display( $lang['Download'], 'pa_category_body.tpl' );
	}
}

?>