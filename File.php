<?php

namespace Awurth\Upload;

use InvalidArgumentException;
use Exception;
use finfo;

/**
 * File
 *
 * @author  Alexis Wurth <alexis.wurth57@gmail.com>
 * @package Awurth\Upload
 */
class File
{
    /**
     * Error code messages
     *
     * @var array
     */
    protected static $errorCodeMessages = [
        UPLOAD_ERR_INI_SIZE => 'The file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];

    /**
     * Convert file units to bytes
     *
     * @var array
     */
    protected static $units = [
        'b' => 1,
        'k' => 1024,
        'm' => 1048576,
        'g' => 1073741824
    ];

    /**
     * The key in $_FILES
     *
     * @var string
     */
    protected $key;

    /**
     * Original name of the uploaded file
     *
     * @var string
     */
    protected $originalName;

    /**
     * Original file name without extension
     *
     * @var string
     */
    protected $name;

    /**
     * File extension
     *
     * @var string
     */
    protected $extension;

    /**
     * File MIME Type
     *
     * @var string
     */
    protected $mimeType;

    /**
     * File size
     *
     * @var int
     */
    protected $size;

    /**
     * File temporary name
     *
     * @var string
     */
    protected $tmpName;

    /**
     * File upload error code
     *
     * @var int
     */
    protected $errorCode;

    /**
     * Validation constraints
     *
     * @var array
     */
    protected $constraints;

    /**
     * Validation errors
     *
     * @var array
     */
    protected $errors;

    /**
     * Has this file already been validated?
     *
     * @var bool
     */
    protected $validated;

    /**
     * The directory where the file will be stored
     *
     * @var string
     */
    protected $uploadDir;

    /**
     * Should we overwrite existing files?
     *
     * @var bool
     */
    protected $overwrite;

    /**
     * Is the file required?
     *
     * @var bool
     */
    protected $required;

    /**
     * The new file name
     *
     * @var string
     */
    protected $newName;

    /**
     * Create new File
     *
     * @param string $key The key in $_FILES
     * @param string $uploadDir The directory where the file will be stored
     * @param bool $overwrite Should we overwrite existing files?
     * @param bool $required If the file is required
     */
    public function __construct($key, $uploadDir = null, $overwrite = true, $required = false)
    {
        if (!isset($_FILES[$key])) {
            throw new InvalidArgumentException('No file was found with key: ' . $key);
        }

        if ($uploadDir) {
            $this->setUploadDir($uploadDir);
        }

        $this->key = $key;
        $this->originalName = $_FILES[$key]['name'];
        $this->errorCode = $_FILES[$key]['error'];
        $this->size = $_FILES[$key]['size'];
        $this->tmpName = $_FILES[$key]['tmp_name'];
        $this->uploadDir = $uploadDir;
        $this->overwrite = $overwrite;
        $this->required = $required;
        $this->validated = false;
    }

    public function upload()
    {
        if (!$this->required && $this->errorCode === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        if (!$this->uploadDir) {
            throw new Exception('Upload directory not specified');
        }

        if (!$this->validated) {
            $this->validate();
        }

        if (!$this->isValid()) {
            return false;
        }

        if ($this->newName) {
            $filename = $this->uploadDir . DIRECTORY_SEPARATOR . $this->newName . '.' . $this->getExtension();
        } else {
            $filename = $this->uploadDir . DIRECTORY_SEPARATOR . $this->getNameWithExtension();
        }

        if (!$this->overwrite && file_exists($filename)) {
            $this->addErrors('File already exists');
            return false;
        }

        return move_uploaded_file($this->tmpName, $filename);
    }

    /****************************************************/
    /* ------------------ VALIDATION ------------------ */
    /****************************************************/

    /**
     * Validate the file
     *
     * @return $this
     */
    public function validate()
    {
        $this->validated = true;

        if (!$this->required && $this->errorCode === UPLOAD_ERR_NO_FILE) {
            return $this;
        }

        if (!$this->isOk()) {
            $this->addErrors(self::$errorCodeMessages[$this->errorCode]);
        }

        foreach ($this->constraints as $constraint) {
            $constraint->validate($this);
        }

        return $this;
    }

    /**
     * Return true if the file is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->validated && empty($this->errors);
    }

    /**
     * Check if file was uploaded successfully
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->errorCode === UPLOAD_ERR_OK;
    }

    /**
     * Add validation constraints
     *
     * @param $constraints
     * @return $this
     */
    public function addConstraints($constraints)
    {
        if (is_array($constraints)) {
            foreach ($constraints as $constraint) {
                $this->constraints[] = $constraint;
            }
        } else {
            $this->constraints[] = $constraints;
        }

        $this->validated = false;
        return $this;
    }

    /**
     * Set validation constraints
     *
     * @param array $constraints
     * @return $this
     */
    public function setConstraints(array $constraints)
    {
        $this->constraints = $constraints;
        $this->validated = false;
        return $this;
    }

    /**
     * Get validation constraints
     *
     * @return array
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * Add validation errors
     *
     * @param string $errors
     * @return $this
     */
    public function addErrors($errors)
    {
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $this->errors[] = $error;
            }
        } else {
            $this->errors[] = $errors;
        }

        return $this;
    }

