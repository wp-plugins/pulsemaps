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

while(ob_get_level()) ob_end_clean();
header('Connection: close');
ignore_user_abort();
ob_start();

require_once('config.php');
$c = curl_init($pulsemaps_api . '/mapInfo/wp/');
$data = array('key' => $_POST['key']);
curl_setopt($c, CURLOPT_POSTFIELDS, $data);
curl_exec($c);
$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
curl_close($c);
if ($code != "200") {
	echo "Could not retrieve info from PulseMaps server. Please try again later.";
	die;
}

$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush();
flush();

require('../../../wp-load.php');

$c = curl_init($pulsemaps_api . '/mapInfo.json');
$opts = get_option('pulsemaps_options');
$data = array('key' => $opts['key']);
curl_setopt($c, CURLOPT_POSTFIELDS, $data);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
$ret = curl_exec($c);
$code = curl_getinfo($c, CURLINFO_HTTP_CODE);
curl_close($c);
if ($code == '200') {
	$d = json_decode($ret, true);
	if ($opts['plan'] != $d['planTag']) {
		$opts['plan'] = $d['planTag'];
		update_option('pulsemaps_options', $opts);
	}
}
