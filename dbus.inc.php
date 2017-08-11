<?php
	function dbus_get_data() {
		$connection = new DBus(DBus::BUS_SYSTEM, true);
		$service = "me.burnaway.BrandMeister.N" . NETWORK_ID;
		$proxy = $connection->createProxy($service, "/me/burnaway/BrandMeister", "me.burnaway.BrandMeister");
		$result = $proxy->getContextList();
		$results = array();

		if (is_object($result) && get_class($result) == 'DbusArray') {
			$list = $result->getData();

			foreach ($list as $banner) {
				$result = $proxy->getRepeaterData($banner);

				if (is_object($result) && get_class($result) == 'DbusSet')
					$results[] = $result->getData();
			}
		}
		return $results;
	}

	function dbus_get_callsign_for_dmr_id($dbus_data, $id) {
		foreach ($dbus_data as $set) {
			if ($set[1] == $id)
				return $set[2];
		}
		return false;
	}

	function dbus_get_dmr_id_for_callsign($dbus_data, $callsign) {
		foreach ($dbus_data as $set) {
			if ($set[2] == $callsign)
				return $set[1];
		}
		return false;
	}
?>
