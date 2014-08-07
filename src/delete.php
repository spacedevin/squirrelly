<?php

require_once __DIR__ . '/beersquirrel.php';

$bs->db()->exec('delete from upload where date < date_sub(now(), interval 7 day)');
