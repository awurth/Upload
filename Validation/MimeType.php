<?php

namespace Awurth\Upload\Validation;

use Awurth\Upload\File;

/**
 * MimeType
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 * @package Awurth\Upload\Validation
 */
class MimeType implements Constraint
{
    /**
     * Authorized extensions for the file
     *
     * @var array
     */
    protected $authorizedMimeTypes;

    /**
     * Message if file MIME type is not allowed
     *
     * @var string
     */
    protected $message = 'Invalid MIME type';

    /**
     * Constructor
     *
     * @param array $authorizedMimeTypes Authorized extensions for the file
     * @param string $message Message if file MIME type is not allowed
     */
    public function __construct(array $authorizedMimeTypes, $message = null)
    {
        $this->authorizedMimeTypes = $authorizedMimeTypes;

        if ($message) {
            $this->message = $message;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(File $file)
    {
        if (!in_array($file->getMimeType(), $this->authorizedMimeTypes)) {
            $file->addErrors($this->message);
        }
    }
}