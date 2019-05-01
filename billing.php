<?
include('lib.php');

$key = 'wfI8FEOVTaKOkXeF7QczhA';
if(aget($_GET, 'key') !== $key) {
  return do_error('Bad key');
}

$verb = $_SERVER['REQUEST_METHOD'];
if($verb === 'GET') {
  $append = false;
  $user = intval(aget($_GET, 'user'));
  if($user) {
    $append = "and user = $user";
  }

	return do_success(db_all("select * from sessions where paid='false' $append"));
}

if($verb === 'POST' || $verb === 'PUT') {
	$raw = file_get_contents('php://input');
	$data = json_decode($raw, true);
	if($data) {
		// filter out non-numeric
		$data = array_map(function($row) { return intval($row);}, $data);
		$str = implode(',', $data);
    $res = db_one("update sessions set paid='true' where id in ($str)");
    do_success($res);
  } else {
    do_error('Bad Data');
  }
}

