<?php
$data = file_get_contents('php://input');
//file_put_contents("/tmp/session.tmp", json_encode($data) . "\n", FILE_APPEND);
if(!empty($data)) {
  file_put_contents("/tmp/session-post.tmp", $data . "\n", FILE_APPEND);
}
//file_put_contents("/tmp/session.tmp", json_encode([$_REQUEST, $_SERVER, getallheaders()]) . "\n", FILE_APPEND);
