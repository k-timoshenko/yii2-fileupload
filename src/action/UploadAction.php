<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload\action;


use tkanstantsin\fileupload\saver\Factory;
use tkanstantsin\fileupload\saver\Uploader;
use yii\db\ActiveRecord;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class UploadAction saves any file according config defined in aliases.
 * @see \tkanstantsin\fileupload\config\Alias
 *
 * @example acton definition:
 * public function actions()
 * {
 *      return [
 *          'upload' => [
 *              'class' => file\actions\FileUploadAction::className(),
 *              'fs' => \Yii::$app->contentFS,
 *          ],
 *      ];
 * }
 *
 * @example url: /path/to/controller/upload?alias=product&id=10
 *
 * @todo: create class/interface/behavior to detect `right` models.
 */
class UploadAction extends AbstractAction
{
    /**
     * GET param name of `id`.
     * @var string
     */
    public $modelIdParam = 'id';
    /**
     * GET param name of `alias`.
     * @var string
     */
    public $modelAliasParam = 'alias';
    /**
     * File param name.
     * @var string
     */
    public $fileParam = 'file';

    /**
     * File url param name.
     * @var string
     */
    public $fileUrlParam = 'file-url';

    /**
     * Response type format.
     * @var string
     */
    public $responseFormat = Response::FORMAT_JSON;

    /**
     * Owner model id.
     * @var integer
     */
    protected $ownerId;
    /**
     * Owner model short alias.
     * @var string
     */
    protected $ownerModelAlias;
    /**
     * Full owner model class name.
     * @var string
     */
    protected $ownerModelClass;
    /**
     * Owner model from db.
     * @var ActiveRecord
     */
    protected $ownerModel;

    /**
     * Error message. This default value sets when no specified error message
     * exists.
     * @var string
     */
    protected $errorMessage;
    /**
     * Message on success uploading.
     * @var string
     */
    protected $successMessage;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->successMessage = $this->successMessage ?? \Yii::t('yii2fileupload', 'File successfully uploaded.');
        $this->errorMessage = $this->errorMessage ?? \Yii::t('yii2fileupload', 'Error occurs file file uploading.');

        \Yii::$app->response->format = $this->responseFormat;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @throws \League\Flysystem\FileExistsException
     */
    public function run()
    {
        $id = (int) \Yii::$app->request->get($this->modelIdParam);
        $id = $id > 0 ? $id : null;
        $alias = (string) \Yii::$app->request->get($this->modelAliasParam);
        $this->setQueryData($id, $alias);

        $uploadedFile = $this->createFile();
        $uploader = $uploadedFile === null
            ? null
            : new Uploader(
                $this->fileManager,
                $uploadedFile,
                $this->fileManager->getAliasConfig($this->ownerModelAlias),
                $this->contentFS,
                [ // other config
                    'parent_model' => $this->ownerModelAlias,
                    'parent_model_id' => $this->ownerId,
                ]);

        if ($uploader !== null && $uploader->upload()) {
            $data = [
                'files' => $this->fileManager->getFileDataArray([$uploader]),
            ];
        } else {
            $data = [
                'name' => $uploadedFile->name ?? null,
                'size' => $uploadedFile->size ?? 0,
                'error' => $this->errorMessage,
            ];
        }


        return $data;
    }

    /**
     * @param int $id
     * @param string $alias
     * @throws \ErrorException
     */
    protected function setQueryData(int $id = null, string $alias): void
    {
        $this->ownerId = $id;
        $this->ownerModelAlias = $alias;

        $this->ownerModelClass = $this->fileManager->getModelByAlias($this->ownerModelAlias);
        $this->ownerModel = $this->findOwnerModel($this->ownerModelClass, $this->ownerId);
    }

    /**
     * @param string|ActiveRecord $modelClass
     * @param integer $id
     * @return ActiveRecord|null
     */
    protected function findOwnerModel($modelClass, $id): ?ActiveRecord
    {
        if ($id === null) {
            return null;
        }

        $model = $modelClass::findOne($id);
        if ($model === null) {
            $this->errorMessage = \Yii::t('yii2fileupload', 'Owner model with id `{id}` not found.', ['id' => $id]);

            return null;
        }

        return $model;
    }

    /**
     * Creates file from input parameters
     * @return UploadedFile|null
     */
    protected function createFile(): ?UploadedFile
    {
        $uploadedFile = UploadedFile::getInstanceByName($this->fileParam);
        if ($uploadedFile === null) {
            $url = \Yii::$app->request->post($this->fileUrlParam);
            if ($url !== null) {
                return Factory::buildFromUrl(\Yii::$app->request->post($this->fileUrlParam));
            }
        }

        return $uploadedFile;
    }
}
