<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload;

use tkanstantsin\fileupload\FileManager as BaseFileManager;
use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type as FileType;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\UrlManager;

/**
 * Class FileComponent
 *
 * @todo: proxy all methods to the real filemanager.
 */
class FileManager extends Component
{
    /**
     * Will hide file formatting exception if true
     * @var bool
     */
    public $silentMode = true;

    /**
     * Url manager used for correct url generating
     * @var UrlManager
     */
    public $urlManager;

    /**
     * Base url for uploading files
     * @var string
     */
    public $uploadBaseUrl;
    /**
     * Path to folder which would contain cached files.
     * /cache-base-path/file-path-in-file-system
     * @var string
     */
    public $cacheBasePath;

    /**
     * Add updatedAt timestamp if it defined
     * @var bool
     */
    public $assetAppendTimestamp = true;

    /**
     * Url for default image
     * NOTE: for correct routing it is recommended to set array
     * @var string|array
     */
    public $imageNotFoundUrl;
    /**
     * Url for 404 page for files
     * NOTE: for correct routing it is recommended to set array
     * @var string|array
     */
    public $fileNotFoundUrl;

    /**
     * @var BaseFileManager
     */
    public $manager;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
    public function init(): void
    {
        if ($this->urlManager === null) {
            $this->urlManager = \Yii::$app->urlManager;
        }
        if (\is_string($this->urlManager)) {
            $this->urlManager = \Yii::$app->get($this->urlManager);
        }
        if (!($this->urlManager instanceof UrlManager)) {
            throw new InvalidConfigException('Url manager must be defined');
        }

        if ($this->uploadBaseUrl === null) {
            throw new \ErrorException('Base upload url must be defined.');
        }
        if ($this->cacheBasePath === null) {
            throw new \ErrorException('Base path for cache must be defined.');
        }
        if ($this->imageNotFoundUrl === null || $this->fileNotFoundUrl === null) {
            throw new \ErrorException('URLs for not founded image and file must be defined.');
        }

        $this->manager['contentFS'] = \Yii::$app->{$this->manager['contentFS']}->getFileSystem();
        $this->manager['cacheFS'] = \Yii::$app->{$this->manager['cacheFS']}->getFileSystem();

        $class = ArrayHelper::remove($this->manager, 'class', BaseFileManager::class);
        $this->manager = new $class($this->manager);
        if (!($this->manager instanceof BaseFileManager)) {
            throw new InvalidConfigException(\Yii::t('yii2fileupload', 'Invalid file manager config'));
        }

        parent::init();
    }

    /**
     * Generates url for upload file with upload widget.
     * Url format: $uploadBaseUrl/$alias/$id
     * @param string $aliasName
     * @param int|null $id
     * @return string
     */
    public function getUploadUrl(string $aliasName, int $id = null): string
    {
        return $this->urlManager->createUrl(implode(DIRECTORY_SEPARATOR, array_filter([$this->uploadBaseUrl, $aliasName, $id])));
    }

    /**
     * @param null|IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @return string
     * @throws \Exception
     */
    public function getFileUrl(?IFile $file, string $format, array $formatterConfig = []): string
    {
        return $this->createUrl($this->getFileUrlInternal($file, $format, $formatterConfig), false);
    }

    /**
     * @param null|IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @return string
     * @throws \Exception
     */
    public function getFileAbsoluteUrl(?IFile $file, string $format, array $formatterConfig = []): string
    {
        return $this->createUrl($this->getFileUrlInternal($file, $format, $formatterConfig), true);
    }

    /**
     * Format file and return image link if failed
     * @param null|IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @param string|null $notFoundUrl
     * @return string
     * @throws \Exception
     */
    public function getImageUrl(?IFile $file, string $format, array $formatterConfig = [], string $notFoundUrl = null): string
    {
        return $this->createUrl($this->getImageUrlInternal($file, $format, $formatterConfig, $notFoundUrl), false);
    }

    /**
     * Create absolute url to image
     * @param null|IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @param string|null $notFoundUrl
     * @return string
     * @throws \Exception
     */
    public function getImageAbsoluteUrl(?IFile $file, string $format, array $formatterConfig = [], string $notFoundUrl = null): string
    {
        return $this->createUrl($this->getImageUrlInternal($file, $format, $formatterConfig, $notFoundUrl), true);
    }

    /**
     * Choose 404 url
     * @param int $fileTypeId
     * @return array|string
     */
    public function getNotFoundUrl(int $fileTypeId)
    {
        switch ($fileTypeId) {
            case FileType::IMAGE:
                return $this->imageNotFoundUrl;
            case FileType::FILE:
            default:
                return $this->fileNotFoundUrl;
        }
    }

    /**
     * @param null|IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @return array|string
     * @throws \Exception
     */
    protected function getFileUrlInternal(?IFile $file, string $format, array $formatterConfig = [])
    {
        $path = $this->getFilePath($file, $format, $formatterConfig);
        if ($path !== null) {
            $fileTypeId = $file !== null && $file->getType() !== null
                ? $file->getType()
                : FileType::FILE;

            return $this->getNotFoundUrl($fileTypeId);
        }


        return $this->cacheBasePath . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param null|IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @param string|null $notFoundUrl
     * @return null|string
     * @throws \Exception
     */
    protected function getImageUrlInternal(?IFile $file, string $format, array $formatterConfig = [], string $notFoundUrl = null): ?string
    {
        $path = $this->getFilePath($file, $format, $formatterConfig);
        if ($path === null) {
            return $notFoundUrl ?? $this->getNotFoundUrl(FileType::IMAGE);
        }

        $url = $this->cacheBasePath . DIRECTORY_SEPARATOR . $path;
        if ($this->assetAppendTimestamp && $file !== null && $file->getUpdatedAt() !== null) {
            $url .= '?' . $file->getUpdatedAt();
        }

        return $url;
    }

    /**
     * Caches file and returns url to it. Return 404 image or link if fails
     * without exception.
     * @param null|IFile $file
     * @param string $format
     * @param array $formatterConfig
     * @return null|string
     * @throws \Exception
     */
    protected function getFilePath(?IFile $file, string $format, array $formatterConfig = []): ?string
    {
        if ($file === null || $file->getId() === null /*null if file is not saved yet*/) {
            return null;
        }

        try {
            return $this->manager->getFilePath($file, $format, $formatterConfig);
        } catch (\Exception $e) {
            if (!$this->silentMode) {
                throw $e;
            }
        }

        return null;
    }

    /**
     * Create url with UrlManager
     * @param array|string $params
     * @param bool $absolute
     * @return string
     */
    protected function createUrl($params, bool $absolute = false): string
    {
        return $absolute === true
            ? $this->urlManager->createAbsoluteUrl($params)
            : $this->urlManager->createUrl($params);
    }
}
