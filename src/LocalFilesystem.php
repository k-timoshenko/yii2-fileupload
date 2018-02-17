<?php
declare(strict_types=1);

/**
 * @link https://github.com/creocoder/yii2-flysystem
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tkanstantsin\fileupload\flysystem;

use creocoder\flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use yii\base\InvalidConfigException;

/**
 * LocalFilesystem
 *
 * @todo: checkout purpose of this class.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class LocalFilesystem extends Filesystem
{
    /**
     * @var string
     */
    public $path;
    public $writeFlags = LOCK_EX;
    public $linkHandling = Local::DISALLOW_LINKS;
    public $permissions = [];

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        if ($this->path === null) {
            throw new InvalidConfigException(\Yii::t('yii2fileupload', 'The "path" property must be set.'));
        }

        $this->path = \Yii::getAlias($this->path);

        parent::init();
    }

    /**
     * @return Local
     * @throws \LogicException
     */
    protected function prepareAdapter(): Local
    {
        return new Local($this->path, $this->writeFlags, $this->linkHandling, $this->permissions);
    }
}
