<?php

global $pulsemaps_api;
$pulsemaps_api = 'http://api.pulsemaps.com';

global $pulsemaps_site;
$pulsemaps_site = 'http://pulsemaps.com';

global $pulsemaps_version;
$pulsemaps_version = 8;

$local_config = dirname(__FILE__) . '/pm-config-local.php';
if (file_exists($local_config)) {
	require($local_config);
}
