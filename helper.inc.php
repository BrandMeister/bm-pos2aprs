<?php
	function is_dstid_valid($id) {
		return ($id == LOCATION_SERVICE_ID0 ||
			$id == LOCATION_SERVICE_ID1 ||
			$id == LOCATION_SERVICE_ID2 ||
			$id == LOCATION_SERVICE_ID3 ||
			$id == LOCATION_SERVICE_ID4 ||
			$id == LOCATION_SERVICE_ID5 ||
			$id == LOCATION_SERVICE_ID6 ||
			$id == LOCATION_SERVICE_ID7 ||
			$id == LOCATION_SERVICE_ID8 ||
			$id == LOCATION_SERVICE_ID9);
	}
?>
