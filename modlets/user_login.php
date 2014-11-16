<?php
/**
 * Quicksilver Forums
 * Copyright (c) 2005 The Quicksilver Forums Development Team
 *  http://www.quicksilverforums.com/
 * 
 * based off MercuryBoard
 * Copyright (c) 2001-2005 The Mercury Development Team
 *  http://www.mercuryboard.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 **/

if (!defined('QUICKSILVERFORUMS')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * Create a quicklogin box for users to log in with.
 *
 * @author Roger Libiez [Samson] 2006
 * @since 1.3.1
 **/
class user_login extends modlet
{	
	function run() {
		if( $this->qsf->perms->is_guest ) {
			$this->qsf->lang->login(); // For login words
			$this->qsf->lang->register(); // For registration word

			return eval($this->qsf->template('MAIN_USER_LOGIN'));
		}
		return "";
	}
}
?>
