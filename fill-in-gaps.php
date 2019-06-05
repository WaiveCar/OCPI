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

$earliest = db_one('select min(start) from sessions where cost is null');
var_dump($earliest);exit;
$res = curldo('sessions', ['date_from' => '2019-05-28T22:35:04Z']);

if (!empty($res['data'])) {
  foreach($res['data'] as $session) {
    echo "${session['id']} ${session['kwh']} ${session['total_cost']}\n";
  }
}
