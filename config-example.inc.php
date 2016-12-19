<?php
	define('PHPMQTT_PATH',					'phpMQTT/phpMQTT.php');

	// Network ID from BrandMeister.conf
	define('NETWORK_ID',					2162);

	// If USE_MULTIPLE_SERVICE_IDS is true, the APRS symbol of position
	// reports depend on the used destination ARS/RRS ID, not the one set
	// in SelfCare. For example, if you set your radio's ARS/RRS ID
	// to the ID of LOCATION_SERVICE_ID9, you will have the symbol of a car.
	// If you set your radio's ARS/RRS ID to the ID of LOCATION_SERVICE_ID7,
	// you will have the symbol of a walking person.
	//
	// If USE_MULTIPLE_SERVICE_IDS is false, your APRS symbol will be set to
	// the BM SelfCare symbol (you have to send the ARS/RRS data to
	// LOCATION_SERVICE_ID0 in this case).
	define('USE_MULTIPLE_SERVICE_IDS',		true);
	define('LOCATION_SERVICE_ID0',			5050);
	define('LOCATION_SERVICE_ID1',			5051);
	define('LOCATION_SERVICE_ID2',			5052);
	define('LOCATION_SERVICE_ID3',			5053);
	define('LOCATION_SERVICE_ID4',			5054);
	define('LOCATION_SERVICE_ID5',			5055);
	define('LOCATION_SERVICE_ID6',			5056);
	define('LOCATION_SERVICE_ID7',			5057);
	define('LOCATION_SERVICE_ID8',			5058);
	define('LOCATION_SERVICE_ID9',			5059);

	// User, passcode, server used for connecting to the APRS network.
	define('APRS_CALLSIGN',					'HA2NON-15');
	define('APRS_PASSCODE',					0);
	define('APRS_SERVER',					'hun.aprs2.net');
	define('APRS_SERVER_PORT',				14580);

	// MySQL user, password, host, Registry database name and table names.
	define('DMR_DB_USER',					'ham-dmr.hu');
	define('DMR_DB_PASSWORD',				'');
	define('DMR_DB_HOST',					'localhost');
	define('DMR_DB_NAME',					'Registry');
	define('DMR_DB_USERS_TABLE',			'Users');
	define('DMR_DB_REPEATERS_TABLE',		'Repeaters');
?>
