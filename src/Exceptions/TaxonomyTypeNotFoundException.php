<?php

namespace JobMetric\Taxonomy\Exceptions;

use InvalidArgumentException;

class TaxonomyTypeNotFoundException extends InvalidArgumentException
{
    public function __construct(string $type)
    {
        parent::__construct("Taxonomy type [$type] is not registered.");
    }
}
