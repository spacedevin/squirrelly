<?php

require_once __DIR__ . '/beersquirrel.php';

$upload = $bs->model('Upload');

$q = 'select * from upload where date < date_sub(now(), interval 7 day)';

foreach ($upload->q($q) as $up) {
	$up->del();
}
