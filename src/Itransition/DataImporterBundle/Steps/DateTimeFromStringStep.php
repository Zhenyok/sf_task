<?php
namespace Itransition\DataImporterBundle\Steps;

use Port\Steps\Step;

/**
 * Class DateTimeFromStringStep
 * @package Itransition\DataImporterBundle\Steps
 */
class DateTimeFromStringStep implements Step
{

    /**
     * @var Datetime
     */
    protected $inputDateTimeFormat;

    public function __construct()
    {
        $this->inputDateTimeFormat = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function process($item, callable $next)
    {

        if (isset($item['Discontinued']) && $item['Discontinued'] == 'yes') {
            $item['Discontinued'] = $this->inputDateTimeFormat;
        } else {
            unset($item['Discontinued']);
        }

        $item['timeStamp'] = $this->inputDateTimeFormat;
        $item['dateAdded'] = $this->inputDateTimeFormat;

        return $next($item);
    }
}
