<?php

namespace Awurth\Upload\Validation;

use Awurth\Upload\File;

/**
 * Constraint
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 * @package Awurth\Upload\Validation
 */
interface Constraint
{
    /**
     * Validate file
     *
     * @param File $file
     */
    public function validate(File $file);
}