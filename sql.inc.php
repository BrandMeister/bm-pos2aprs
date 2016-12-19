<?php
	function sql_connect() {
		$sql = mysqli_connect(DMR_DB_HOST, DMR_DB_USER, DMR_DB_PASSWORD, DMR_DB_NAME);
		if (!$sql) {
			echo "error: can't connect to mysql database!\n";
			return false;
		}

		// Making sure we are using UTF8 for everything.
		$sql->query("set names 'utf8'");
		$sql->query("set charset 'utf8'");

		return $sql;
	}

	function sql_get_callsign_for_dmr_id($sql, $id, $table) {
		$query_response = $sql->query('select `Call` from `' . $table . '` ' .
			'where `ID`="' . $sql->escape_string($id) . '"');
		if (!$query_response) {
			echo "mysql query error: $sql->error\n";
			return false;
		}
		$row = $query_response->fetch_row();
		return $row[0];
	}

	function sql_get_dmr_id_for_callsign($sql, $callsign, $ssid = '') {
		$ssid_condition = '';
		if ($ssid)
			$ssid_condition = ' and `SSID` = "' . $sql->escape_string($ssid) . '"';
		$query_response = $sql->query('select `ID` from `' . DMR_DB_USERS_TABLE . '` ' .
			'where `Call`="' . $sql->escape_string($callsign) . '"' . $ssid_condition);
		if (!$query_response) {
			echo "mysql query error: $sql->error\n";
			return false;
		}
		$row = $query_response->fetch_row();
		return $row[0];
	}

	function sql_get_aprs_text_for_dmr_id($sql, $id) {
		$query_response = $sql->query('select `Text` from `Users` ' .
			'where `ID`="' . $sql->escape_string($id) . '"');
		if (!$query_response) {
			echo "mysql query error: $sql->error\n";
			return false;
		}
		$row = $query_response->fetch_row();
		return $row[0];
	}

	function sql_get_aprs_symbol_and_ssid_for_dmr_id($sql, $id) {
		$query_response = $sql->query('select `Symbol`,`SSID` from `Users` ' .
			'where `ID`="' . $sql->escape_string($id) . '"');
		if (!$query_response) {
			echo "mysql query error: $sql->error\n";
			return false;
		}
		return $query_response->fetch_row();
	}
?>
