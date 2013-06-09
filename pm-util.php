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

ini_set('track_errors', 1);


function pulsemaps_post_request($url, $data, $optional_headers = null) {
	$params = array('http' => array('method' => 'POST',
									'content' => http_build_query($data)));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Cannot connect to $url: $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Cannot read from $url: $php_errormsg");
	}
	return $response;
}

function pulsemaps_call_json($url, $data) {
    return json_decode(pulsemaps_post_request($url, $data), true);
}
