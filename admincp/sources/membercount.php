<?php
/**
 * QSF Portal
 * Copyright (c) 2006-2015 The QSF Portal Development Team
 * https://github.com/Arthmoor/QSF-Portal
 *
 * Based on:
 *
 * Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 * http://code.google.com/p/quicksilverforums/
 * 
 * MercuryBoard
 * Copyright (c) 2001-2006 The Mercury Development Team
 * https://github.com/markelliot/MercuryBoard
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

if (!defined('QUICKSILVERFORUMS') || !defined('QSF_ADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/admincp/admin.php';

/**
 * Member maintenance
 *
 * @author Jason Warner <jason@mercuryboard.com>
 * @since Beta 3.0
 **/
class membercount extends admin
{
	/**
	 * Recounts the number of members
	 *
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 3.0
	 * @version 1.0.0
	 * @Intensive queries, but not used much.
	 * @return string Message
	 **/
	function execute()
	{
		$this->set_title($this->lang->mcount);
		$this->tree($this->lang->mcount);

		$this->ResetMemberStats();
		$this->write_sets();

		return $this->message($this->lang->mcount, $this->lang->mcount_updated);
	}
}
?>