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

