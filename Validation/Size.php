<?php

namespace Awurth\Upload\Validation;

use Awurth\Upload\File;

/**
 * Size
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 * @package Awurth\Upload\Validation
 */
class Size implements Constraint
{
    /**
     * File min size
     *
     * @var int
     */
    protected $min;

    /**
     * File max size
     *
     * @var int
     */
    protected $max;

    /**
     * Message if file size is too small
     *
     * @var string
     */
    protected $minMessage = 'The file size is too small';

    /**
     * Message if file size is too large
     *
     * @var string
     */
    protected $maxMessage = 'The file size is too large';

    /**
     * Constructor
     *
     * @param int $max File max size
     * @param int $min File min size
     * @param string $maxMessage Message if file size is too small
     * @param string $minMessage Message if file size is too large
     */
    public function __construct($max, $min = 0, $maxMessage = null, $minMessage = null)
    {
        if (is_string($min)) {
            $min = File::humanReadableToBytes($min);
        }
        $this->min = $min;

        if (is_string($max)) {
            $max = File::humanReadableToBytes($max);
        }
        $this->max = $max;

        if ($minMessage) {
            $this->minMessage = $minMessage;
        }

        if ($maxMessage) {
            $this->maxMessage = $maxMessage;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(File $file)
    {
        if ($file->getSize() > $this->max) {
            $file->addErrors($this->maxMessage);
        } elseif ($file->getSize() < $this->min) {
            $file->addErrors($this->minMessage);
        }
    }
}