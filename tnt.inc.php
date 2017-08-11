<?php
	function tnt_connect() {
		$tnt = new Tarantool(TNT_HOST, TNT_PORT, TNT_USER, TNT_PASSWORD);
		if (!$tnt->ping()) {
			echo "error: can't connect to tarantool\n";
			return false;
		}
		return $tnt;
	}

	function tnt_get_callsign_for_dmr_id($tnt, $id) {
		$res = $tnt->select('Profiles', $id);
		if (count($res) < 1)
			return false;
		return $res[0][6];
	}

	function tnt_get_aprs_text_for_dmr_id($tnt, $id) {
		$res = $tnt->select('Profiles', $id);
		if (count($res) < 1)
			return false;
		return $res[0][9];
	}

	function tnt_get_aprs_symbol_and_ssid_for_dmr_id($tnt, $id) {
		$res = $tnt->select('Profiles', $id);
		if (count($res) < 1)
			return false;
		return array($res[0][8], $res[0][7]);
	}
?>
