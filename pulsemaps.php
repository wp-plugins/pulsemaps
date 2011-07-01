<?php
/*
Plugin Name: PulseMaps
Plugin URI: http://pulsemaps.com/wordpress/
Description: Show off your visitors on the world map.  When people around the world visit your blog, the corresponding areas on the heat map widget light up!
Version: 1.1.1
Author: Aito Software Inc.
License: GPLv2 or later
*/

/*  Copyright 2011 Aito Software Inc. (email : contact@aitosoftware.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('config.php');
require_once('widget.php');
require_once('settings.php');

function pulsemaps_register() {
	global $pulsemaps_api;
	$c = curl_init($pulsemaps_api . '/register');
	error_log($pulsemaps_api . '/register');
	$data = array('name' => get_option('blogname'),
				  'email' => get_option('admin_email'),
				  'url' => get_option('home'));
	curl_setopt($c, CURLOPT_POSTFIELDS, $data);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);

	return json_decode($ret, true);
}


function pulsemaps_upgrade($opts) {
	// Upgrade possible old widget style option.
	$style = get_option('pulsemaps_widget', null);
	if ($style !== null) {
		delete_option('pulsemaps_widget');
		$opts['widget_width'] = 220;
		$opts['widget_color'] = 'F2EFE8';
		$opts['widget_bgcolor'] = '99B2CF';
		$opts['custom_color'] = 'F2EFE8';
		$opts['custom_bgcolor'] = '99B2CF';
		$opts['widget_type'] = 'plain';
		if ($style == 'satellite') {
			$opts['widget_type'] = 'satellite';
		} else if ($style == 'monochrome') {
			$opts['widget_bgcolor'] = '3B3B3B';
		}
	}

	global $pulsemaps_version;
	$opts['version'] = $pulsemaps_version;

	update_option('pulsemaps_options', $opts);
}


add_action('plugins_loaded', 'pulsemaps_upgrade_check');
function pulsemaps_upgrade_check() {
	global $pulsemaps_version;

	$opts = get_option('pulsemaps_options', array());
	if (!isset($opts['version']) || $opts['version'] < $pulsemaps_version) {
		pulsemaps_upgrade($opts);
	}
}


register_activation_hook(__FILE__, 'pulsemaps_install');
function pulsemaps_install() {
	$opts = get_option('pulsemaps_options', array());

	if (!isset($opts['key']) || !isset($opts['id'])) {
		$ret = pulsemaps_register();
		if ($ret['status'] == 'ok') {
			$opts['key'] = $ret['key'];
			$opts['id'] = $ret['id'];
		} else {
			error_log(json_encode($ret));
		}

		update_option('pulsemaps_options', $opts);
	}
}
