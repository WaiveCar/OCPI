<?php
$data = json_decode(file_get_contents('php://input'), true);

file_put_contents(
  "/tmp/session-info.tmp", json_encode([
    'time' => date('c'),
    'request' => json_encode($_REQUEST),
    'data' => $data
  ]) . "\n", FILE_APPEND);
