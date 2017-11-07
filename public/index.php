<?php

// Global Settings
date_default_timezone_set('Australia/Melbourne');

// Global constants
const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
const SECRET_KEY = 'holmesglensuckssomuch';
const TOKEN_DATA = 'tokenData';

require '../vendor/autoload.php';

// Classes
require '../src/config/Database.php';
require '../src/models/MyResponse.php';
require '../src/middleware/AuthMiddleware.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// Routes
require '../src/routes/auth.php';
require '../src/routes/users.php';
require '../src/routes/members.php';

$app->run();