<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Helper\Table;
use App\Service\Importer;

#[AsCommand(
    name: 'import:csv',
    description: 'Import CSV files.',
)]
class ImportCsvCommand extends Command
{
    private ParameterBagInterface $parameterBag;

    private Importer $importer;

    public function __construct(ParameterBagInterface $parameterBag, Importer $importer)
    {
         $this->parameterBag = $parameterBag;
         $this->importer = $importer;
         parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'File name which should be imported.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (! $optionName = $input->getOption('name')) {
            if (! $this->importer->areFilesToImport()) {
                $io->warning('There is not new imported file.');

                return Command::SUCCESS;
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Filename'])
                ->setRows($this->importer->getFilesNameToImport())
            ;
            $table->render();

            return Command::SUCCESS;
        }

        var_dump($this->importer->importCsv($optionName));
        die;

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
