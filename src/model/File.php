<?php
declare(strict_types=1);

namespace tkanstantsin\yii2fileupload\model;

use tkanstantsin\fileupload\model\IFile;
use tkanstantsin\fileupload\model\Type;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class File
 *
 * The followings are the available columns in table 'image':
 * @property int $id
 * @property int $parent_model_id
 * @property string $parent_model
 * @property string $name
 * @property string $extension
 * @property bool $is_deleted
 * @property bool $is_confirmed
 * @property int $size
 * @property string $mime_type
 * @property int $type_id
 * @property string $hash
 * @property int $priority
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 *
 * @property \yii\db\ActiveRecord|null $ownerModel
 * @property string $fullName
 */
class File extends ActiveRecord implements IFile
{
    public const MAX_AGE = 2 * 60 * 60;
    public const MAX_NAME_LENGTH = 255;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        // TODO: how to defined it outside via any type of config?
        return '{{%file}}';
    }

    /**
     * @return Query
     * @throws \yii\base\InvalidConfigException
     */
    public static function find(): Query
    {
        return \Yii::createObject(Query::class, [static::class]);
    }


    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            // filters
            [
                'name',
                function ($attribute) {
                    $name = $this->$attribute ?: static::DEFAULT_FILENAME;
                    // remove all wrong characters
                    $name = preg_replace('/[^A-zА-я0-9\s]+/u', '-', $name);
                    $name = preg_replace('/[\-]+/', '-', $name); // remove duplicates
                    // remove odd whitespaces
                    $name = preg_replace('/\s+/u', ' ', $name);
                    $name = trim($name); // remove duplicates
                    $name = mb_substr($name, 0, self::MAX_NAME_LENGTH);

                    return $name;
                },
                'skipOnEmpty' => false,
            ],

            // defaults
            [['is_deleted'], 'default', 'value' => false],
            [['priority'], 'default', 'value' => null],

            // rules
            [['parent_model', 'size', 'name', 'mime_type', 'hash'], 'required'],
            [['parent_model_id', 'size'], 'integer'],
            [['parent_model'], 'string', 'max' => 45],
            [['name'], 'string', 'max' => self::MAX_NAME_LENGTH],
            [['extension'], 'string', 'max' => 45],
            [['mime_type'], 'string', 'max' => 100],
            [['type_id'], 'in', 'range' => array_keys(Type::all())],
            [['hash'], 'string', 'length' => 32],
            [['priority'], 'integer'],
            [['is_deleted'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => \Yii::t('yii2fileupload', 'ID'),
            'parent_model_id' => \Yii::t('yii2fileupload', 'Model ID'),
            'parent_model' => \Yii::t('yii2fileupload', 'Model'),
            'name' => \Yii::t('yii2fileupload', 'Filename'),
            'extension' => \Yii::t('yii2fileupload', 'File ext'),
            'fullName' => \Yii::t('yii2fileupload', 'Full filename'),
            'size' => \Yii::t('yii2fileupload', 'Size'),
            'is_confirmed' => \Yii::t('yii2fileupload', 'Is confirmed'),
            'is_deleted' => \Yii::t('yii2fileupload', 'Is deleted'),
            'mime_type' => \Yii::t('yii2fileupload', 'MIME type'),
            'type_id' => \Yii::t('yii2fileupload', 'Type'),
            'hash' => \Yii::t('yii2fileupload', 'Hash (md5)'),
            'priority' => \Yii::t('yii2fileupload', 'Priority'),
            'created_at' => \Yii::t('yii2fileupload', 'Created at'),
            'updated_at' => \Yii::t('yii2fileupload', 'Updated at'),
            'deleted_at' => \Yii::t('yii2fileupload', 'Deleted at'),
        ];
    }


    /* GETTERS */

    /**
     * @return ActiveRecord|null
     * @throws \Exception
     */
    public function getOwnerModel(): ?ActiveRecord
    {
        $modelClass = \Yii::$app->fileManager->getModelByAlias($this->parent_model);
        if (!class_exists($modelClass)) {
            throw new \ErrorException("Class `$modelClass` does not exists.");
        } elseif (!is_subclass_of($modelClass, ActiveRecord::class)) {
            throw new \ErrorException("`$modelClass` must extend `" . ActiveRecord::class . "` class.");
        }

        /* @var ActiveRecord $modelClass */

        return $modelClass::findOne($this->parent_model_id);
    }

    /**
     * Whether file is not temp or deleted.
     * @return bool
     */
    public function isActual(): bool
    {
        return $this->is_confirmed && !$this->is_deleted;
    }

    /**
     * Marks file for deletion
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function lazyDelete(): bool
    {
        if ($this->is_deleted) {
            return true;
        }

        return false !== $this->update([
                'is_deleted' => true,
                'deleted_at' => time(),
            ]);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Alias of associated model
     * @return null|string
     */
    public function getModelAlias(): ?string
    {
        return $this->parent_model;
    }

    /**
     * @param string $alias
     */
    public function setModelAlias(string $alias): void
    {
        $this->parent_model = $alias;
    }

    /**
     * Id of associated model
     * @return int|null
     */
    public function getModelId(): ?int
    {
        return $this->parent_model_id ? (int) $this->parent_model_id : null;
    }

    /**
     * @param int $modelId
     */
    public function setModelId(int $modelId): void
    {
        $this->parent_model_id = $modelId;
    }


    /**
     * Get name without extension
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name without extension
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns full name of file with extension
     * @return string
     */
    public function getFullName(): string
    {
        return $this->name . ($this->extension !== null ? '.' . $this->extension : '');
    }


    /**
     * Get file extension
     * @return null|string
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * Set file extension
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return int
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type_id;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type_id = $type;
    }

    /**
     * @return null|string
     */
    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mime_type = $mimeType;
    }

    /**
     * @return string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): ?int
    {
        return $this->created_at;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt(int $createdAt): void
    {
        $this->created_at = $createdAt;
    }

    /**
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        return $this->updated_at;
    }

    /**
     * @param int $updatedAt
     */
    public function setUpdatedAt(int $updatedAt): void
    {
        $this->updated_at = $updatedAt;
    }
}
