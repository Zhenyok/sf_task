<?php
namespace Itransition\DataImporterBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ProductItemValidator
 * @package Itransition\DataImporterBundle\Validator
 */
class ProductItemValidator extends ConstraintValidator
{

    /**
     * {@inheritdoc}
     */
    public function validate($stockVal, Constraint $constraint)
    {
        if (!is_numeric($stockVal)) {
            $this->context->buildViolation($constraint->message)
                 ->addViolation();
        }
    }
}
