<?php
/**
 * Created by IntelliJ IDEA.
 * User: Eugene
 * Date: 25/03/17
 * Time: 22:41
 */

namespace Itransition\DataImporterBundle\Importer;

use Itransition\DataImporterBundle\Steps\DateTimeFromStringStep;
use Itransition\DataImporterBundle\Writer\CustomDoctrineWriter;
use Port\Steps\Step\MappingStep;
use Port\SymfonyConsole\ProgressWriter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Port\Steps\StepAggregator as Workflow;
use Doctrine\ORM\EntityManager;
use \SplFileObject;
use Port\Csv\CsvReader;
use Symfony\Component\Yaml\Yaml;

class CsvDataImport {
    protected $outputInterface;
    protected $dbMapping = [];
    protected $ormManager;
    protected $fileLocator;
    protected $validator;

    public function __construct(EntityManager $entityManager, FileLocator $fileLocator, ValidatorInterface $validator) {
        $this->ormManager = $entityManager;
        $this->fileLocator = $fileLocator;
        $this->dbMapping = $this->getColumnMapper();
        $this->validator = $validator;
    }

    public function getColumnMapper() {
        try {
            $mapping = Yaml::parse($this->fileLocator->locate('@ItransitionDataImporterBundle/Resources/config/db_mapping.yml'));
            return $mapping['columns'];
        } catch(ParseException $e) {
            //TODO realize error handling message

        }

        return array();
    }

    public function setOutput(OutputInterface $output) {
        $this->outputInterface = $output;
        return $this;
    }

    public function startImport(SplFileObject $fileObject, $envType = false) {
        $reader = new CsvReader($fileObject, ',');
        $reader->setHeaderRowNumber(0);

        $output = new ConsoleOutput();
        $workflow = new Workflow($reader);
        $progressWriter = new ProgressWriter($output, $reader);

        $customWriter = new CustomDoctrineWriter($this->ormManager, 'ItransitionDataImporterBundle:Tblproductdata', $this->validator);
        $customWriter->setMode($envType);
        $mapConverter = $this->mapConvert();
        $mappings = new MappingStep($mapConverter);
        $customConverter = new DateTimeFromStringStep();

        $result = $workflow->setSkipItemOnFailure(true)
                             ->addStep($customConverter)
                             ->addStep($mappings)
                             ->addWriter($customWriter)
                             ->addWriter($progressWriter)
                             ->process();
    }

    private function mapConvert() {
        $result = array();

        if (!empty($this->dbMapping)) {

            $tmpResult = array_flip($this->dbMapping);

            foreach ($tmpResult as $key => $val) {
                $result['['.$key.']'] = '['.$val.']';
            }
        }

        return $result;
    }
}