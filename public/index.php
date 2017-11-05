<?php

date_default_timezone_set('Australia/Melbourne');

require '../vendor/autoload.php';
require '../src/config/Database.php';
require '../src/models/MyResponse.php';

$app = new \Slim\App;

// Routes
require '../src/routes/auth.php';
require '../src/routes/members.php';

$app->run();