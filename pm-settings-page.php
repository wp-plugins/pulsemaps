<?php

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

function pulsemaps_admin_styles() {
    $url_css = plugins_url('style.css', __FILE__);
    echo "<link rel='stylesheet' href='$url_css' type='text/css' media='all' />\n";
}

function pulsemaps_admin_scripts() {
    $url_js = plugins_url('jscolor/jscolor.js', __FILE__);
    echo "<script type='text/javascript' src='$url_js'></script>\n";
    $url_js = plugins_url('settings.js', __FILE__);
    echo "<script type='text/javascript' src='$url_js'></script>\n";

	global $pulsemaps_api;
	$options = get_option('pulsemaps_options');
	$id = $options['id'];
	$widget_url = "$pulsemaps_api/widget.js?id=$id&notrack=1&target=widget-preview";
    $url_load = plugins_url('pm-plan.php', __FILE__);
?>
<script type='text/javascript'>
  function updatePreview() {
    pulsemaps.updatePreview('<?php echo $widget_url; ?>');
  }
  jQuery(document).ready(function() {
	pulsemaps.setHooks('<?php echo $widget_url; ?>');
	jQuery("#pulsemaps_plan_load").load("<?php echo $url_load; ?>", {key: "<?php echo $options['key']; ?>"});
  });
</script>
<?php
}


function pulsemaps_admin_footer() {
	echo "<div id=\"script-container\"><script type='text/javascript'>updatePreview();</script></div>\n";
}


add_action('admin_menu', 'pulsemaps_admin_add_page');
function pulsemaps_admin_add_page() {
	$page = add_options_page('PulseMaps Settings', 'PulseMaps', 'manage_options', 'pulsemaps', 'pulsemaps_options_page');
	add_action("admin_print_styles-$page", 'pulsemaps_admin_styles');
	add_action("admin_head-$page", 'pulsemaps_admin_scripts');
	add_action('admin_footer', 'pulsemaps_admin_footer');
}

function pulsemaps_options_page() {
	$opts = get_option('pulsemaps_options', array());
	$id = $opts['id'];
	$style = get_option('pulsemaps_widget', 'default');
	global $pulsemaps_api;
    $siteurl = get_option('siteurl');
	if (!pulsemaps_tracking_active()) {
		echo '<div class="error"><p><strong>Visitor tracking is inactive. Drag the PulseMaps widget on a sidebar on the <a href="';
		echo get_option('siteurl') . '/wp-admin/widgets.php';
		echo '">widget admin page</a>.</strong></p></div>';
	}
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>PulseMaps Visitor Map</h2>
<div id="pulsemaps_plan">
<div id="pulsemaps_descr">
<div id="pulsemaps_plan_load">
<img src="<?php echo $siteurl; ?>/wp-admin/images/loading.gif" alt="loading...">
</div>
</div>
<div id="pulsemaps_map"></div>
</div>
<script type="text/javascript" id="pulsemaps_<?php echo $id; ?>" src="<?php echo $pulsemaps_api; ?>/map.js?id=<?php echo $id; ?>&target=pulsemaps_map"></script>
<div id="pulsemaps_settings">
<form action="options.php" method="post">
	 <?php settings_fields('pulsemaps_options'); ?>
	 <?php do_settings_sections('pulsemaps'); ?>

</div>
<div style="clear: both;"></div>
</div>

<p class="submit">
<input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
</p>
</form>
</div>
</div>
<?php
}

add_action('admin_init', 'pulsemaps_admin_init');
function pulsemaps_admin_init(){
	$options = get_option('pulsemaps_options');
	register_setting( 'pulsemaps_options', 'pulsemaps_options', 'pulsemaps_options_validate' );
	add_settings_section('pulsemaps_options', 'Widget designer', 'pulsemaps_widget_section', 'pulsemaps');
	add_settings_field('pulsemaps_widget_type',   'Type', 'pulsemaps_widget_type',  'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_size',   'Width', 'pulsemaps_widget_width',  'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_color',  'Color', 'pulsemaps_widget_color', 'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_bgcolor','Background', 'pulsemaps_widget_bgcolor', 'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_open',   'New Window', 'pulsemaps_widget_opennew',  'pulsemaps', 'pulsemaps_options');
	if ($options['plan'] != 'free') {
		add_settings_field('pulsemaps_track_all',   'Track without widget', 'pulsemaps_track_all',  'pulsemaps', 'pulsemaps_options');
	}
}

function pulsemaps_widget_section() {
?>
	<div id="pulsemaps_widget_wrap">
		<div id="pulsemaps_widget_preview">
		     Widget preview:<br/>
		     <div id="pulsemaps_widget_bg"><div id="widget-preview"></div></div>
        </div>
        <div id="pulsemaps_widget_settings">
<?php
}

function pulsemaps_widget_type() {
	 $options = get_option('pulsemaps_options');
	 $type = $options['widget_type'];
?>
	 <input id="widget-plain" class="widget-param" type="radio" name="pulsemaps_options[widget_type]" value="plain" <?php checked('plain', $type); ?> />
	 <label for="widget-plain">Plain</label><br/>
	 <input id="widget-satellite" class="widget-param" type="radio" name="pulsemaps_options[widget_type]" value="satellite" <?php checked('satellite', $type); ?> />
	 <label for="widget-satellite">Satellite</label><br/>
<?php
}

