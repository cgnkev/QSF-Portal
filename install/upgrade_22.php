<?php
/**
 * QSF Portal
 * Copyright (c) 2006-2008 The QSF Portal Development Team
 * http://www.qsfportal.com/
 *
 * Based on:
 *
 * Quicksilver Forums
 * Copyright (c) 2005-2006 The Quicksilver Forums Development Team
 * http://www.quicksilverforums.com/
 * 
 * MercuryBoard
 * Copyright (c) 2001-2006 The Mercury Development Team
 * http://www.mercuryboard.com/
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

if (!defined('INSTALLER')) {
	exit('Use index.php to upgrade.');
}

// Upgrade from 1.3.0 to 1.3.1

// Template changes
$need_templates = array(
	// Added templates
	// Changed templates
	'FORUM_TOPICS_MAIN',
	'RECENT_TOPIC',
	'ADMIN_INSTALL_SKIN',
	'CP_PREFS'
	);

// Permission changes	
$new_permissions['edit_profile'] = true;
$new_permissions['edit_avatar'] = true;
$new_permissions['edit_sig'] = true;

// Queries to run

// New Timezones

?>
