<?php

namespace Itransition\DataImporterBundle\Steps;

use Port\Steps\Step;

class DateTimeFromStringStep implements Step    {

    protected $inputDateTimeFormat;
    public function __construct() {

        $this->inputDateTimeFormat = new \DateTime();

    }
    public function process($item, callable $next)
    {
        $item['dtmadded'] = $this->inputDateTimeFormat;

        if (isset($item['Discontinued']) && $item['Discontinued'] == 'yes') {
            $item['Discontinued'] = $this->inputDateTimeFormat;
        } else {
            $item['Discontinued'] = null;
        }
        return $next($item);
    }
}