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
