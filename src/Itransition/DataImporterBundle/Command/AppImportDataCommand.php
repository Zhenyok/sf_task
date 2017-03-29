<?php

namespace Itransition\DataImporterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppImportDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:import-data')
             ->setDescription('Import data into database')
             ->addArgument('filePath', InputArgument::REQUIRED, 'Path to file')
             ->addOption('test', null, InputOption::VALUE_NONE, 'Test environment option');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filePath');

        if (!is_readable($filePath)) {
            $output->writeln('<error>File doesn\'t exist or is not readable</error>');
            exit;
        }

        $fileObject = new \SplFileObject($filePath);

        $this->getContainer()
            ->get('products.import')
            ->setOutput($output)
            ->startImport($fileObject, $input->getOption('test'));
    }

}
