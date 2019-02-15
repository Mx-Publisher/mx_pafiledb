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
 *    $Id: admin_pa_license.php,v 1.13 2005/12/11 16:19:20 jonohlsson Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */
 
if ( file_exists( './../viewtopic.php' ) )
{
	//
	// phpBB MOD mode
	//	
	define( 'IN_PHPBB', 1 );
	define( 'IN_PORTAL', 1 );
	define( 'MXBB_MODULE', false );
	
	$phpbb_root_path = $module_root_path = $mx_root_path = './../';
	require_once( $phpbb_root_path . 'extension.inc' );

	if ( !empty( $setmodules ) )
	{
		include_once( $module_root_path . 'pafiledb/includes/pafiledb_constants.' . $phpEx );
		$file = basename( __FILE__ );
		$module['pafileDB_Download']['4_License_title'] = $file;
		return;
	}	

	// Load default header
	
	$no_page_header = true;

	require( './pagestart.' . $phpEx );
	
	include_once( $phpbb_root_path . 'includes/functions_admin.'.$phpEx );
	include_once( $phpbb_root_path . 'pafiledb/pafiledb_common.' . $phpEx );
}
else 
{
	define( 'IN_PORTAL', 1 );
	define( 'MXBB_MODULE', true );
	
	if ( !empty( $setmodules ) )
	{
		$mx_root_path = './../';
		$module_root_path = './../modules/mx_pafiledb/';
		require_once( $mx_root_path . 'extension.inc' );
		include_once( $module_root_path . 'pafiledb/includes/pafiledb_constants.' . $phpEx );
				
		$file = basename( __FILE__ );
		$module['pafileDB_Download']['4_License_title'] = 'modules/mx_pafiledb/admin/' . $file;
		return;
	}	
	
	$no_page_header = true;
	$module_root_path = './../';
	$mx_root_path = './../../../';
	
	define( 'MXBB_27x', file_exists( $mx_root_path . 'mx_login.php' ) );
	
	require( $mx_root_path . 'extension.inc' );
	require( $mx_root_path . 'admin/pagestart.' . $phpEx );
	
	include_once( $module_root_path . 'pafiledb/pafiledb_common.' . $phpEx );
} 

