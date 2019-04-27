<?php
include('lib.php');
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
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

  $end = aget($data, 'end_datetime');
  if($end) {
    db_update('sessions', $row['id'], [
      'end'     => db_date($end),
      'kwh'     => aget($data, 'kwh'),
      'cost'    => aget($data, 'total_cost'),
      'status'  => db_string(aget($data, 'status'))
    ]);
  }
}

file_put_contents("/tmp/session-info.tmp", date('c') . "|$raw\n", FILE_APPEND);
