<?php
/*
Plugin Name: PulseMaps
Plugin URI: http://pulsemaps.com/wordpress/
Description: Show off your visitors on the world map.  When people around the world visit your blog, the corresponding areas on the heat map widget light up!
Version: 1.4.3
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

require_once('pm-config.php');
require_once('pm-widget.php');
require_once('pm-settings-page.php');

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

	if (!isset($opts['track_all'])) {
		$opts['track_all'] = false;
	}

	if (!isset($opts['widget_dots'])) {
		$opts['widget_dots'] = true;
	}

	if (!isset($opts['widget_meta'])) {
		$opts['widget_meta'] = true;
	}

	if (!isset($opts['settings_visited'])) {
		$opts['settings_visited'] = !$first;
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

function pulsemaps_tracking_active() {
	if (is_active_widget(false, false, 'pulsemapswidget', true)) {
		return true;
	}
	$opts = get_option('pulsemaps_options', array());
	return $opts['track_all'];
}


function pulsemaps_settings_visited() {
	$opts = get_option('pulsemaps_options', array());
	return $opts['settings_visited'];
}


function pulsemaps_activate_notice() {
	global $pulsemaps_api;
	$opts = get_option('pulsemaps_options');
	$id = $opts['id'];
	if (substr($_SERVER["PHP_SELF"], -11) == 'plugins.php' && !pulsemaps_tracking_active()) {
		echo "<iframe style=\"display: none;\" src=\"$pulsemaps_api/mapOwner/?map=$id\"></iframe>";
		echo '<div class="error"><p><strong>Activate PulseMaps visitor tracking on the <a href="';
		echo get_option('siteurl') . '/wp-admin/widgets.php';
		echo '">widget admin page</a>.  Check also the <a href="';
		echo get_option('siteurl') . '/wp-admin/options-general.php?page=pulsemaps';
        echo '">settings page</a>.</strong></p></div>';
	} else if (substr($_SERVER["PHP_SELF"], -11) == 'widgets.php') {
	    echo '<div id="pulsemaps_not_active" class="error" ';
		if (pulsemaps_tracking_active()) {
			echo 'style="display: none;"';
		}
		echo '><p><strong>Drag the PulseMaps widget to a sidebar on the right to activate.</strong></p></div>';
	    echo '<div ';
		if (!pulsemaps_settings_visited()) {
			echo 'id="pulsemaps_activated" ';
		} else {
			echo 'style="display: none;" ';
		}
		echo 'class="updated"><p><strong>Remember to visit your <a href="';
		echo get_option('siteurl'). '/wp-admin/options-general.php?page=pulsemaps';
		echo '">PulseMaps settings page</a> to customize your widget.</strong></p></div>';
	} else {
		if (!pulsemaps_settings_visited()) {
			echo '<div class="updated"><p><strong>Remember to visit your <a href="';
			echo get_option('siteurl'). '/wp-admin/options-general.php?page=pulsemaps';
			echo '">PulseMaps settings page</a> to customize your widget.</strong></p></div>';
		}
	}
}
add_action('admin_notices', 'pulsemaps_activate_notice');

function pulsemaps_widgets_css() {
?>
<style type="text/css" media="all">
.pulsemaps-red-border {
		border-color: #c00 !important;
}
.pulsemaps-red-bg {
		background-color: #ffebe8 !important;
}
</style>
<?php
}

add_action("admin_print_scripts-widgets.php", 'pulsemaps_widgets_css');

function pulsemaps_widgets_script() {
	?>
<script type='text/javascript'>
   var pulsemaps = pulsemaps || {};
   pulsemaps.showRed = function() {
	   jQuery('div.widget[id*=_pulsemapswidget] .widget-title').addClass('pulsemaps-red-bg');
	   jQuery('div.widget[id*=_pulsemapswidget] .widget-top').addClass('pulsemaps-red-border');
	   jQuery('div.widget[id*=_pulsemapswidget]').addClass('pulsemaps-red-border');
   }
   pulsemaps.hideRed = function() {
	   jQuery('div.widget[id*=_pulsemapswidget] .widget-title').removeClass('pulsemaps-red-bg');
	   jQuery('div.widget[id*=_pulsemapswidget] .widget-top').removeClass('pulsemaps-red-border');
	   jQuery('div.widget[id*=_pulsemapswidget]').removeClass('pulsemaps-red-border');
   }
   pulsemaps.origSaveOrder = wpWidgets.saveOrder;
   pulsemaps.saveOrder = function(sb) {
	   if (jQuery('#widgets-right').find('div.widget[id*=_pulsemapswidget]').length) {
		   jQuery('#pulsemaps_not_active').slideUp();
		   jQuery('#pulsemaps_activated').slideDown();
		   pulsemaps.hideRed();
	   } else {
		   jQuery('#pulsemaps_not_active').slideDown();
		   jQuery('#pulsemaps_activated').slideUp();
		   pulsemaps.showRed();
	   }
	   pulsemaps.origSaveOrder.call(wpWidgets, sb);
   }
   wpWidgets.saveOrder = pulsemaps.saveOrder;

   if (jQuery('#widgets-right').find('div.widget[id*=_pulsemapswidget]').length == 0) {
	   pulsemaps.showRed();
   }
</script>
<?php
}
add_action("admin_footer-widgets.php", 'pulsemaps_widgets_script');

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
	if ($opts['track_all']) {
		global $pulsemaps_api;
		?>
<script type="text/javascript">
(function() {
   var pm = document.createElement('script'); pm.type = 'text/javascript'; pm.async = true;
   pm.src = "<?php echo $pulsemaps_api .'/tracker.js?id='.$opts['id']; ?>";
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(pm, s);
})();
</script>
<?php
	}
}

add_action('wp_head', 'pulsemaps_async_tracker');
