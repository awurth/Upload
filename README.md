# Awurth/upload

Easy file upload in PHP

## Installation
```bash
$ composer require awurth/upload
```

## Basic usage

```php
<?php

use Awurth\Upload\File;
use Awurth\Upload\Validation\Size;
use Awurth\Upload\Validation\MimeType;

$file = new File('file', 'upload_dir');
$file->setNewName('new_name');

$file->addConstraints([
    new Size('2M'),
    new MimeType(['image/png', 'image/jpeg'])
]);

if ($file->validate()->isValid()) {
    $file->upload();
} else {
    $errors = $file->getErrors();
}
```
