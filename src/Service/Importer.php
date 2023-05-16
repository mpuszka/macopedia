<?php

namespace App\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Psr\Log\LoggerInterface as Logger;

class Importer
{
    private Finder $finder;

    private ParameterBagInterface $parameterBag;

    private string $filePath;

    private EntityManager $entityManager;

    private Logger $logger;

    public function __construct(ParameterBagInterface $parameterBag, EntityManager $entityManager, Logger $logger)
    {
        $this->finder = new Finder();
        $this->parameterBag = $parameterBag;
        $this->filePath = $this->parameterBag->get('csv_importer_directory');
        $this->finder->files()->in($this->filePath)->name('*.csv');
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    private function isFileExist(string $fileName): bool
    {
        return file_exists($this->filePath . "/{$fileName}");
    }

    public function areFilesToImport(): bool
    {
        return $this->finder->hasResults();
    }

    public function getFilesNameToImport(): array
    {
        $rows = [];
        foreach ($this->finder as $file) {
            $rows[] = [$file->getFilename()];
        }

        return $rows;
    }

    public function importCsv(string $fileName): array
    {
        $rows = [];
        if (! $this->isFileExist($fileName)) {
            return [
                'status' => 'error',
                'message' => 'File not exist',
            ];
        }

        $file = $this->finder->name($fileName);

        foreach ($this->finder as $file) { $csv = $file; }

        if (false !== ($handle = fopen($csv->getRealPath(), "r"))) {
            $i = 0;

            while (false !== ($data = fgetcsv($handle, null, ";"))) {
                $i++;
                if (1 === $i) { continue; }

                $repository = $this->entityManager->getRepository(Product::class);

                if ($repository->isProductExists($data[1])) {
                    $this->logger->info("Importer: The product with the name: {$data[0]} and product number: {$data[1]} already exists in the database!");
                    continue;
                }

                $product = new Product();
                $product->setName($data[0]);
                $product->setProductNumber(substr($data[1], 0, 7));

                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $rows[] = $data;
            }
            fclose($handle);
            unlink($csv->getRealPath());
        }

        return [
            'status' => 'success',
            'message' => 'The products have been imported',
        ];
    }
}
