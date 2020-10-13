<?php

use ArcaSolutions\MultiDomainBundle\HttpFoundation\MultiDomainRequest;

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../app/bootstrap.php.cache';

if ($_SERVER['REQUEST_URI'] === '/favicon.ico') {
    exit;
}

require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);

$request = MultiDomainRequest::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
