<?php

namespace Itransition\DataImporterBundle\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ProductItem extends Constraint {
    public $message = 'Data import failed. Product item is not valid.';
}