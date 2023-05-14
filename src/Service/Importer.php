<?php

namespace App\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Importer
{
    private Finder $finder;

    private ParameterBagInterface $parameterBag;

    private string $filePath;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->finder = new Finder();
        $this->parameterBag = $parameterBag;
        $this->filePath = $this->parameterBag->get('csv_importer_directory');
        $this->finder->files()->in($this->filePath);
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
        if ($this->isFileExist($fileName)) {
            $file = $this->finder->name($fileName);

            foreach ($this->finder as $file) { $csv = $file; }
                if (($handle = fopen($csv->getRealPath(), "r")) !== FALSE) {
                    $i = 0;

                    while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
                        $i++;
                        if ($i == 1) { continue; }
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
        }


        return $rows;
    }
}
