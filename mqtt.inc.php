<?php
	function db_clean() {
		global $db;

		foreach ($db as $db_entry_key => $db_entry) {
			if (time()-$db_entry['ts'] > 6) {
				echo "db clean: removing $db_entry_key\n";
				unset($db[$db_entry_key]);
			}
		}
	}

	function mqtt_procmsg($topic, $msg) {
		global $db;

		$msg = json_decode($msg);
		switch ($msg->Event) {
			case 'Session-Start':
				if (($msg->SessionType != SESSION_TYPE_CONTROL_BLOCK &&
					$msg->SessionType != SESSION_TYPE_PRIVATE_DATA) ||
					!is_dstid_valid($msg->DestinationID))
						return;

				echo '[' . date('H:i:s') . "] session start, storing pos. report start for $msg->SourceID in db\n";
				$db[$msg->SourceID] = array();
				$db[$msg->SourceID]['ts'] = time();
				$db[$msg->SourceID]['repeaterid'] = $msg->ContextID;
				$db[$msg->SourceID]['dstid'] = $msg->DestinationID;
				break;
			case 'Location-Report':
				if (!array_key_exists($msg->SourceID, $db))
					return;

				echo '[' . date('H:i:s') . "] got location report\n";
				aprs_send_location($msg);
				break;
			case 'Session-Stop':
				if (!is_dstid_valid($msg->DestinationID) ||
					!array_key_exists($msg->SourceID, $db))
						return;

				echo '[' . date('H:i:s') . "] session stop, removing $msg->SourceID from db\n";
				unset($db[$msg->SourceID]);
				break;
		}

		db_clean();
	}

	function mqtt_send_sms($dst_id, $src_id, $text) {
		global $mqtt;

		$topic = 'Master/' . NETWORK_ID . "/Outgoing/Message/$src_id/$dst_id";
		$content = mb_convert_encoding($text, 'UTF-16LE');
		$mqtt->publish($topic, $content, 0);
	}
?>
