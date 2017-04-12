<?php
namespace Itransition\DataImporterBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ProductItem extends Constraint
{
    public $message = 'Stock value is not importable.';
}
