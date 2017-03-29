<?php

namespace Itransition\DataImporterBundle\Writer;


use Doctrine\Common\Persistence\ObjectManager;
use Port\Doctrine\DoctrineWriter;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomDoctrineWriter extends DoctrineWriter {
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
    protected $exclusionItems = [];

    /**
     * @var int
     */
    private $exclusion = 0;

    private $lastResult;

    private $test;

    protected $counter = 0;

    private $batchSize = 20;

    public function __construct(ObjectManager $objectManager, $objectName, ValidatorInterface $validator, $index = null) {
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
        $this->test = (bool)$test;
    }

    public function exclusionItems()
    {
        return $this->exclusionItems;
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
     * Contains unique key in current batch.
     *
     * @param $entity
     * @return bool
     */
    private function contains($entity)
    {
        foreach ($this->uniqueFields as $field) {
            $value = $this->objectMetadata->getFieldValue($entity, $field);
            $key = $field . '_' . $value;
            if (isset($this->uniqueFieldsValues[$key])) {
                return true;
            } else {
                $this->uniqueFieldsValues[$key] = true;
            }
        }

        return false;
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
            $this->exclusionItems[] = $item;
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
}