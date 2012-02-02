<?php

/*  Copyright 2011-2012 Aito Software Inc. (email : contact@aitosoftware.com)

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
$c = curl_init($pulsemaps_url . $_POST['path']);
curl_setopt($c, CURLOPT_POSTFIELDS, $_POST);
curl_setopt($c, CURLOPT_HEADER, true);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($c);
$info = curl_getinfo($c);
curl_close($c);
$header_size = $info["header_size"];
$headers = substr($data, 0, $header_size);
foreach (explode("\n", $headers) as $header) {
	$header = rtrim($header);
	if ($header && strpos($header, 'Transfer-Encoding') === false) {
		header($header);
	}
}

echo substr($data, $header_size);
