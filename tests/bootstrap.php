<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
\Tracy\Debugger::enable();
date_default_timezone_set('Europe/Prague');
