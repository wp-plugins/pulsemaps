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

require_once('config.php');

add_action('admin_head', 'pulsemaps_admin_head');
function pulsemaps_admin_head() {
    $siteurl = get_option('siteurl');
    $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/style.css';
    echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}

add_action('admin_menu', 'pulsemaps_admin_add_page');
function pulsemaps_admin_add_page() {
	add_options_page('PulseMaps Settings', 'PulseMaps', 'manage_options', 'pulsemaps', 'pulsemaps_options_page');
}

function pulsemaps_options_page() {
	$opts = get_option('pulsemaps_options', array());
	$id = $opts['id'];
	$style = get_option('pulsemaps_widget', 'default');
	global $pulsemaps_api;
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>PulseMaps Visitor Map</h2>
<div id="pulsemaps_plan">
<?php
	if (!isset($opts['plan'])) {
?>
<div id="pulsemaps_descr">
  <h3>Plan</h3>
<p>Current plan: <strong>free</strong></p>
<ul>
<li>Only pages which include the PulseMaps widget will be tracked.</li>
<li>Ads may be displayed on the widget.</li>
<li>A link to pulsemaps.com is included on the map widget.</li>
</ul>
<p>
A future version of the plugin will allow you to upgrade to a paid plan for visitor tracking without the widget, no ads, longer history, and more.
</p>
</div>
<?php
	}
?>
<div id="pulsemaps_map"></div>
</div>
<script type="text/javascript" id="pulsemaps_<?php echo $id; ?>" src="<?php echo $pulsemaps_api; ?>/map.js?id=<?php echo $id; ?>&target=pulsemaps_map"></script>
<div id="pulsemaps_settings">
<h3>Widget appearance</h3>
<form action="options.php" method="post">
	 <?php settings_fields('pulsemaps_widget'); ?>

	 <input id="widget-default" type="radio" name="pulsemaps_widget" value="default" <?php checked('default', $style); ?> />
	 <label for="widget-default">Default<br/>
     <img class="widget-sample" src="<?php echo plugins_url(); ?>/pulsemaps/widget-default.png"></label><br/>
									 <input id="widget-monochrome" type="radio" name="pulsemaps_widget" value="monochrome" <?php checked('monochrome', $style); ?> />
	 <label for="widget-monochrome">Black &amp; White<br/>
     <img class="widget-sample" src="<?php echo plugins_url(); ?>/pulsemaps/widget-monochrome.png"></label><br/>
													<input id="widget-satellite" type="radio" name="pulsemaps_widget" value="satellite" <?php checked('satellite', $style); ?> />
	 <label for="widget-satellite">Satellite<br/>
     <img class="widget-sample" src="<?php echo plugins_url(); ?>/pulsemaps/widget-satellite.png"></label><br/>

<p class="submit">
<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
</p>
</form>
</div>
</div>
<?php
}

add_action('admin_init', 'pulsemaps_admin_init');
function pulsemaps_admin_init(){
	register_setting( 'pulsemaps_options', 'pulsemaps_options', 'pulsemaps_options_validate' );
	register_setting( 'pulsemaps_widget', 'pulsemaps_widget', null );
}

function pulsemaps_section_text() {
}

function pulsemaps_setting_string() {
	$options = get_option('pulsemaps_options');
	echo "<input id='pulsemaps_api_key' name='pulsemaps_options[key]' size='40' type='text' value='{$options['key']}' />";
}

function pulsemaps_options_validate($input) {
	echo "Validating<br>";
	var_dump($input);
	$options = get_option('pulsemaps_options', array());
	$options['key'] = trim($input['key']);
	if(!preg_match('/^[a-zA-Z0-9_=-]{27}$/i', $options['key'])) {
		unset($options['key']);
	}
	$options['id'] = trim($input['id']);
	if(!preg_match('/^[1-9][0-9]{8}$/', $options['id'])) {
		unset($options['id']);
	}

	echo "Saving<br>";
	var_dump($options);
	return $options;
}
