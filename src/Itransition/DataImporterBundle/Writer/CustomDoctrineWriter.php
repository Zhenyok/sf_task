<?php
namespace Itransition\DataImporterBundle\Writer;

use Doctrine\Common\Persistence\ObjectManager;
use Port\Doctrine\DoctrineWriter;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomDoctrineWriter
 * @package Itransition\DataImporterBundle\Writer
 */
class CustomDoctrineWriter extends DoctrineWriter
{
    /**
     * Validator
     *
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Unique Fields map
     *
     * @var array
     */
    protected $uniqueFields = [];

    /**
     * Unique Fields Values
     *
     * @var array
     */
    protected $uniqueFieldsValues = [];

    /**
     * @var array
     */
    protected $excludedItems = [];

    /**
     * @var int
     */
    private $exclusion = 0;

    /**
     * @var Array
     */
    private $lastResult;

    /**
     * @var Boolean
     */
    private $test;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var int
     */
    private $batchSize = 20;

    public function __construct(
        ObjectManager $objectManager,
        $objectName,
        ValidatorInterface $validator,
        $index = null
    ) {
        parent::__construct($objectManager, $objectName, $index);
        $this->validator = $validator;

        foreach ($this->objectMetadata->getFieldNames() as $fieldName) {
            if ($this->objectMetadata->isUniqueField($fieldName)) {
                $this->uniqueFields[] = $fieldName;
            }
        }
    }

    public function setMode($test)
    {
        $this->test = (bool) $test;
    }

    public function getExcludedItems()
    {
        return $this->excludedItems;
    }

    public function getLastResult()
    {
        return $this->lastResult;
    }

    public function setLastResult($result)
    {
        $this->lastResult = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function writeItem(array $item)
    {
        $entity = $this->findOrCreateItem($item);
        $this->loadAssociationObjectsToObject($item, $entity);

        $this->updateObject($item, $entity);
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0 || $this->contains($entity)) {
            $this->excludedItems[] = $this->prepareExcludedItem($item);
            $this->exclusion++;
            $this->setLastResult(false);

            return $this;
        }

        $this->objectManager->persist($entity);
        $this->counter++;

        if ((($this->counter - $this->exclusion) % $this->batchSize) == 0) {
            if ($this->test) {
                $this->clear();
                $this->objectManager->clear($this->objectName);
            } else {
                $this->flushBuffer();
            }
        }
        $this->setLastResult($entity);

        return $this;
    }


    protected function clear()
    {
        $this->uniqueFieldsValues = [];
        $this->exclusion = 0;
    }

    protected function flushBuffer()
    {
        $this->clear();
        $this->objectManager->flush();
    }

    /**
     * Removes date info from rows that can't be imported
     *
     * @param Array  $item    Reference to item that can't be imported
     *
     * @return Array Item with removed dates
     *
     */
    private function prepareExcludedItem(&$item)
    {
        if (isset($item['timeStamp'])) {
            unset($item['timeStamp']);
        }
        if (isset($item['dateAdded'])) {
            unset($item['dateAdded']);
        }

        if (isset($item['discontinued']) && !empty($item['discontinued'])) {
            $item['discontinued'] = $item['discontinued']->format('Y-m-d H:i:s');
        }

        return $item;
    }

    /**
     * Contains unique key in current batch.
     *
     * @param $entity
     * @return bool
     */
    private function contains($entity)
    {
        foreach ($this->uniqueFields as $field) {
            $value = $this->objectMetadata->getFieldValue($entity, $field);
            $key = $field.'_'.$value;
            if (isset($this->uniqueFieldsValues[$key])) {
                return true;
            } else {
                $this->uniqueFieldsValues[$key] = true;
            }
        }

        return false;
    }
}
