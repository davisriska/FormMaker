<?php

namespace Grafite\FormMaker\Fields;

use Grafite\FormMaker\Fields\Field;

class Range extends Field
{
    protected static function getType()
    {
        return 'range';
    }

    protected static function getFactory()
    {
        return 'numberBetween(1, 10)';
    }
}