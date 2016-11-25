<?php

namespace Awurth\Upload\Validation;

use Awurth\Upload\File;

/**
 * Extension
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 * @package Awurth\Upload\Validation
 */
class Extension implements Constraint
{
    /**
     * Authorized extensions for the file
     *
     * @var array
     */
    protected $authorizedExtensions;

    /**
     * Message if file extension type is not allowed
     *
     * @var string
     */
    protected $message = 'Invalid extension';

    /**
     * Constructor
     *
     * @param array $authorizedExtensions Authorized extensions for the file
     * @param string $message Message if file extension type is not allowed
     */
    public function __construct(array $authorizedExtensions, $message = null)
    {
        $this->authorizedExtensions = $authorizedExtensions;

        if ($message) {
            $this->message = $message;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(File $file)
    {
        if (!in_array($file->getExtension(), $this->authorizedExtensions)) {
            $file->addErrors($this->message);
        }
    }
}