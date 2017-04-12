<?php


namespace Itransition\DataImporterBundle\Tests;

use Itransition\DataImporterBundle\Command\AppImportDataCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CsvDataImportTest
 * @package Itransition\DataImporterBundle\Tests
 */
class CsvDataImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var ObjectManager
     */
    private $entity;

    protected function setUp()
    {
        parent::setUp();
        $this->kernel = new \AppKernel('dev', true);
        $this->kernel->boot();
        $this->application = new Application($this->kernel);
        $this->entity = $this->kernel->getContainer()->get('doctrine')->getManager();
    }


    public function testImportCsvEnv()
    {
        $command = new AppImportDataCommand();
        $command->setApplication($this->application);

        $import = function (AppImportDataCommand $command) {
            return $command->import;
        };

        $testCommand = new CommandTester($command);
        $testCommand->execute(array(
            'command' => $command->getName(),
            'filePath' => __DIR__.DIRECTORY_SEPARATOR.'stock.csv',
            '--test' => true,
        ));
        $this->assertTrue($testCommand->getStatusCode() == 0);

        $reflect = \Closure::bind($import, null, $command);
        $this->assertTrue(count($reflect($command)->getExcludedItems()) == 6);
    }

    public function tearDown()
    {
        $this->kernel->shutdown();
        $this->entity = null;
        $this->application = null;
        $this->kernel = null;
        parent::tearDown();
    }
}
