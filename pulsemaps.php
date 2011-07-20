<?php
/*
Plugin Name: PulseMaps
Plugin URI: http://pulsemaps.com/wordpress/
Description: Show off your visitors on the world map.  When people around the world visit your blog, the corresponding areas on the heat map widget light up!
Version: 1.2
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
	}

	if (!isset($opts['widget_width'])) {
		$opts['widget_width'] = 220;
	}
	if (!isset($opts['widget_color'])) {
		$opts['widget_color'] = 'F2EFE8';
	}
	if (!isset($opts['widget_bgcolor'])) {
		$opts['widget_bgcolor'] = '99B2CF';
	}
	if (!isset($opts['custom_color'])) {
		$opts['custom_color'] = 'F2EFE8';
	}
	if (!isset($opts['custom_bgcolor'])) {
		$opts['custom_bgcolor'] = '99B2CF';
	}
	if (!isset($opts['widget_type'])) {
		$opts['widget_type'] = 'plain';
	}

	update_option('pulsemaps_options', $opts);

}


function pulsemaps_activate_notice() {
	if (substr($_SERVER["PHP_SELF"], -11) == 'plugins.php'
		&& !is_active_widget(false, false, 'pulsemapswidget', true)) {
		echo '<div class="error"><p><strong>Activate PulseMaps visitor tracking on the <a href="';
		echo get_option('siteurl') . '/wp-admin/widgets.php';
		echo '">widget admin page</a>.  Check also the <a href="';
		echo get_option('siteurl') . '/wp-admin/options-general.php?page=pulsemaps';
        echo '">settings page</a>.</strong></p></div>';
	} else if (substr($_SERVER["PHP_SELF"], -11) == 'widgets.php'
			   && !is_active_widget(false, false, 'pulsemapswidget', true)) {
		echo '<div class="error"><p><strong>Drag the PulseMaps widget to a sidebar on the right to activate.</p></div>';
	}
}

add_action('admin_notices', 'pulsemaps_activate_notice');


function pulsemaps_plugin_actions($links, $file) {
	if ($file == 'pulsemaps/pulsemaps.php') {
		$link = '<a href="'. get_option('siteurl') . '/wp-admin/options-general.php?page=pulsemaps' . '">Settings</a>';
		array_unshift($links, $link);
	}
	return $links;
}

add_filter('plugin_action_links', 'pulsemaps_plugin_actions', 10, 2);
