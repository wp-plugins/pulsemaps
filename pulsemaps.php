<?php
/*
Plugin Name: PulseMaps
Plugin URI: http://pulsemaps.com/wordpress/
Description: See where your visitors come from on the world map.
Version: 1.7.2
Author: Aito Software Inc.
License: GPLv2 or later
*/

/*  Copyright 2011-2013 Aito Software Inc. (email : contact@aitosoftware.com)

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

require_once('pm-config.php');
require_once('pm-widget.php');
require_once('pm-settings-page.php');
require_once('pm-register.php');

function pulsemaps_upgrade($opts, $first) {
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

	if (!isset($opts['plan'])) {
		$opts['plan'] = 'free';
	}

	if (!isset($opts['widget_new_window'])) {
		$opts['widget_new_window'] = false;
	}

	if (!isset($opts['widget_dots'])) {
		$opts['widget_dots'] = true;
	}

	if (!isset($opts['widget_meta'])) {
		$opts['widget_meta'] = '2';
	} else if (is_bool($opts['widget_meta'])) {
		if ($opts['widget_meta']) {
			$opts['widget_meta'] = '2';
		} else {
			$opts['widget_meta'] = '0';
		}
	}

	if (!isset($opts['congrats'])) {
		$opts['congrats'] = false;
	}

	if (!isset($opts['activated'])) {
		$opts['activated'] = true;
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
		pulsemaps_upgrade($opts, !isset($opts['version']));
	}
}


register_activation_hook(__FILE__, 'pulsemaps_install');
function pulsemaps_install() {
	$opts = get_option('pulsemaps_options', array());

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
	if (!isset($opts['congrats'])) {
		$opts['congrats'] = false;
	}
	if (!isset($opts['activated'])) {
		$opts['activated'] = false;
	}
	if (!isset($opts['after_text'])) {
		$opts['after_text'] = '';
	}

	update_option('pulsemaps_options', $opts);

	if (!wp_next_scheduled('pulsemaps_daily')) {
		wp_schedule_event(time(), 'daily', 'pulsemaps_daily');
	}
}

register_deactivation_hook(__FILE__, 'pulsemaps_uninstall');
function pulsemaps_uninstall() {
	wp_clear_scheduled_hook('pulsemaps_daily');
}


function pulsemaps_registered() {
	$opts = get_option('pulsemaps_options', array());
	return isset($opts['key']) && isset($opts['id']);
}


function pulsemaps_refresh() {
	if (!pulsemaps_registered()) {
		return;
	}

	global $pulsemaps_url;
	$opts = get_option('pulsemaps_options', array());
	$c = curl_init($pulsemaps_url . '/refresh');
	$data = array('key' => $opts['key'],
				  'name' => get_option('blogname'),
				  'url' => get_option('home'));
	curl_setopt($c, CURLOPT_POSTFIELDS, $data);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($c);
	$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	curl_close($c);
	foreach (json_decode($ret, true) as $k => $v) {
		$opts[$k] = $v;
	}
	update_option('pulsemaps_options', $opts);
}
add_action('pulsemaps_daily', 'pulsemaps_refresh');


function pulsemaps_plugin_version() {
	$plugin_data = get_plugin_data(__FILE__);
	$plugin_version = $plugin_data['Version'];
	return $plugin_version;
}

function pulsemaps_admin_url() {
	if (function_exists('admin_url')) {
		return admin_url();
	} else {
		return get_option('siteurl') . '/wp-admin/';
	}
}

function pulsemaps_settings_url() {
	return pulsemaps_admin_url() . 'options-general.php?page=pulsemaps';
}

function pulsemaps_activate_notice() {
	$opts = get_option('pulsemaps_options');
	$id = $opts['id'];
	if (!$opts['id'] && substr($_SERVER["PHP_SELF"], -11) != 'general.php'
		&& $_GET["page"] != "pulsemaps") {
		echo '<div class="error"><p><strong>Activate PulseMaps for this site on the <a href="';
		echo pulsemaps_settings_url();
        echo '">settings page</a>.</strong></p></div>';
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


function pulsemaps_async_tracker() {
	$opts = get_option('pulsemaps_options');
	if (!is_user_logged_in()) {
		global $pulsemaps_url;
		?>
<script type="text/javascript">
(function() {
   var pm = document.createElement('script'); pm.type = 'text/javascript'; pm.async = true;
   pm.src = "<?php echo $pulsemaps_url .'/tracker.js?id='.$opts['id']; ?>";
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(pm, s);
})();
</script>
<?php
	}
}

add_action('wp_head', 'pulsemaps_async_tracker');


function pulsemaps_bigmap($atts) {
	$opts = get_option('pulsemaps_options', array());
	$id = $opts['id'];
	if (isset($atts['height'])) {
		$height = 'height: ' . $atts['height'] . 'px; ';
	} else {
		$height = '';
	}
	if (isset($atts['width'])) {
		$width = 'width: ' . $atts['width'] . 'px; ';
	} else {
		$width = 'width: 100%; ';
	}

	global $pulsemaps_url;
	$url = $pulsemaps_url . '/map.js?id=' . $id . '&target=pulsemaps_map';
	return "<div id=\"pulsemaps_map\" style=\"$width$height\">\n"
		. "<a style=\"float: right; color: rgba(0, 0, 0, 0.7); font-size: 10px; font-style: normal; font-family: Arial,sans-serif; padding: 6px; text-decoration: none;\" href=\"http://pulsemaps.com/\">Website visitor map by PulseMaps.com</a>\n"
		. "</div>"
		. "<script type=\"text/javascript\">\n"
		. "(function() {\n"
		. "  var pm=document.createElement(\"script\");\n"
		. "  pm.type = 'text/javascript';\n"
		. "  pm.async = true;\n"
		. "  pm.src = '" . $url . "';\n"
		. "  var s = document.getElementById('pulsemaps_map');\n"
		. "  s.parentNode.appendChild(pm);\n"
		. "})();\n"
		. "</script>\n";
}

add_shortcode('pulsemaps', 'pulsemaps_bigmap');
