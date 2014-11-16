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
 
define('QUICKSILVERFORUMS', true);
define('QSF_PUBLIC', true);

$time_now   = explode(' ', microtime());
$time_start = $time_now[1] + $time_now[0];

srand((double)microtime() * 1234567);

// Zend_Hash_Del_Key_Or_Index Vulnerability - We don't use $_REQUEST, so kill it.
if (@ini_get('register_globals')) {
	foreach ( $_REQUEST as $var => $null ) {
		unset($$var);
		unset($$var);
	}
}

require './settings.php';
$set['include_path'] = '.';
require_once $set['include_path'] . '/defaultutils.php';

if (!$set['installed']) {
	header('Location: ./install/index.php');
}

set_error_handler('error');

error_reporting(E_ALL);
set_magic_quotes_runtime(0);

// Check for any addons available
include_addons($set['include_path'] . '/addons/');

// Open connection to database
$db = new $modules['database']($set['db_host'], $set['db_user'], $set['db_pass'], $set['db_name'], $set['db_port'], $set['db_socket'], $set['prefix']);
if (!$db->connection) {
    error(QUICKSILVER_ERROR, 'A connection to the database could not be established and/or the specified database could not be found.', __FILE__, __LINE__);
}
$settings = $db->fetch("SELECT settings_data FROM %psettings LIMIT 1");
$set = array_merge($set, unserialize($settings['settings_data']));

/*
 * Logic here:
 * If 'a' is not set, but some other query is, it's a bogus request for this software.
 * If 'a' is set, but the module doesn't exist, it's either a malformed URL or a bogus request.
 * Otherwise $missing remains false and no error is generated later.
 */
$missing = false;
$terms_module = '';
if (!isset($_GET['a']) ) {
	$module = $modules['default_module'];
	if( isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '&debug=1' )
		$missing = true;
} elseif ( !in_array( $_GET['a'], array_merge($set['optional_modules'], $modules['public_modules']) ) ) {
	$module = $modules['default_module'];

	if( $_GET['a'] != 'forum_rules' && $_GET['a'] != 'upload_rules' )
		$missing = true;
	else
		$terms_module = $_GET['a'];
} else {
	$module = $_GET['a'];
}

require './func/' . $module . '.php';

$qsf = new $module($db);
$qsf->pre = $set['prefix'];

$qsf->get['a'] = $module;
$qsf->sets     = $set;
$qsf->modules  = $modules;

// If zlib isn't available, then trying to use it doesn't make much sense.
if (extension_loaded('zlib')) {
	if ($qsf->sets['output_buffer'] && isset($qsf->server['HTTP_ACCEPT_ENCODING']) && stristr($qsf->server['HTTP_ACCEPT_ENCODING'], 'gzip')) {
		if( !@ob_start('ob_gzhandler') ) {
			ob_start();
		}
	} else {
		ob_start();
	}
} else {
	ob_start();
}

header( 'P3P: CP="CAO PSA OUR"' );
session_start();

$qsf->user_cl = new $modules['user']($qsf);
$qsf->user    = $qsf->user_cl->login();
$qsf->lang    = $qsf->get_lang($qsf->user['user_language'], $qsf->get['a']);
$qsf->session = &$_SESSION;
$qsf->session['id'] = session_id();

if (!isset($qsf->get['skin'])) {
	$qsf->skin = $qsf->user['skin_dir'];
} else {
	$qsf->skin = $qsf->get['skin'];
}

$qsf->init();

$server_load = $qsf->get_load();

$qsf->tree($qsf->sets['forum_name'], "$qsf->self?a=board");

if ($qsf->is_banned()) {
	error(QUICKSILVER_NOTICE, $qsf->lang->main_banned);
}

$reminder = null;
$reminder_text = null;

if ($qsf->sets['closed']) {
	if (!$qsf->perms->auth('board_view_closed')) {
		if ($qsf->get['a'] != 'login') {
			error(QUICKSILVER_NOTICE, $qsf->sets['closedtext'] . "<br /><hr />If you are an administrator, <a href='$qsf->self?a=login&amp;s=on'>click here</a> to login.");
		}
	} else {
		$reminder_text = $qsf->lang->main_reminder_closed . '<br />&quot;' . $qsf->sets['closedtext'] . '&quot;';
	}
}

if ($qsf->user['user_group'] == USER_AWAIT) {
	$reminder_text = "{$qsf->lang->main_activate}<br /><a href='{$qsf->self}?a=register&amp;s=resend'>{$qsf->lang->main_activate_resend}</a>";
}

if ($reminder_text) {
	$reminder = eval($qsf->template('MAIN_REMINDER'));
}

if ($qsf->sets['max_load'] && ($server_load > $qsf->sets['max_load'])) {
	error(QUICKSILVER_NOTICE, sprintf($qsf->lang->main_max_load, $qsf->sets['forum_name']));
}

$qsf->add_feed($qsf->sets['loc_of_board'] . $qsf->mainfile . '?a=rssfeed');

if( $missing ) {
	header( 'HTTP/1.0 404 Not Found' );
	$output = $qsf->message( $qsf->lang->error, $qsf->lang->error_404 );
} else {
	if( $terms_module == 'forum_rules' ) {
		$tos = $qsf->db->fetch( 'SELECT settings_tos FROM %psettings' );

		$message = $qsf->format( $tos['settings_tos'], FORMAT_HTMLCHARS | FORMAT_BREAKS | FORMAT_MBCODE );
		$output = $qsf->message( 'Terms of Service: Forums', $message );
	} elseif ( $terms_module == 'upload_rules' ) {
		$tos = $qsf->db->fetch( 'SELECT settings_tos_files FROM %psettings' );

		$message = $qsf->format( $tos['settings_tos_files'], FORMAT_HTMLCHARS | FORMAT_BREAKS | FORMAT_MBCODE );
		$output = $qsf->message( 'Terms of Service: Uploads', $message );
	} else {
		$output = $qsf->execute();
	}
}

if (($qsf->get['a'] == 'forum') && isset($qsf->get['f'])) {
	$searchlink = '&amp;f=' . intval($qsf->get['f']);
} else {
	$searchlink = null;
}

$userheader = eval($qsf->template('MAIN_HEADER_' . ($qsf->perms->is_guest ? 'GUEST' : 'MEMBER')));

$title = isset($qsf->title) ? $qsf->title : $qsf->sets['forum_name'];

$time_now  = explode(' ', microtime());
$time_exec = round($time_now[1] + $time_now[0] - $time_start, 4);

if (isset($qsf->get['debug'])) {
	$output = $qsf->show_debug($server_load, $time_exec);
}

if (!$qsf->nohtml) {
	$google = null;
	if ( isset($qsf->sets['analytics_id']) && !empty($qsf->sets['analytics_id']) ) {
		$google = "<script src=\"http://www.google-analytics.com/urchin.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
_uacct = \"{$qsf->sets['analytics_id']}\";
urchinTracker();
</script>";
	}
	$servertime = $qsf->mbdate( DATE_LONG, $qsf->time, false );
	$copyright = eval($qsf->template('MAIN_COPYRIGHT'));
	$quicksilverforums = $output;
	echo eval($qsf->template('MAIN'));
} else {
	echo $output;
}

@ob_end_flush();
@flush();

// Do post output stuff
$qsf->cleanup();

// Close the DB connection.
$qsf->db->close();
?>