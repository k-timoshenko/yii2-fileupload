<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload\action;

use League\Flysystem\Filesystem;
use tkanstantsin\fileupload\config\Alias;
use tkanstantsin\fileupload\formatter\Factory as FormatterFactory;
use tkanstantsin\fileupload\model\Type;
use tkanstantsin\yii2fileupload\model\File;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;

/**
 * Class UploadAction allows receive files with access control check.
 * @package common\components\file\actions
 *
 * @example acton definition:
 * public function actions()
 * {
 *      return [
 *          'upload' => [
 *              'class' => file\actions\FileUploadAction::className(),
 *              'fs' => $this->contentFS,
 *          ],
 *      ];
 * }
 *
 * @example url
 *     /file/get?id=10&updatedAt=00000000&fileType=image&fileName=filename.com
 *
 * @todo: create class/interface/behavior to detect `right` models.
 */
class GetAction extends AbstractAction
{
    /**
     * Filesystem component for assets
     * @var Filesystem
     */
    public $webFS;

    /**
     * @var Alias
     */
    protected $aliasConfig;

    /**
     * @inheritdoc
     * @param string $fileType
     * @param string $hash
     * @param int $id
     * @param string $fileName
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \yii\base\ExitException
     * @throws \yii\web\NotFoundHttpException
     * @throws \ErrorException
     */
    public function run($fileType, $hash, $id, $fileName): void
    {
        $this->init();

        $file = $this->findModel($id);
        $this->aliasConfig = $this->fileManager->getAliasConfig($file->getModelAlias());
        $formatter = $this->fileManager->buildFormatter($file, FormatterFactory::FILE_ORIGINAL);

        if (!$this->contentFS->has($this->aliasConfig->getFilePath($file))) {
            header('HTTP/1.0 404 Not Found');
            \Yii::$app->end();
        }

        [$clearFileType, $fileOptions] = $this->processFileType($fileType);

        $this->setHeaders($file, $clearFileType);

        switch ($clearFileType) {
            case 'image': // TODO: constant!
                $this->displayImage($file, $fileOptions, $this->aliasConfig->getAssetPath($file, $formatter));
                break;
            case 'file':
                $this->displayFile($file, $fileOptions);
                break;
        }
        \Yii::$app->end();
    }

    /**
     * @param integer $id
     * @return \tkanstantsin\yii2fileupload\model\File
     * @throws NotFoundHttpException
     */
    protected function findModel($id): File
    {
        $file = File::findOne($id);

        if ($file === null) {
            throw new NotFoundHttpException(404, \Yii::t('yii2fileupload', 'File not found'));
        }

        return $file;
    }

    /**
     * @param \tkanstantsin\yii2fileupload\model\File $file
     * @param string $fileType
     */
    protected function setHeaders(File $file, $fileType): void
    {
        $disposition = $fileType === Type::IMAGE ? 'inline' : 'attachment';

        header('Content-type: ' . $file->mime_type);
        header('Content-Disposition: ' . $disposition . '; filename="' . $file->name . '"');
    }

    /**
     * @param \tkanstantsin\yii2fileupload\model\File $file
     * @param array $fileOption
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \ErrorException
     */
    protected function displayFile(File $file, $fileOption): void
    {
        $stream = $this->contentFS->readStream($this->aliasConfig->getFilePath($file));
        echo stream_get_contents($stream);
        fclose($stream);
    }

    /**
     * @param \tkanstantsin\yii2fileupload\model\File $file
     * @param array $fileOptions
     * @param string $assetPath
     * @throws \League\Flysystem\FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \yii\base\ExitException
     * @throws \ErrorException
     */
    protected function displayImage(File $file, $fileOptions, string $assetPath): void
    {
        $filePath = $this->aliasConfig->getFilePath($file);
        $content = null;
        if ($this->webFS->has($assetPath)) {
            $content = $this->webFS->read($assetPath);
        } elseif ($this->contentFS->has($filePath)) {
            $content = $this->contentFS->read($filePath);
            // TODO: add cleanup of cached files.
            $this->webFS->write($assetPath, $content);
        }

        // TODO: process 404.
        // TODO: process image to specified size.
        // TODO: cache image basing on md5(id + updatedAt + options).

        echo $content;
        \Yii::$app->end();
    }

    /**
     * @param string $fileType
     * @return array
     */
    protected function processFileType($fileType): array
    {
        $options = [
            'originFileType' => $fileType,
        ];

        if (StringHelper::startsWith($fileType, 'image_')) {
            $parts = explode($fileType, '_');
            $width = $parts[1] ?? null;
            $height = $parts[2] ?? $width;

            $fileType = 'image';
            $options['width'] = $width;
            $options['height'] = $height;
        }

        return [$fileType, $options,];
    }
}
