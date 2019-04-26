<?php
include('lib.php');

reservation(
  aget($_GET, 'evse'), 
  aget($_GET, 'user')
);

