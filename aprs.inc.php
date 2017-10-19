<?php
	function aprs_connect() {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if (!$socket) {
			echo "error: can't create aprs socket\n";
			return false;
		}

		if (!socket_connect($socket, APRS_SERVER, APRS_SERVER_PORT)) {
			echo "error: can't connect to aprs server\n";
			return false;
		}

		$tosend = 'user ' . APRS_CALLSIGN . ' pass ' . APRS_PASSCODE . "\n";
		socket_write($socket, $tosend, strlen($tosend));
		$authstartat = time();
		$authenticated = false;
		while ($msgin = socket_read($socket, 1000, PHP_NORMAL_READ)) {
			if (strpos($msgin, APRS_CALLSIGN . ' verified') !== FALSE) {
				$authenticated = true;
				break;
			}
			// Timeout handling
			if (time()-$authstartat > 5)
				break;
		}
		if (!$authenticated) {
			echo "error: aprs auth timeout\n";
			return false;
		}
		return $socket;
	}

	function aprs_decimal_degrees_to_dms($decimal_degrees) {
		$result = array();

		$result['degrees'] = floor($decimal_degrees);
		$result['minutes'] = fmod(floor($decimal_degrees * 60), 60);
		$result['seconds'] = fmod(($decimal_degrees * 3600), 60);

		return $result;
	}

	function aprs_send_location($location) {
		global $db, $aprs_socket;

		$repeaterid = $db[$location->SourceID]['repeaterid'];
		$dstid = $db[$location->SourceID]['dstid'];
		echo "  sending location to aprs for $location->SourceID\n";
		echo "    dstid: $dstid\n";
		echo "    repeater: $repeaterid\n";
		echo "    data:\n";
		foreach (get_object_vars($location) as $key => $value)
			echo "      $key: $value\n";

		$tnt = tnt_connect();
		if ($tnt === false)
			return;

		$src_callsign = tnt_get_callsign_for_dmr_id($tnt, $location->SourceID);
		if ($src_callsign === false) {
			echo "    can't get source callsign\n";
			return;
		}
		echo "    src callsign: $src_callsign\n";

		$dbus_data = dbus_get_data();
		$repeater_callsign = dbus_get_callsign_for_dmr_id($dbus_data, $repeaterid);
		if ($repeater_callsign === false || $repeater_callsign == '') {
			echo "    can't get repeater callsign, searching for id in users\n";

			$repeater_callsign = tnt_get_callsign_for_dmr_id($tnt, $repeaterid);
			if ($repeater_callsign === false) {
				echo "    can't get repeater callsign\n";
				return;
			}
		}
		echo "    repeater callsign: $repeater_callsign\n";

		$aprs_text = tnt_get_aprs_text_for_dmr_id($tnt, $location->SourceID);
		if ($aprs_text === false)
			$aprs_text = 'DMR ID: ' . $location->SourceID;
		echo "    aprs text: $aprs_text\n";

		$timestamp = date('dHi');

		$degrees = aprs_decimal_degrees_to_dms(abs($location->Latitude));
		$hundredths = round(($degrees['seconds']/60)*100);
		$latitude = str_pad($degrees['degrees'], 2, '0', STR_PAD_LEFT) .
			str_pad($degrees['minutes'], 2, '0', STR_PAD_LEFT) . '.' .
			str_pad($hundredths, 2, '0', STR_PAD_LEFT) .
			($location->Latitude > 0 ? 'N' : 'S');

		$degrees = aprs_decimal_degrees_to_dms(abs($location->Longitude));
		$hundredths = round(($degrees['seconds']/60)*100);
		$longitude = str_pad($degrees['degrees'], 3, '0', STR_PAD_LEFT) .
			str_pad($degrees['minutes'], 2, '0', STR_PAD_LEFT) . '.' .
			str_pad($hundredths, 2, '0', STR_PAD_LEFT) .
			($location->Longitude > 0 ? 'E' : 'W');

		$course = (isset($location->Course) ? $location->Course : 0);
		$coursespeed = sprintf('%03u/%03u', $course, $location->Speed);

		if (isset($location->Altitude))
			$aprs_text = '/A=' . str_pad(round($location->Altitude*3.28084), 6, '0', STR_PAD_LEFT) . $aprs_text;

		if (USE_MULTIPLE_SERVICE_IDS) {
			$aprs_symbol1 = '/';
			if ($dstid == LOCATION_SERVICE_ID0) {
				$aprs_symbol2 = '-';
				$gpspos_callsign = "$src_callsign";
			} else if ($dstid == LOCATION_SERVICE_ID1) {
				$aprs_symbol2 = '=';
				$gpspos_callsign = "$src_callsign-1";
			} else if ($dstid == LOCATION_SERVICE_ID2) {
				$aprs_symbol2 = 'F';
				$gpspos_callsign = "$src_callsign-2";
			} else if ($dstid == LOCATION_SERVICE_ID3) {
				$aprs_symbol2 = 'k';
				$gpspos_callsign = "$src_callsign-3";
			} else if ($dstid == LOCATION_SERVICE_ID4) {
				$aprs_symbol2 = 'v';
				$gpspos_callsign = "$src_callsign-4";
			} else if ($dstid == LOCATION_SERVICE_ID5) {
				$aprs_symbol2 = '$';
				$gpspos_callsign = "$src_callsign-5";
			} else if ($dstid == LOCATION_SERVICE_ID6) {
				$aprs_symbol2 = ';';
				$gpspos_callsign = "$src_callsign-6";
			} else if ($dstid == LOCATION_SERVICE_ID7) {
				$aprs_symbol2 = '[';
				$gpspos_callsign = "$src_callsign-7";
			} else if ($dstid == LOCATION_SERVICE_ID8) {
				$aprs_symbol2 = '<';
				$gpspos_callsign = "$src_callsign-8";
			} else if ($dstid == LOCATION_SERVICE_ID9) {
				$aprs_symbol2 = '>';
				$gpspos_callsign = "$src_callsign-9";
			}
		} else {
			$symbol_ssid = tnt_get_aprs_symbol_and_ssid_for_dmr_id($tnt, $location->SourceID);
			if (!$symbol_ssid) {
				$aprs_symbol1 = '/';
				$aprs_symbol2 = '[';
				$gpspos_callsign = "$src_callsign-7";
			} else {
				$aprs_symbol1 = $symbol_ssid[0][0];
				$aprs_symbol2 = $symbol_ssid[0][1];
				$gpspos_callsign = "$src_callsign-" . $symbol_ssid[1];
			}
		}

		$tnt->disconnect();

		$tosend = "$gpspos_callsign>APBM1S,$repeater_callsign*,qAR,$repeater_callsign:@${timestamp}z" .
			"$latitude$aprs_symbol1$longitude$aprs_symbol2$coursespeed$aprs_text\n";

		echo "    aprs data: $tosend";
		if (socket_write($aprs_socket, $tosend, strlen($tosend)) === false)
			echo "    send failed\n";
	}

	function aprs_remove_ssid_from_callsign($callsign) {
		if (strpos($callsign, '-') === FALSE)
			return $callsign;
		$callsign = explode('-', $callsign);
		return $callsign[0];
	}

	function aprs_get_ssid_from_callsign($callsign) {
		if (strpos($callsign, '-') === FALSE)
			return '';
		$callsign = explode('-', $callsign);
		return $callsign[1];
	}

	function aprs_process_received_line($line) {
		echo '[' . date('H:i:s') . "] got aprs data: $line\n";
		if (strpos($line, '::') === FALSE) {
			echo "  not a message, ignoring\n";
			return;
		}

		$src_callsign = explode('>', $line);
		$src_callsign = trim($src_callsign[0]);
		if (!strlen($src_callsign))
			return;
		echo "  src callsign: $src_callsign\n";

		$msg_data = substr($line, strpos($line, '::')+2);
		$dst_callsign_endpos = strpos($msg_data, ':');
		$dst_callsign = trim(substr($msg_data, 0, $dst_callsign_endpos));
		echo "  dst callsign: $dst_callsign\n";
		$msg_text = trim(substr($msg_data, $dst_callsign_endpos+1));
		echo "  msg text: $msg_text\n";

		$tnt = tnt_connect();
		if ($tnt === false)
			return;

		$dbus_data = dbus_get_data();
		$dst_id = dbus_get_dmr_id_for_callsign($dbus_data, $dst_callsign);
		if (!$dst_id) {
			$dst_id = dbus_get_dmr_id_for_callsign($dbus_data, aprs_remove_ssid_from_callsign($dst_callsign));
			if (!$dst_id) {
				echo "  dst dmr id can't be resolved\n";
				return;
			}
		}
		echo "  dst dmr id: $dst_id\n";
		$src_id = dbus_get_dmr_id_for_callsign($dbus_data, $src_callsign);
		if (!$src_id) {
			$src_id = dbus_get_dmr_id_for_callsign($dbus_data, aprs_remove_ssid_from_callsign($src_callsign));
			if (!$src_id) {
				echo "  src dmr id can't be resolved\n";
				return;
			}
		}
		echo "  src dmr id: $src_id\n";

		$tnt->disconnect();

		mqtt_send_sms($dst_id, $src_id, $msg_text);
	}
?>
