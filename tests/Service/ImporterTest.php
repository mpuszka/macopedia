<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Service\Importer;
use Doctrine\ORM\EntityManagerInterface;
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

    public function testImportProcess(): void
    {
        $container = static::getContainer();
        $importer = $container->get(Importer::class);
        $parameterBag = $container->get(ParameterBagInterface::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $countOfProduct = $entityManager->getRepository(Product::class)->getCountOfAll();

        $content = [
            ['product name', 'product number'],
            ['product' . rand(), rand()],
            ['product' . rand(), rand()],
        ];

        $fp = fopen($parameterBag->get('csv_importer_directory') . '/file.csv', 'w');
        foreach ($content as $fields) {
            fputcsv($fp, $fields, ';');
        }
        fclose($fp);

        $importCsv = $importer->importCsv('file.csv');
        $this->assertEquals($importCsv, ['status' => 'success', 'message' => 'The products have been imported']);
        $this->assertEquals($entityManager->getRepository(Product::class)->getCountOfAll(), $countOfProduct + 2);
        $this->assertEquals(file_exists($parameterBag->get('csv_importer_directory') . '/file.csv'), false);

    }
}