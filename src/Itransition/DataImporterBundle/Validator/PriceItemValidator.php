<?php
namespace Itransition\DataImporterBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Class PriceItemValidator
 * @package Itransition\DataImporterBundle\Validator
 */
class PriceItemValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        $conditions = (array) $constraint->conditions;

        if (0 == count($conditions)) {
            throw new ConstraintDefinitionException('Rules can\'t be empty.');
        }

        foreach ($conditions as $condition) {
            if (count($condition) == 2) {
                if ($entity->getPrice() < $condition[0] && $entity->getStock() < $condition[1]) {
                    $this->context
                        ->buildViolation(
                            sprintf(
                                'Items in stock are less then %d and price is less then $%d.',
                                $condition[1],
                                $condition[0]
                            )
                        )
                        ->addViolation();
                }
            }

            if (count($condition) == 1) {
                if ($entity->getPrice() > $condition[0]) {
                    $this->context
                        ->buildViolation(sprintf('Product price is greater then $%d.', $condition[0]))
                        ->addViolation();
                }
            }
        }
    }
}
