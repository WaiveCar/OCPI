<?
include('lib.php');

$key = 'wfI8FEOVTaKOkXeF7QczhA';
if(aget($_GET, 'key') !== $key) {
  return do_error('Bad key');
}

$append = false;
$user = intval(aget($_GET, 'user'));
if($user) {
  $append = "and user = $user";
}

$verb = $_SERVER['REQUEST_METHOD'];
if($verb === 'GET') {
  return do_success(db_all("select * from sessions where status='COMPLETED' and paid='false' $append"));
}

if($verb === 'POST' || $verb === 'PUT') {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if($data) {
    // filter out non-numeric
    $data = array_map(function($row) { return intval($row); }, $data);
    $str = implode(',', $data);
    $res = db_one("update sessions set paid='true' where id in ($str)");
    do_success($data);
  } else {
    do_error('Bad Data');
  }
}

if($verb === 'DELETE') {
  $data = aget($_GET, 'id');
  if($data) {
    $data = explode(',', $data);
    $data = array_map(function($row) { return intval($row); }, $data);
    $str = implode(',', $data);
    $append .= " and id in ($str)";
  }

  $res = db_one("update sessions set paid='false' where paid='true' $append");
  do_success($data);
}

