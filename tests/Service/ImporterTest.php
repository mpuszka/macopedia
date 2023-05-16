<?php

namespace App\Tests\Service;

use App\Service\Importer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImporterTest extends KernelTestCase
{
    public function testFileNotExist(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $importer = $container->get(Importer::class);
        $importCsv = $importer->importCsv('somefile.txt');

        $this->assertEquals($importCsv, ['status' => 'error', 'message' => 'File not exist']);
    }

    public function testMimeType(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $importer = $container->get(Importer::class);
        $parameterBag = $container->get(ParameterBagInterface::class);
        fopen($parameterBag->get('csv_importer_directory') . '/file.txt', 'w');
        $importCsv = $importer->importCsv('file.txt');

        $this->assertEquals($importCsv, ['status' => 'error', 'message' => 'File is not a CSV file']);
    }

    public function testImportProcess(): void
    {
        $container = static::getContainer();
        $importer = $container->get(Importer::class);
        $parameterBag = $container->get(ParameterBagInterface::class);

        $content = [
            ['product name', 'product number'],
            ['product1', 1],
            ['product2', 2],
            ['product3', 3],
            ['product4', 4],
        ];

        $fp = fopen($parameterBag->get('csv_importer_directory') . '/file.csv', 'w');
        foreach ($content as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $importCsv = $importer->importCsv('file.csv');
        $this->assertEquals($importCsv, ['status' => 'success', 'message' => 'The products have been imported.']);
    }
}