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

function get_column_list($table_name) {
  $db = db_connect();
  $res = $db->query("pragma table_info( $table_name )");

  return array_map(function($row) { 
    return $row['name'];
  }, sql_all($res));
}

function db_connect() {
  global $db;
  return $db;
}

function sql_all($sql_res) {
  $res = [];
  while( ($res[] = $sql_res->fetchArray(SQLITE3_ASSOC)) );
  array_pop($res);
  return $res;
}

function sql_kv($hash, $operator = '=', $quotes = "'", $intList = []) {
  $ret = [];
  foreach($hash as $key => $value) {
    if ( is_string($value) ) {
      if(in_array($key, $intList)) {
        $ret[] = "$key $operator $value";
      } else {
        $ret[] = "$key $operator $quotes$value$quotes";
      }
    }
  } 
  return $ret;
}


