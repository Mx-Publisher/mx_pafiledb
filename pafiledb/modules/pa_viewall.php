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
 *    $Id: pa_viewall.php,v 1.12 2005/12/08 15:15:13 jonohlsson Exp $
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

class pafiledb_viewall extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $lang, $phpEx, $pafiledb_config, $_REQUEST, $userdata;

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

		if ( !$pafiledb_config['settings_viewall'] )
		{
			mx_message_die( GENERAL_MESSAGE, $lang['viewall_disabled'] );
		}
		elseif ( !$this->auth_global['auth_viewall'] )
		{
			if ( !$userdata['session_logged_in'] )
			{
				redirect( append_sid( "login.$phpEx?redirect=dload.$phpEx?action=viewall", true ) );
			}

			$message = sprintf( $lang['Sorry_auth_viewall'], $this->auth_global['auth_viewall_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		$pafiledb_template->assign_vars( array( 
				'L_VIEWALL' => $lang['Viewall'],
				'L_INDEX' => "<<",

				'U_INDEX' => append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD' => append_sid( pa_this_mxurl() ),

				'DOWNLOAD' => $pafiledb_config['module_name'] ) 
			);

		$this->display_files( $sort_method, $sort_order, $start, true );

		// ===================================================
		// assign var for navigation
		// ===================================================
		
		$this->display( $lang['Download'], 'pa_viewall_body.tpl' );
	}
}

?>