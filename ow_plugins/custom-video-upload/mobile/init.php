<?php

$router = OW::getRouter();
$router->addRoute(new OW_Route('cvideoupload.stream', 'custom-video-upload/stream/:name/:extension', 'CVIDEOUPLOAD_CTRL_Stream', 'index'));

require_once  __DIR__ . '/../vendor/autoload.php';

CVIDEOUPLOAD_CLASS_EventHandler::getInstance()->init();
CVIDEOUPLOAD_CLASS_ContentProvider::getInstance()->init();