    /**
     * Set validation errors
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /****************************************************/
    /* --------------- GETTERS / SETTERS -------------- */
    /****************************************************/

    /**
     * Get key in $_FILES
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get original file name
     *
     * @return string
     */
    public function getNameWithExtension()
    {
        return $this->originalName;
    }

    /**
     * Get original file name without extension
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            $this->name = pathinfo($this->originalName, PATHINFO_FILENAME);
        }

        return $this->name;
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getExtension()
    {
        if (!$this->extension) {
            $this->extension = strtolower(pathinfo($this->originalName, PATHINFO_EXTENSION));
        }

        return $this->extension;
    }

    /**
     * Get file MIME type
     *
     * @return string
     */
    public function getMimeType()
    {
        if (!$this->mimeType) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $this->mimeType = $finfo->file($this->tmpName);
        }

        return $this->mimeType;
    }

    /**
     * Get file size
     *
     * @param bool $humanReadable
     * @return int
     */
    public function getSize($humanReadable = false)
    {
        if ($humanReadable) {
            return $this->bytesToHumanReadable($this->size);
        }

        return $this->size;
    }

    /**
     * Get file temporary name
     *
     * @return string
     */
    public function getTmpName()
    {
        return $this->tmpName;
    }

    /**
     * Set new file name
     *
     * @param string $name The new file name
     * @return $this
     */
    public function setNewName($name)
    {
        $this->newName = $name;
        return $this;
    }

    /**
     * Get new file name
     *
     * @return string
     */
    public function getNewName()
    {
        return $this->newName;
    }

    /**
     * Set upload directory
     *
     * @param string $dir
     * @return $this
     */
    public function setUploadDir($dir)
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException('Directory does not exist');
        }

        if (!is_writable($dir)) {
            throw new InvalidArgumentException('Directory is not writable');
        }

        $this->uploadDir = $dir;
        return $this;
    }

    /**
     * Get upload directory
     *
     * @return string
     */
    public function getUploadDir()
    {
        return $this->uploadDir;
    }

    /**
     * Set overwrite
     *
     * @param bool $overwrite Should we overwrite existing files?
     * @return $this
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    /**
     * Return true if this file can overwrite existing files
     *
     * @return bool
     */
    public function getOverwrite()
    {
        return $this->overwrite;
    }

    /**
     * Set if the file is required
     *
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * Return true if the file is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Convert human readable file size (e.g. "10K" or "3M") into bytes
     *
     * @param  string $input
     * @throws InvalidArgumentException
     * @return int
     */
    public static function humanReadableToBytes($input)
    {
        $number = (int) $input;
        $unit = strtolower(substr($input, -1));
        if (!isset(self::$units[$unit])) {
            throw new InvalidArgumentException('Unknown file size unit');
        }

        return $number = $number * self::$units[$unit];
    }

    /**
     * Convert bytes into human readable file size (e.g. "10K" or "3M")
     *
     * @param int $bytes
     * @return string
     */
    public static function bytesToHumanReadable($bytes)
    {
        if ($bytes >= self::$units['g']) {
            return (int) ($bytes / self::$units['g']) . 'G';
        } elseif ($bytes >= self::$units['m']) {
            return (int) ($bytes / self::$units['m']) . 'M';
        } elseif ($bytes >= self::$units['k']) {
            return (int) ($bytes / self::$units['k']) . 'K';
        }

        return $bytes . 'B';
    }
}