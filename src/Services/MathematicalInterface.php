<?php

namespace Drupal\mathematical_formatter\Services;


/**
 * Interface MathematicalInterface
 * @package Drupal\mathematical_formatter\Services
 */
interface MathematicalInterface
{
    /**
     * @param array $input
     * @return mixed
     */
    public function parse(Array $input);


}