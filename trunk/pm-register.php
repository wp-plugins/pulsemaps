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
require_once('pm-util.php');


add_action('wp_ajax_pulsemaps_register', 'pulsemaps_register');
function pulsemaps_register() {
	global $pulsemaps_url;
	$data = array('name' => get_option('blogname'),
				  'admin' => get_option('admin_email'),
				  'url' => get_option('home'),
				  'email' => $_POST['email'],
				  'password' => $_POST['password']);

	try {
		$d = pulsemaps_call_json($pulsemaps_url . '/register', $data);
		if ($d['status'] == 'ok') {
			$opts = get_option('pulsemaps_options', array());
			$opts['key'] = $d['key'];
			$opts['id'] = $d['id'];
			update_option('pulsemaps_options', $opts);
			$result = array('status' => 'ok');
		} else {
			$msg = $d['message'];
			if (!$msg) {
				$msg = 'No details';
			}
			$result = array('status' => $d['status'],
							'errorMessage' => $msg);
		}
	} catch (Exception $e) {
		$result = array('status' => 'error',
						'errorMessage' => $e->getMessage());
	}

	header('Content-Type: application/json');
	echo json_encode($result);
	die;
}
