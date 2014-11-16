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
 * Add user upload data to profile view
 * 
 * @author Roger Libiez <samson@afkmud.com>
**/
class uploads_data extends modlet
{
	function run($uid)
	{
		$user = $this->qsf->db->fetch( "SELECT user_uploads, user_joined FROM %pusers WHERE user_id=%d", $uid );
		$last = $this->qsf->db->fetch( "SELECT file_id, file_name, file_date FROM %pfiles
			WHERE file_submitted=%d ORDER BY file_date DESC LIMIT 1", $uid );

		$lastfile = "None yet.";
		if(isset($last['file_id'])) {
			$date = $this->qsf->mbdate(DATE_LONG, $last['file_date']);
			$lastfile = "<a href=\"{$this->qsf->self}?a=files&amp;s=viewfile&amp;fid={$last['file_id']}\">{$last['file_name']}</a><br />{$date}";
		}

		$uploadsPerDay = $user['user_uploads'] / ((($this->qsf->time - $user['user_joined']) / 86400));
		$uploadsPerDay = number_format($uploadsPerDay, 2, $this->qsf->lang->sep_decimals, $this->qsf->lang->sep_thousands);
		$uploads = "<a href=\"{$this->qsf->self}?a=files&amp;s=search&amp;uid={$uid}\">{$user['user_uploads']} total, {$uploadsPerDay} per day</a>";

		return eval($this->qsf->template('PROFILE_UPLOADS'));
	}
}
?>