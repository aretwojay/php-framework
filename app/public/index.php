<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\TestController;

$test = new TestController();
echo $test->hello();
