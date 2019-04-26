<?php

$db = new SQLite3(__DIR__ . "/../../ocpi.db");
$schema = [
  'sessions' => [
    'id'     => 'INTEGER PRIMARY KEY', 
    'start'  => 'TIMESTAMP', 
    'end'    => 'TIMESTAMP',
    'session'=> 'INTEGER',
    'kwh'    => 'FLOAT',
    'evse'   => 'INTEGER',
    'status' => 'TEXT'
  ]
];

$data = json_decode(file_get_contents('php://input'), true);

file_put_contents(
  "/tmp/session-info.tmp", json_encode([
    'time' => date('c'),
    'request' => json_encode($_REQUEST),
    'data' => $data
  ]) . "\n", FILE_APPEND);
