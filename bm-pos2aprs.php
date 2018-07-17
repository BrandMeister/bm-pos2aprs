#!/usr/bin/php
<?php
	define('SESSION_TYPE_FLAG_KNOWN',		(1 << 0));
	define('SESSION_TYPE_FLAG_GROUP',		(1 << 1));
	define('SESSION_TYPE_FLAG_VOICE',		(1 << 2));
	define('SESSION_TYPE_FLAG_DATA',		(1 << 3));
	define('SESSION_TYPE_FLAG_CONTROL',		(1 << 4));
	define('SESSION_TYPE_FLAG_PRIVACY',		(1 << 5));

	define('SESSION_TYPE_UNKNOWN',			0);
	define('SESSION_TYPE_PRIVATE_VOICE',	(SESSION_TYPE_FLAG_KNOWN | SESSION_TYPE_FLAG_VOICE));
	define('SESSION_TYPE_GROUP_VOICE',		(SESSION_TYPE_FLAG_KNOWN | SESSION_TYPE_FLAG_VOICE| SESSION_TYPE_FLAG_GROUP));
	define('SESSION_TYPE_PRIVATE_DATA',		(SESSION_TYPE_FLAG_KNOWN | SESSION_TYPE_FLAG_DATA));
	define('SESSION_TYPE_GROUP_DATA',		(SESSION_TYPE_FLAG_KNOWN | SESSION_TYPE_FLAG_DATA | SESSION_TYPE_FLAG_GROUP));
	define('SESSION_TYPE_CONTROL_BLOCK',	(SESSION_TYPE_FLAG_KNOWN | SESSION_TYPE_FLAG_CONTROL));

	define('SESSION_STATE_INITIAL',			0);
	define('SESSION_STATE_PROGRESS',		1);
	define('SESSION_STATE_FINAL',			2);

	ini_set('display_errors','On');
	error_reporting(E_ALL);

	chdir(dirname(__FILE__));

	include('config.inc.php');
	include('mqtt.inc.php');
	include('tnt.inc.php');
	include('dbus.inc.php');
	include('aprs.inc.php');
	include('helper.inc.php');
	include(PHPMQTT_PATH);

	echo '[' . date('H:i:s') . "] connecting to mqtt...\n";
	$mqtt = new phpMQTT('localhost', 1883, "Server-API".rand());
	if (!$mqtt->connect()) {
		echo "error: can't connect to mqtt\n";
		return 1;
	}

	$topics['Master/' . NETWORK_ID . '/Session/#'] = array('qos' => 0, 'function' => 'mqtt_procmsg');
	$topics['Master/' . NETWORK_ID . '/Service/#'] = array('qos' => 0, 'function' => 'mqtt_procmsg');
	$mqtt->subscribe($topics, 0);

	echo '[' . date('H:i:s') . "] connecting to aprs...\n";
	$aprs_socket = aprs_connect();
	if ($aprs_socket === false)
		return 1;

	$db = array();

	echo '[' . date('H:i:s') . "] starting main loop\n";
	while ($mqtt->proc()) {
		// Checking if APRS socket is readable.
		$read = array($aprs_socket);
		$write = NULL;
		$except = NULL;
		if (socket_select($read, $write, $except, 0) === FALSE) {
			echo "socket_select() error\n";
			break;
		}
		// If it's readable, read it.
		if (in_array($aprs_socket, $read)) {
			$lines = socket_read($aprs_socket, 1000, PHP_NORMAL_READ);
			if ($lines === FALSE) {
				echo '[' . date('H:i:s') . "] aprs disconnected, reconnecting\n";
				$aprs_socket = aprs_connect();
				if ($aprs_socket === false)
					break;
			}
			$lines = explode("\n", $lines);
			foreach ($lines as $line) {
				$line = trim($line);
				if ($line && $line[0] != '#') // Ignoring comments.
					aprs_process_received_line($line);
			}
		}
	}

	$mqtt->close();
	socket_close($aprs_socket);
?>
