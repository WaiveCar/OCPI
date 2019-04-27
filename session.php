<?php
include('lib.php');
$data = json_decode(file_get_contents('php://input'), true);
if($data) {

  $evse = aget($data, 'location.id');
  $row = db_get(aget($data, 'id'));

  if(!$row) {
    $insert = [
      'start' 	=> db_date(aget($data, 'start_datetime')),
      'session' => aget($data, 'id'),
      'evse'    => $evse,
      'status' 	=> db_string(aget($data, 'status')),
    ];

    $user = reservation($evse);
    if($user) {
      $insert['user'] = $user;
    }

    $row = ['id' => db_insert('sessions', $insert)];
  } 

  db_update('sessions', $row['id'], [
    'end'     => db_date(aget($data, 'end_datetime')),
    'kwh'     => aget($data, 'kwh'),
    'cost'    => aget($data, 'total_cost'),
    'status'  => db_string(aget($data, 'status'))
  ]);
}

file_put_contents(
  "/tmp/session-info.tmp", json_encode([
    'time' => date('c'),
    'data' => $data
  ]) . "\n", FILE_APPEND);
