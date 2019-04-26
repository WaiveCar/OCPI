<?php

$db = new SQLite3(__DIR__ . "/../../ocpi.db");
$REDIS = false;

$schema = [
  'sessions' => [
    'id'     => 'INTEGER PRIMARY KEY', 
    'start'  => 'TIMESTAMP', 
    'end'    => 'TIMESTAMP',
    'session'=> 'INTEGER',
    'kwh'    => 'FLOAT',
    'cost'	 => 'FLOAT',
    'evse'   => 'INTEGER',
    'user'   => 'INTEGER',
    'status' => 'TEXT'
  ]
];

function aget($source, $keyList, $default = null) {
  if(!is_array($keyList)) {
    $keyStr = $keyList;
    $keyList = explode('.', $keyStr);

    $orList = explode('|', $keyStr);
    if(count($orList) > 1) {

      $res = null;
      foreach($orList as $key) {
        // this resolves to the FIRST valid value
        if($res === null) {
          $res = aget($source, $key);
        }
      }
      return ($res === null) ? $default : $res;
    }   
  }
  $key = array_shift($keyList);

  if($source && isset($source[$key])) {
    if(count($keyList) > 0) {
      return aget($source[$key], $keyList);
    } 
    return $source[$key];
  }

  return $default;
}

function get_column_list($table_name) {
  $db = db_connect();
  $res = $db->query("pragma table_info( $table_name )");

  return array_map(function($row) { 
    return $row['name'];
  }, sql_all($res));
}

function get_redis() {
  global $REDIS;
  if(!$REDIS) {
    $REDIS = new Redis();
    $REDIS->connect('127.0.0.1');
  }
  return $REDIS;
}

function db_connect() {
  global $db;
  return $db;
}

function db_get($key) {
  global $db;
  $key = $db->escapeString($key);
  return $db->querySingle("select id from sessions where session='$key'");
}

function reservation($id, $user = false) {
  if(!$id) {
    return false;
  }
  $key = "evse:$id";
  $redis = get_redis();
  if($user === false) {
    return $redis->get($key);
  }

  // we give it 60 seconds to start
  $redis->set($key, $user, 60);
}

function db_update($table, $id, $kv) {
  $fields = [];

  $db = db_connect();

  foreach($kv as $k => $v) {
    $fields[] = "$k=".$db->escapeString($v);
  } 

  $fields = implode(',', $fields);

  return $db->exec("update $table set $fields where id = $id");
}

function db_insert($table, $kv) {
  $fields = [];
  $values = [];

  $db = db_connect();

  foreach($kv as $k => $v) {
    $fields[] = $k;
    if($v === false) {
      $values[] = 'false';
    } else {
      $values[] = $v;//db->escapeString($v);
    }
  } 

  $values = implode(',', $values);
  $fields = implode(',', $fields);

  $qstr = "insert into $table($fields) values($values)";

  try {
    if($db->exec($qstr)) {
      return $db->lastInsertRowID();
    } 
  } catch(Exception $ex) { 
    error_log($qstr);
    error_log($ex);
  }
  return $qstr;
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


