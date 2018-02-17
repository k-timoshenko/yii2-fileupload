<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload\action;


use League\Flysystem\Filesystem;
use tkanstantsin\fileupload\FileManager;
use yii\base\Action;

/**
 * Class AbstractAction
 */
class AbstractAction extends Action
{
    /**
     * Filesystem component of file sources
     * @var Filesystem
     */
    public $contentFS;
    /**
     * @var \tkanstantsin\fileupload\FileManager
     */
    public $fileManager;

    /**
     * @inheritdoc
     * @throws \ErrorException
     */
    public function init()
    {
        if (!($this->contentFS instanceof Filesystem)) {
            // TODO: add i18n namespace.
            throw new \ErrorException(\Yii::t('yii2fileupload', 'Content FS must be defined and be instance of `{class}`', ['class' => Filesystem::class]));
        }

        if (!($this->fileManager instanceof FileManager)) {
            throw new \ErrorException(\Yii::t('yii2fileupload', 'File manager component must be defined and be instance of `{class}`', ['class' => FileManager::class]));
        }
    }
}
