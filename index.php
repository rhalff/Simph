<?php
ini_set('display_errors', true);

require_once 'config.php';
require_once 'lib/sp.class.php';

$sp = new Simph($CONFIG);
$sp->run();
