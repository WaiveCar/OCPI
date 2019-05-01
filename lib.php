<?php

$DB = false;
$REDIS = false;

$schema = [
  'sessions' => [
    'id'     => 'INTEGER PRIMARY KEY', 
    'start'  => 'TIMESTAMP', 
    'end'    => 'TIMESTAMP',
    'session'=> 'INTEGER',
    'kwh'    => 'FLOAT',
    'cost'   => 'FLOAT',
    'evse'   => 'INTEGER',
    'user'   => 'INTEGER',
    'status' => 'TEXT',
    'paid'   => 'BOOLEAN default false'
  ]
];

function db_string($what) {
  return "'$what'";
}

function db_date($what) {
  $what = db_string(trim($what, '"\''));
  return "datetime($what)";
}


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
  $db = get_db();
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

function get_db() {
  global $DB;
  if(!$DB) {
    $DB = new SQLite3(__DIR__ . "/../../ocpi.db");
  }
  return $DB;
}

function db_one($qstr) {
  $db = get_db();
  return $db->querySingle($qstr, true);
}

function db_get($key) {

  if(empty($key)) {
    return false;
  }

  $db = get_db();

  $qstr = "select * from sessions where session=$key";
  error_log($qstr);

  return $db->querySingle($qstr, true);
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

  $row = db_one("select * from sessions where status='active' and evse=$id");
  if($row) {
    db_update('sessions', $row['id'], ['user' => $user]);
  }

  // we give it some time to start
  $redis->set($key, $user, 240);
}

function db_update($table, $id, $kv) {
  $fields = [];

  $db = get_db();

  foreach($kv as $k => $v) {
    $fields[] = "$k=$v";
  } 

  $fields = implode(',', $fields);

  $qstr = "update $table set $fields where id=$id";
  error_log($qstr);
  if(!empty($id)) {
    return $db->exec($qstr);
  }
}

function db_insert($table, $kv) {
  $fields = [];
  $values = [];

  $db = get_db();

  foreach($kv as $k => $v) {
    $fields[] = $k;
    if($v === false) {
      $values[] = 'false';
    } else {
      $values[] = $v;
    }
  } 

  $values = implode(',', $values);
  $fields = implode(',', $fields);

  $qstr = "insert into $table($fields) values($values)";
  error_log($qstr);

  if($db->exec($qstr)) {
    return $db->lastInsertRowID();
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


