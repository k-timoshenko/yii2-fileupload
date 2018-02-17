# File upload

Yii2 widget for [t-kanstantsin/fileupload](https://github.com/t-kanstantsin/fileupload)

[![Latest Stable Version](https://poser.pugx.org/t-kanstantsin/yii2-fileupload/v/stable.png)](https://packagist.org/packages/t-kanstantsin/yii2-fileupload)
[![Total Downloads](https://poser.pugx.org/t-kanstantsin/yii2-fileupload/downloads.png)](https://packagist.org/packages/t-kanstantsin/yii2-fileupload)
[![Build Status](https://travis-ci.org/t-kanstantsin/yii2-fileupload.svg)](https://travis-ci.org/t-kanstantsin/yii2-fileupload)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/t-kanstantsin/yii2-fileupload/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/t-kanstantsin/yii2-fileupload/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/t-kanstantsin/yii2-fileupload/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/t-kanstantsin/yii2-fileupload/?branch=master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)


## Configuration

Full-featured configuration example:

```php
<?php
use \tkanstantsin\fileupload\FileManager;
use \tkanstantsin\fileupload\formatter\Image;
use \League\Flysystem\Adapter\Local as LocalFSAdapter;
use \League\Flysystem\Filesystem;

new FileManager([
    'uploadFS' => new Filesystem(new LocalFSAdapter(__DIR__ . '/tmp/upload', LOCK_EX, LocalFSAdapter::DISALLOW_LINKS)),
    'cacheFS' => new Filesystem(new LocalFSAdapter(__DIR__ . '/tmp/web', LOCK_EX, LocalFSAdapter::DISALLOW_LINKS)),

    'aliasArray' => [
        'attachment' => [
            'maxCount' => 1,
        ],
    ],
    'formatterConfigArray' => [
        'attachment-gallery' => [
            'class' => Image::class,
            'width' => 1920,
            'height' => 1080,
            'mode' => Image::RESIZE_INSET,
        ],
        'attachment-preview' => [
            'class' => Image::class,
            'width' => 300,
            'height' => 150,
            'mode' => Image::RESIZE_OUTBOUND,
        ],
    ],
]);
```
