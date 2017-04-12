<?php
namespace Itransition\DataImporterBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class PriceItem extends Constraint
{

    /**
     * @var String
     */
    public $message = 'Price is less';

    /**
     * @var Array
     */
    public $conditions;

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getDefaultOption()
    {
        return 'conditions';
    }
}
