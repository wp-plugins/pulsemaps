<?php

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

function pulsemaps_admin_styles() {
    $url_css = plugins_url('style.css', __FILE__);
    echo "<link rel='stylesheet' href='$url_css' type='text/css' media='all' />\n";
}

function pulsemaps_admin_scripts() {
	$opts = get_option('pulsemaps_options', array());
	if (!$opts['settings_visited']) {
		$opts['settings_visited'] = true;
		update_option('pulsemaps_options', $opts);
	}

    $url_js = plugins_url('jscolor/jscolor.js', __FILE__);
    echo "<script type='text/javascript' src='$url_js'></script>\n";
    $url_js = plugins_url('settings.js', __FILE__);
    echo "<script type='text/javascript' src='$url_js'></script>\n";

	global $pulsemaps_url;
	$options = get_option('pulsemaps_options');
	$id = $options['id'];
    $siteurl = get_option('siteurl');
}

function pulsemaps_admin_footer() {
	echo "<div id=\"script-container\"></div>\n";
}


add_action('admin_menu', 'pulsemaps_admin_add_page');
function pulsemaps_admin_add_page() {
	$page = add_options_page('PulseMaps Settings', 'PulseMaps', 'manage_options', 'pulsemaps', 'pulsemaps_options_page');
	add_action("admin_print_styles-$page", 'pulsemaps_admin_styles');
	add_action("admin_head-$page", 'pulsemaps_admin_scripts');
	add_action("admin_footer-$page", 'pulsemaps_admin_footer');
}


function pulsemaps_options_page() {
	if (!pulsemaps_registered()) {
		pulsemaps_register_page();
	} else {
		pulsemaps_settings_page();
	}
}


function pulsemaps_register_page() {
	global $pulsemaps_url;
	$tags = 'utm_source=wordpress&utm_medium=integration&utm_campaign=wp_plugin_' . pulsemaps_plugin_version();
?>
<h2>Install PulseMaps</h2>

<h3>Sign in to your PulseMaps account to continue</h3>

<form id="pulsemaps_install_form" method="post">
	<table class="form-table">
		<tr>
			<th style="width: 100px;"><label for="email">Email</label></th>
			<td>
				<input tabindex="1" style="width: 200px;" id="email" name="email" size="32" type="text" class="regular-text" value="" />
				<a href="<?php echo "$pulsemaps_url/?$tags"; ?>" target="_blank">(don't have a PulseMaps account yet?)</a>
			</td>
		</tr>
		<tr>
			<th style="width: 100px;"><label for="password">Password</label></th>
			<td>
				<input tabindex="1" style="width: 200px;" id="password" name="password" size="32" type="password" class="regular-text">
				<a href="<?php echo "$pulsemaps_url/forgot/?$tags"; ?>" target="_blank">(forgot your password?)</a>
			</td>
		</tr>
	</table>
	<table class="form-table">
		<tr>
			<th>
				<input tabindex="1" style="float: left; margin-right: 10px;" id="pulsemaps_install_button" type="submit" class="button button-primary button-large" value="Install »" />
				<img id="pulsemaps_install_loading" style="padding-top: 8px; display: none;" src="<?php echo pulsemaps_admin_url(); ?>images/loading.gif" alt="loading...">
			</th>
			<td>
				<div style="padding-top: 3px; color: #ff6060; font-size: 120%;" id="pulsemaps_install_error"></div>
			</td>
	</table>
</form>
<?php
}

function pulsemaps_settings_page() {
	global $pulsemaps_url;
	$opts = get_option('pulsemaps_options', array());
	$id = $opts['id'];
?>
<script type="text/javascript">
    var pulsemaps_data = {
     url: "<?php echo $pulsemaps_url; ?>",
     adminUrl: "<?php echo pulsemaps_admin_url(); ?>",
     proxyUrl: "<?php echo plugins_url('pm-proxy.php', __FILE__); ?>",
     id: "<?php echo $opts['id']; ?>",
     key :"<?php echo $opts['key']; ?>"
    };
</script>
<div id="pulsemaps_message" style="display: none;" class="updated"></div>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>PulseMaps Settings</h2>
<div id="pulsemaps_descr">
<div id="pulsemaps_plan_load">
</div>
</div>
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
	add_settings_field('pulsemaps_widget_type',    'Type', 'pulsemaps_widget_type',  'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_size',    'Width', 'pulsemaps_widget_width',  'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_color',   'Color', 'pulsemaps_widget_color', 'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_bgcolor', 'Background', 'pulsemaps_widget_bgcolor', 'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_open',    'New Window', 'pulsemaps_widget_opennew',  'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_dots',    'Real-time dots', 'pulsemaps_widget_dots', 'pulsemaps', 'pulsemaps_options');
	add_settings_field('pulsemaps_widget_meta',    'Text above small map', 'pulsemaps_widget_showmeta', 'pulsemaps', 'pulsemaps_options');
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


function pulsemaps_widget_dots() {
	$options = get_option('pulsemaps_options');
	$dots = $options['widget_dots'];
?>
	<input id="widget-dots" class="widget-param" type="checkbox" name="pulsemaps_options[widget_dots]" value="1" <?php checked(1 == $dots); ?> />
	<label for="widget-dots">Show real-time dots.</label>
<?php
}


function pulsemaps_widget_showmeta() {
	$options = get_option('pulsemaps_options');
	$meta = $options['widget_meta'];
?>
    <select id="widget-meta" class="widget-param" name="pulsemaps_options[widget_meta]">
      <option value="2" <?php selected($meta == '2'); ?>>Visitor count and start date</option>
      <option value="1" <?php selected($meta == '1'); ?>>Visitor count only</option>
      <option value="0" <?php selected($meta == '0'); ?>>No text</option>
    </select>
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

function pulsemaps_validate_bool(&$options, $input, $field) {
	if (isset($input[$field])) {
		$options[$field] = (bool)$input[$field];
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
	pulsemaps_validate_bool($options, $input, 'widget_dots');
	pulsemaps_validate_bool($options, $input, 'widget_new_window');
	pulsemaps_validate_bool($options, $input, 'activated');
	pulsemaps_validate_bool($options, $input, 'congrats');

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

	if (isset($input['widget_meta'])) {
		$widget_meta = trim($input['widget_meta']);
		if (in_array($widget_meta, array('0', '1', '2'))) {
			$options['widget_meta'] = $widget_meta;
		}
	}

	return $options;
}