function pulsemaps_widget_width() {
	$options = get_option('pulsemaps_options');
	$width = $options['widget_width'];
?>
	<input id="widget-width" class="widget-param" name="pulsemaps_options[widget_width]" size="4" type="text" value="<?php echo $width; ?>" />
	<br/><span class="description">Width in pixels (at least 50, at most 500).</span>
<?php
}

function pulsemaps_widget_opennew() {
	$options = get_option('pulsemaps_options');
	$new_window = $options['widget_new_window'];
?>
	<input id="widget-new-window" class="widget-param" type="checkbox" name="pulsemaps_options[widget_new_window]" value="1" <?php checked(1 == $new_window); ?> />
	<label for="widget-new-window">Open map details page in new window.</label>
<?php
}

function pulsemaps_track_all() {
	$options = get_option('pulsemaps_options');
	$track_all = $options['track_all'];
?>
	<input id="track-all" class="widget-param" type="checkbox" name="pulsemaps_options[track_all]" value="1" <?php checked(1 == $track_all); ?> />
	<label for="track-all">Track also pages without the widget.</label>
<?php
}

function pulsemaps_widget_color() {
	$options = get_option('pulsemaps_options');
	$color = $options['widget_color'];
	$custom = $options['custom_color'];
	switch ($color) {
	case 'F2EFE8':
		$choice = 'default';
		break;
	default:
		$choice = 'custom';
	}
?>
	<input id="color-default" class="widget-param widget-plain" type="radio" name="widget_color" value="default" <?php checked('default', $choice); ?> />
	<label for="color-default">Default</label><br/>

	<input id="color-custom" class="widget-param widget-plain" type="radio" name="widget_color" value="custom" <?php checked('custom', $choice); ?> />
    <label for="color-custom">Custom</label>

	<input id="widget-color" name="pulsemaps_options[widget_color]" size="6" type="hidden" value="<?php echo $color; ?>" />
	<input id="custom-color" class="color widget-param widget-plain" name="pulsemaps_options[custom_color]" size="6" type="text" value="<?php echo $custom; ?>" />
<?php
}


function pulsemaps_widget_bgcolor() {
	$options = get_option('pulsemaps_options');
	$color = $options['widget_bgcolor'];
	$custom = $options['custom_bgcolor'];
	switch ($color) {
	case '99B2CF':
		$choice = 'default';
		break;
	case '3B3B3B':
		$choice = 'dark';
		break;
	case 'transparent':
		$choice = 'transparent';
		break;
	default:
		$choice = 'custom';
	}

?>
	<input id="bgcolor-default" class="widget-param widget-plain" type="radio" name="widget_bgcolor" value="default" <?php checked('default', $choice); ?> />
	<label for="bgcolor-default">Default</label><br/>
	<input id="bgcolor-dark" class="widget-param widget-plain" type="radio" name="widget_bgcolor" value="dark" <?php checked('dark', $choice); ?> />
	<label for="bgcolor-dark">Dark</label><br/>
	<input id="bgcolor-transparent" class="widget-param widget-plain" type="radio" name="widget_bgcolor" value="transparent" <?php checked('transparent', $choice); ?> />
	<label for="bgcolor-transparent">Transparent</label><br/>
	<input id="bgcolor-custom" class="widget-param widget-plain" type="radio" name="widget_bgcolor" value="custom" <?php checked('custom', $choice); ?> />
	<label for="bgcolor-custom">Custom</label>
	<input id="widget-bgcolor" name="pulsemaps_options[widget_bgcolor]" size="6" type="hidden" value="<?php echo $color; ?>" />
	<input id="custom-bgcolor" class="color widget-param widget-plain" name="pulsemaps_options[custom_bgcolor]" size="6" type="text" value="<?php echo $custom; ?>" />
<?php
}

function pulsemaps_validate_regex(&$options, $input, $field, $regex, $filter = null) {
	if (isset($input[$field])) {
		$val = trim($input[$field]);
		if (preg_match($regex, $val)) {
			if ($filter) {
				$val = $filter($val);
			}
			$options[$field] = $val;
		}
	}
}

function pulsemaps_validate_color(&$options, $input, $field) {
	if ($input[$field] == 'transparent') {
		$options[$field] = 'transparent';
	} else {
		pulsemaps_validate_regex($options, $input, $field, '/^[a-fA-F0-9]{6}$/i', 'strtoupper');
	}
}


function pulsemaps_options_validate($input) {
	$options = get_option('pulsemaps_options', array());

	pulsemaps_validate_regex($options, $input, 'key', '/^[a-zA-Z0-9_=-]{27}$/i');
	pulsemaps_validate_regex($options, $input, 'id', '/^[1-9][0-9]{8}$/');
	pulsemaps_validate_color($options, $input, 'widget_color');
	pulsemaps_validate_color($options, $input, 'widget_bgcolor');
	pulsemaps_validate_color($options, $input, 'custom_color');
	pulsemaps_validate_color($options, $input, 'custom_bgcolor');

	if (isset($input['widget_type'])) {
		$widget_type = trim($input['widget_type']);
		if (in_array($widget_type, array('plain', 'satellite'))) {
			$options['widget_type'] = $widget_type;
		}
	}

	if (isset($input['widget_width'])) {
		$width = (int)trim($input['widget_width']);
		if ($width >= 50 && $width <= 500) {
			$options['widget_width'] = $width;
		}
	}

	if (isset($input['widget_new_window'])) {
		$options['widget_new_window'] = true;
	} else {
		$options['widget_new_window'] = false;
	}

	if (isset($input['track_all'])) {
		$options['track_all'] = true;
	} else {
		$options['track_all'] = false;
	}

	return $options;
}
