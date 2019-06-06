#!/usr/bin/php
<?php

include('lib.php');
function curldo($url, $params = false, $opts = []) {
  $HOST = 'https://op.evgo.com/externalIncoming/ocpi/cpo/2.1.1';

  $ch = curl_init();
  $url = '/' . ltrim($url, '/');
  if($params) {
    $url .= '?' . http_build_query($params);
  }

  $header[] = "Authorization: Token 7e64ef7b-20cb-447c-92e0-253605c4edf7";
    
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_URL, $HOST . $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $res = curl_exec($ch);
  
  /*
  $tolog = json_encode([
      'verb' => $verb,
      'header' => $header,
      'url' => $url,
      'params' => $params,
      'res' => $res
  ]);
  var_dump(['>>>', curl_getinfo ($ch), json_decode($tolog, true)]);
  

  file_put_contents('/tmp/log.txt', $tolog, FILE_APPEND);
   */

  if(isset($opts['raw'])) {
    return $res;
  }
  $resJSON = @json_decode($res, true);
  if($resJSON) {
    return $resJSON;
  }
  return $res;
}

# We don't really want to worry about timezones 
# nor do we want to get into a nasty loop so we 
# give a nice baseline that's not too far back 
# in time that it may give us some edge condition
# issues but also far back enough so that every session
# we want to see we do see!
$earliest = db_one('select datetime(min(start), "-18 hour") as m from sessions where cost is null');

if(empty($earliest)) {
  echo "Nothing to do!"
  exit(0);
}

#
# sqlite doesn't seem to give us the ISO format with
# the T and Z thing (see our notion of timestamps from
# above --- where I objectively just don't give a shit)
#
# so let's fix that here.
#
$earliest = str_replace(' ', 'T', $earliest['m']) . 'Z';

$emptyList = array_map(function($row) { return $row['session']; }, db_all('select session from sessions where cost is null order by id asc'));

$res = curldo('sessions', ['date_from' => $earliest]);

$db = get_db();
if (!empty($res['data'])) {
  foreach($res['data'] as $session) {
    if (array_search($session['id'], $emptyList) !== false) {
      $qstr = "update sessions set status='${session['status']}', cost=${session['total_cost']} where session=${session['id']}\n";
      $db->exec($qstr);
    }
  }
}
