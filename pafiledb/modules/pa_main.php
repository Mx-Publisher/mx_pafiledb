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
 *    $Id: pa_main.php,v 1.11 2005/12/08 15:15:13 jonohlsson Exp $
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
  Sub category counting bug fix by Kron
  Please read the license included with this script for more information.
*/

class pafiledb_main extends pafiledb_public
{
	function main( $action )
	{
		global $pafiledb_template, $lang, $board_config, $phpEx, $pafiledb_config, $debug, $phpbb_root_path; 

		$pafiledb_template->assign_vars( array( 
				'L_INDEX' => "<<",

				'U_INDEX' => append_sid( $mx_root_path . 'index.' . $phpEx ),
				'U_DOWNLOAD' => append_sid( pa_this_mxurl() ),

				'DOWNLOAD' => $pafiledb_config['module_name'],
				'TREE' => $menu_output ) 
			); 
			
		// ===================================================
		// Show the Category for the download database index
		// ===================================================
		$this->display_categories();

		$this->display( $lang['Download'], 'pa_main_body.tpl' );
	}
}

?>