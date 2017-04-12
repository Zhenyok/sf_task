<?php
namespace Itransition\DataImporterBundle\Importer;

use Itransition\DataImporterBundle\Steps\DateTimeFromStringStep;
use Itransition\DataImporterBundle\Writer\CustomDoctrineWriter;
use Port\Steps\Step\MappingStep;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Port\Steps\StepAggregator as Workflow;
use Doctrine\ORM\EntityManager;
use \SplFileObject;
use Port\Csv\CsvReader;

/**
 * Class CsvDataImport
 * @package Itransition\DataImporterBundle\Importer
 */
class CsvDataImport
{
    /**
     * @var OutputInterface
     */
    protected $outputInterface;

    /**
     * @var Array
     */
    protected $dbMapping = [];

    /**
     * @var ObjectManager
     */
    protected $ormManager;

    /**
     * @var FileLocator
     */
    protected $fileLocator;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Array
     */
    protected $excludedItems;

    public function __construct(
        EntityManager $entityManager,
        FileLocator $fileLocator,
        ValidatorInterface $validator,
        array $columnMapper
    ) {
        $this->ormManager = $entityManager;
        $this->fileLocator = $fileLocator;
        $this->dbMapping = $columnMapper;
        $this->validator = $validator;
    }

    /**
     * Setter for OutputInterface
     *
     * @param OutputInterface $output    Output interface
     *
     * @return CsvDataImport $this       Returns Reference to current object instance
     *
     */
    public function setOutput(OutputInterface $output)
    {
        $this->outputInterface = $output;

        return $this;
    }

    /**
     * Starts csv import
     *
     * @param SplFileObject $fileObject    CSV file to import
     * @param Boolean       $envType       --Test option if import into database is not needed
     *
     * @return CsvDataImport $this       Returns Reference to current object instance
     *
     */
    public function startImport(SplFileObject $fileObject, $envType = false)
    {
        $reader = new CsvReader($fileObject, ',');
        $reader->setHeaderRowNumber(0);
        $workflow = new Workflow($reader);

        $customWriter = new CustomDoctrineWriter($this->ormManager, 'ItransitionDataImporterBundle:Product', $this->validator);

        $customWriter->setMode($envType);
        $mapConverter = $this->mapConvert();
        $mappings = new MappingStep($mapConverter);
        $customConverter = new DateTimeFromStringStep();

        $workflow->setSkipItemOnFailure(true)
                             ->addStep($customConverter)
                             ->addStep($mappings)
                             ->addWriter($customWriter)
                             ->process();

        $this->displayResult($customWriter, $reader);

        return $this;
    }

    /**
     * Renders items that were imported with errors
     *
     * @param CustomDoctrineWriter    $writer       Custom writer into database
     * @param CsvReader               $reader       Object that contains List of imported csv rows
     *
     */
    public function displayResult(CustomDoctrineWriter $writer, $reader)
    {
        $this->excludedItems = $writer->getExcludedItems();
        $header = array_keys($this->dbMapping);
        $this->addReaderErrors($reader, $this->excludedItems);

        if (!empty($this->excludedItems) && $this->outputInterface) {
            $this->outputInterface->writeln('Items that were not imported:');
            $table = new Table($this->outputInterface);
            $table->setHeaders($header)
                ->setRows($this->excludedItems)
                ->render();
        }
    }

    /**
     * Getter for list of error rows
     *
     * @return Array|null  Returns List of errors or null
     *
     */
    public function getExcludedItems()
    {
        if ($this->excludedItems) {
            return $this->excludedItems;
        }

        return null;
    }

    /**
     * Converts mapped fields into array suitable for PHPPort bundle validation
     *
     * @return Array $result   List of mapped fields suitable for PortPHP bundle
     *
     */
    private function mapConvert()
    {
        $result = array();

        $mapProcessingCallback = function ($item) {
            return '['.$item.']';
        };
        if (!empty($this->dbMapping)) {
            $result =  array_combine(
                array_map($mapProcessingCallback, array_values($this->dbMapping)),
                array_map($mapProcessingCallback, array_keys($this->dbMapping))
            );
        }

        return $result;
    }

    /**
     * Includes reader errors to validator errors Array
     *
     * @param CsvReader              $reader            Object that contains List of imported csv rows
     * @param Array                  $excludedItems    Reference to List of rows that were not imported
     *
     */
    private function addReaderErrors($reader, array &$excludedItems)
    {
        $csvHeaders = $reader->getColumnHeaders();
        $readerErrors = $reader->getErrors();
        $header = array_keys($this->dbMapping);
        $sortedKeys = array_fill_keys(array_values($header), '');

        if (!empty($readerErrors)) {
            foreach ($readerErrors as $error) {
                $tmpItem = $this->rowsCombine($csvHeaders, $error);

                if (!empty($tmpItem)) {
                    $excludedItems[] = array_replace($sortedKeys, $tmpItem);
                }

                unset($tmpItem);
            }
        }
    }

    /**
     * Combines reader errors and validation errors into one array
     *
     * @param   Array              $csvHeaders    Array of Table headers
     * @param   Array              $errors        Array of occurred errors
     *
     * @return  $errorResult                      List of combined errors
     */
    private function rowsCombine(array $csvHeaders, array $errors)
    {
        $errorResult = array();

        foreach ($csvHeaders as $key => $header) {
            foreach ($errors as $errorHeader => $value) {
                $needle = array_search($header, $this->dbMapping);
                if ($key == $errorHeader && $needle) {
                    $errorResult[$needle] = $value;
                }
            }
        }

        return $errorResult;
    }
}



