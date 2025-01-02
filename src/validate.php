#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace WPHooks\Generator;

require_once file_exists('vendor/autoload.php') ? 'vendor/autoload.php' : dirname(__DIR__, 4) . '/vendor/autoload.php';

use Opis\JsonSchema\{
    Errors\ErrorFormatter,
    Validator,
};

$data = json_decode(file_get_contents($argv[1]));
$validator = new Validator();
$id = 'https://github.com/wp-hooks/generator/blob/1.0.0/schema.json';
$validator->resolver()->registerFile(
    $id,
    'schema.json',
);

$result = $validator->validate($data, $id);

if ($result->isValid()) {
    echo 'Data is valid', PHP_EOL;
} else {
    print_r((new ErrorFormatter())->format($result->error()));
    exit(1);
}
