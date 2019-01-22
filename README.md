# bm-pos2aprs

Uploads received position data from Brandmeister to APRS-IS.

Brandmeister uploads position data to APRS-IS as simple APRS objects, without
correct path information. bm-pos2aprs listens on MQTT for position data from
Brandmeister, and uploads them to the APRS-IS with correct station->repeater
paths. The script also takes care of transmitting received APRS messages as
short DMR messages over Brandmeister.

Additionally you can turn on the usage of multiple service IDs. In this case
the APRS symbol of position reports depend on the used destination ARS/RRS ID,
not the one set in SelfCare. For example, if you set your radio's ARS/RRS ID
to the ID of LOCATION_SERVICE_ID9, you will have the symbol of a car.
If you set your radio's ARS/RRS ID to the ID of LOCATION_SERVICE_ID7,
you will have the symbol of a walking person.

Location service ID and symbol mappings:

- LOCATION_SERVICE_ID0 (5050 by default): ![House](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-house.png)
- LOCATION_SERVICE_ID1 (5051 by default): ![Train](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-train.png)
- LOCATION_SERVICE_ID2 (5052 by default): ![Tractor](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-tractor.png)
- LOCATION_SERVICE_ID3 (5053 by default): ![Pickup](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-pickup.png)
- LOCATION_SERVICE_ID4 (5054 by default): ![Van](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-van.png)
- LOCATION_SERVICE_ID5 (5055 by default): ![Phone](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-phone.png)
- LOCATION_SERVICE_ID6 (5056 by default): ![Camp](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-camp.png)
- LOCATION_SERVICE_ID7 (5057 by default): ![Person](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-person.png)
- LOCATION_SERVICE_ID8 (5058 by default): ![Bike](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-bike.png)
- LOCATION_SERVICE_ID9 (5059 by default): ![Car](https://cdn.rawgit.com/BrandMeister/bm-pos2aprs/master/contrib/symbol-car.png)

## Usage

- You'll need PHP CLI (ex. php5-cli).
- Rename (and edit) *config-example.inc.php* to *config.inc.php*.
- You have to set the APRSGate's number to a not used number (for ex. 5060) in
  BrandMeister.conf. This ensures that Brandmeister will not upload positions on
  it's own, and lets this script do the job.
- Give read permissions to the Profiles space in Tarantool.

  Execute the following to open Tarantool's console:
  ```
  tarantoolctl connect /tmp/Registry.sock
  ```

  Then enter:
  ```
  box.schema.user.grant('api', 'read', 'space', 'GlobalProfiles',{if_not_exists = true})
  ```

- To use multiple service IDs, you have to adjust ServiceWrapper settings in
  BrandMeister.conf for all LOCATION_SERVICE_ID**x** setting:

  ```
  // ServiceWrapper Application
  ServiceWrapper :
  {
    // List of mapped service IDs:
    // <Type>, <Private ID>
    // Where <Type> in:
    //   1 - Registration Service
    //   2 - Messaging Service
    //   3 - Telemetry Service
    //   4 - Location Service

    numbers =
    [
      1, 216999,
      2, 216990,
      3, 216999,
      4, 5050,
      4, 5051,
      4, 5052,
      4, 5053,
      4, 5054,
      4, 5055,
      4, 5056,
      4, 5057,
      4, 5058,
      4, 5059
    ];
  };
  ```

### Running as a daemon

It's not ideal to run command line PHP scripts as daemons, but I had most of
the routines used here in other projects, so I wrote this in PHP. Feel free
to rewrite this to a better language.
