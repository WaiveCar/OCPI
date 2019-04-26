<?php
include('lib.php');
$data = json_decode(file_get_contents('php://input'), true);

$evse = aget($data, 'location.id');
$row = db_get($data['id']);
$user = reservation($evse);

if(!$row) {
  $row = ['id' => db_insert('sessions', [
      'start' 	=> aget($data, 'start_datetime'),
      'session' => aget($data, 'id'),
      'user'    => $user,
      'evse' 		=> $evse,
      'status' 	=> aget($data, 'status'),
    ])
  ];
} 

db_update('sessions', $row['id'], [
  'end'     => aget($data, 'end_datetime'),
  'kwh'     => aget($data, 'kwh'),
  'cost'    => aget($data, 'total_cost'),
  'status'  => aget($data, 'status')
]);

file_put_contents(
  "/tmp/session-info.tmp", json_encode([
    'time' => date('c'),
    'data' => $data
  ]) . "\n", FILE_APPEND);
