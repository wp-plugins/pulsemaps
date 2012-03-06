<?php

global $pulsemaps_url;
$pulsemaps_url = 'https://pulsemaps.com';

global $pulsemaps_version;
$pulsemaps_version = 10;

$local_config = dirname(__FILE__) . '/pm-config-local.php';
if (file_exists($local_config)) {
	require($local_config);
}
