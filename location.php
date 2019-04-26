
<?php
$now = gmdate('c');
file_put_contents("/tmp/location.tmp", json_encode(getallheaders()) . "\n", FILE_APPEND);
