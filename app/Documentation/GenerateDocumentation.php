<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;
use Symfony\Component\Finder\Finder;


$finder = new Finder();

// Configura el Finder para buscar solo archivos PHP en los directorios relevantes
$finder->files()
//    ->name('*.php')
    ->in(__DIR__ . '/../')
    ->exclude('vendor')
    ->exclude('tests');

$openapi = Generator::scan($finder);
file_put_contents(__DIR__ . '/../Public/swagger.json', $openapi->toJson());

echo "Documentación generada en public/swagger.json\n";