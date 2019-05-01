<?php
include('lib.php');

reservation(
  aget($_GET, 'loc'), 
  aget($_GET, 'user')
);

