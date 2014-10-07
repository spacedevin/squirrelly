<?php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);


require_once __DIR__ . '/beersquirrel.php';

$upload = $bs->model('Upload');

$q = 'select * from upload where date < date_sub(now(), interval 7 day)';

foreach ($upload->q($q) as $up) {
	$up->del();
}