if ( isset( $HTTP_GET_VARS['license'] ) || isset( $HTTP_POST_VARS['license'] ) )
{
	$license = ( isset( $HTTP_POST_VARS['license'] ) ) ? $HTTP_POST_VARS['license'] : $HTTP_GET_VARS['license'];

	switch ( $license )
	{
		case 'add':
			{
				$template->set_filenames( array( 
						/* - orig
			    'admin' => 'admin/pa_admin_license_add.tpl')
			*/
						// MX
						'admin' => 'admin/pa_admin_license_add.tpl' ) 
					);

				if ( isset( $HTTP_GET_VARS['add'] ) || isset( $HTTP_POST_VARS['add'] ) )
				{
					$add = ( isset( $HTTP_GET_VARS['add'] ) ) ? $HTTP_GET_VARS['add'] : $HTTP_POST_VARS['add'];
				}

				if ( $add == 'do' )
				{
					if ( isset( $HTTP_GET_VARS['form'] ) || isset( $HTTP_POST_VARS['form'] ) )
					{
						$form = ( isset( $HTTP_GET_VARS['form'] ) ) ? $HTTP_GET_VARS['form'] : $HTTP_POST_VARS['form'];
					} 
					// $form['text'] = str_replace("\n", "<br>", $form['text']);
					$sql = "INSERT INTO " . PA_LICENSE_TABLE . " VALUES('NULL', '" . $form['name'] . "', '" . $form['text'] . "')";

					if ( !( $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
					}

					$message = $lang['Licenseadded'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_license.$phpEx" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_admin_index'], '<a href="' . append_sid( $mx_root_path . "admin/index.$phpEx?pane=right" ) . '">', '</a>' );

					mx_message_die( GENERAL_MESSAGE, $message );
				}

				if ( empty( $add ) )
				{
					$template->assign_vars( array( 'S_ADD_LIC_ACTION' => append_sid( "admin_pa_license.$phpEx" ),
							'L_ALICENSETITLE' => $lang['Alicensetitle'],
							'L_LICENSEEXPLAIN' => $lang['Licenseexplain'],
							'L_LNAME' => $lang['Lname'],
							'L_LTEXT' => $lang['Ltext'] ) 
						);
				}

				break;
			}

		case 'edit':
			{
				$template->set_filenames( array( 
						/* - orig
				'admin' => 'admin/pa_admin_license_edit.tpl')
			*/
						// MX
						'admin' => 'admin/pa_admin_license_edit.tpl' ) 
					);

				if ( isset( $HTTP_GET_VARS['edit'] ) || isset( $HTTP_POST_VARS['edit'] ) )
				{
					$edit = ( isset( $HTTP_GET_VARS['edit'] ) ) ? $HTTP_GET_VARS['edit'] : $HTTP_POST_VARS['edit'];
				}

				if ( $edit == 'do' )
				{
					if ( isset( $HTTP_GET_VARS['form'] ) || isset( $HTTP_POST_VARS['form'] ) )
					{
						$form = ( isset( $HTTP_GET_VARS['form'] ) ) ? $HTTP_GET_VARS['form'] : $HTTP_POST_VARS['form'];
					}

					if ( isset( $HTTP_GET_VARS['id'] ) || isset( $HTTP_POST_VARS['id'] ) )
					{
						$id = ( isset( $HTTP_GET_VARS['id'] ) ) ? intval( $HTTP_GET_VARS['id'] ) : intval( $HTTP_POST_VARS['id'] );
					} 
					// $form['text'] = str_replace("\n", "<br>", $form['text']);
					$sql = "UPDATE " . PA_LICENSE_TABLE . " SET license_name = '" . $form['name'] . "', license_text = '" . $form['text'] . "' WHERE license_id = '" . $id . "'";

					if ( !( $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
					}

					$message = $lang['Licenseedited'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_license.$phpEx" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_admin_index'], '<a href="' . append_sid( $mx_root_path . "admin/index.$phpEx?pane=right" ) . '">', '</a>' );

					mx_message_die( GENERAL_MESSAGE, $message );
				}

				if ( $edit == 'form' )
				{
					if ( isset( $HTTP_GET_VARS['select'] ) || isset( $HTTP_POST_VARS['select'] ) )
					{
						$select = ( isset( $HTTP_GET_VARS['select'] ) ) ? $HTTP_GET_VARS['select'] : $HTTP_POST_VARS['select'];
					}

					$sql = "SELECT * FROM " . PA_LICENSE_TABLE . " WHERE license_id = '" . $select . "'";

					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
					}

					$license = $db->sql_fetchrow( $result );

					$text = str_replace( "<br>", "\n", $license['license_text'] );

					$template->assign_block_vars( "license_form", array() );

					$template->assign_vars( array( 'S_EDIT_LIC_ACTION' => append_sid( "admin_pa_license.$phpEx" ),
							'L_ELICENSETITLE' => $lang['Elicensetitle'],
							'L_LICENSEEXPLAIN' => $lang['Licenseexplain'],
							'L_LNAME' => $lang['Lname'],
							'LICENSE_NAME' => $license['license_name'],
							'TEXT' => $text,
							'SELECT' => $select,
							'L_LTEXT' => $lang['Ltext'] ) 
						);
				}

				if ( empty( $edit ) )
				{
					$sql = "SELECT * FROM " . PA_LICENSE_TABLE;

					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
					}

					while ( $license = $db->sql_fetchrow( $result ) )
					{
						$row .= '<tr><td width="3%" class="row1" align="center" valign="middle"><input type="radio" name="select" value="' . $license['license_id'] . '"></td><td width="97%" class="row1">' . $license['license_name'] . '</td></tr>';
					}

					$template->assign_block_vars( "license", array() );

					$template->assign_vars( array( 'S_EDIT_LIC_ACTION' => append_sid( "admin_pa_license.$phpEx" ),
							'L_ELICENSETITLE' => $lang['Elicensetitle'],
							'L_LICENSEEXPLAIN' => $lang['Licenseexplain'],
							'ROW' => $row ) 
						);
				}

				break;
			}

		case 'delete':
			{
				$template->set_filenames( array( 
						/* - orig
				'admin' => 'admin/pa_admin_license_delete.tpl')
			*/
						// MX
						'admin' => 'admin/pa_admin_license_delete.tpl' ) 
					);

				if ( isset( $HTTP_GET_VARS['delete'] ) || isset( $HTTP_POST_VARS['delete'] ) )
				{
					$delete = ( isset( $HTTP_GET_VARS['delete'] ) ) ? $HTTP_GET_VARS['delete'] : $HTTP_POST_VARS['delete'];
				}

				if ( $delete == 'do' )
				{
					if ( isset( $HTTP_GET_VARS['select'] ) || isset( $HTTP_POST_VARS['select'] ) )
					{
						$select = ( isset( $HTTP_GET_VARS['select'] ) ) ? $HTTP_GET_VARS['select'] : $HTTP_POST_VARS['select'];
					}

					if ( empty( $select ) )
					{
						$message = $lang['lderror'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_license.$phpEx?license=delete" ) . '">', '</a>' );

						mx_message_die( GENERAL_MESSAGE, $message );
					}
					else
					{
						foreach ( $select as $key => $value )
						{
							$sql = "DELETE FROM " . PA_LICENSE_TABLE . " WHERE license_id = '" . $key . "'";

							if ( !( $db->sql_query( $sql ) ) )
							{
								mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
							}

							$sql = "UPDATE " . PA_FILES_TABLE . " SET file_license = '0' WHERE file_license = '$key'";

							if ( !( $db->sql_query( $sql ) ) )
							{
								mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
							}
						}

						$message = $lang['Ldeleted'] . '<br /><br />' . sprintf( $lang['Click_return'], '<a href="' . append_sid( "admin_pa_license.$phpEx" ) . '">', '</a>' ) . '<br /><br />' . sprintf( $lang['Click_return_admin_index'], '<a href="' . append_sid( $mx_root_path . "admin/index.$phpEx?pane=right" ) . '">', '</a>' );

						mx_message_die( GENERAL_MESSAGE, $message );
					}
				}

				if ( empty( $delete ) )
				{
					$sql = "SELECT * FROM " . PA_LICENSE_TABLE;

					if ( !( $result = $db->sql_query( $sql ) ) )
					{
						mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
					}

					while ( $license = $db->sql_fetchrow( $result ) )
					{
						$row .= '<tr><td width="3%" class="row1" align="center" valign="middle"><input type="checkbox" name="select[' . $license['license_id'] . ']" value="yes"></td><td width="97%" class="row1">' . $license['license_name'] . '</td></tr>';
					}

					$template->assign_vars( array( 'S_DELETE_LIC_ACTION' => append_sid( "admin_pa_license.$phpEx" ),
							'L_DLICENSETITLE' => $lang['Dlicensetitle'],
							'L_LICENSEEXPLAIN' => $lang['Licenseexplain'],
							'ROW' => $row ) 
						);
				}

				break;
			}
	}
}
else
{ 
	// main
	$template->set_filenames( array( 
			/* - orig
				'admin' => 'admin/pa_admin_license_delete.tpl')
			*/
			// MX
			'admin' => 'admin/pa_admin_license.tpl' ) 
		);

	$sql = "SELECT * FROM " . PA_LICENSE_TABLE;

	if ( !( $result = $db->sql_query( $sql ) ) )
	{
		mx_message_die( GENERAL_ERROR, 'Couldnt Query info', '', __LINE__, __FILE__, $sql );
	}

	while ( $license = $db->sql_fetchrow( $result ) )
	{
		$row .= '<tr><td width="80%" class="row1" align="center">' . $license['license_name'] . '</td></tr>';
	}

	$template->assign_vars( array( 'S_DELETE_LIC_ACTION' => append_sid( "admin_pa_license.$phpEx" ),
			'L_LICENSETITLE' => $lang['License_title'],
			'L_ALICENSETITLE' => $lang['Alicensetitle'],
			'L_ELICENSETITLE' => $lang['Elicensetitle'],
			'L_DLICENSETITLE' => $lang['Dlicensetitle'],
			'L_LICENSEEXPLAIN' => $lang['Licenseexplain'],
			'ROW' => $row ) 
		);
}

// Output
include( $mx_root_path . 'admin/page_header_admin.' . $phpEx );
$template->pparse( 'admin' );
include( $mx_root_path . 'admin/page_footer_admin.' . $phpEx );

?>