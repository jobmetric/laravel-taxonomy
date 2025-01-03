<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class CannotMakeParentSubsetOwnChild extends Exception
{
    public function __construct(int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.cannot_make_parent_subset_own_child'), $code, $previous);
    }
}
