<?php

require '../vendor/autoload.php';
require '../src/config/Database.php';
require '../src/models/MyResponse.php';
require '../src/middleware/AuthMiddleware.php';
require '../src/middleware/CORSMiddleware.php';

// Global Settings
date_default_timezone_set('Australia/Melbourne');

// Global constants
const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
const SECRET_KEY = 'holmesglensuckssomuch';
const TOKEN_DATA = 'tokenData';

// Slim config
$config = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

// Holds the config settings
$container = new \Slim\Container($config);

// App created with additional settings
$app = new \Slim\App($container);

// Adding CORS support to the server
$app->add(new CORSMiddleware());

// Routes
require '../src/routes/cors.php';
require '../src/routes/auth.php';
require '../src/routes/users.php';
require '../src/routes/members.php';
require '../src/routes/memberships.php';

$app->run();