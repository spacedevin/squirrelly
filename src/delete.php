<?php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors',true);

require_once __DIR__ . '/beersquirrel.php';

$upload = $bs->service('Upload');

$q = 'select * from upload where date < date_sub(now(), interval '.$bs->config()['data']['life'].')';

foreach ($upload->q($q) as $up) {
	$up->del();
}
