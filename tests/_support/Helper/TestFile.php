<?php
declare(strict_types=1);

namespace Helper;

use tkanstantsin\fileupload\model\BaseObject;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class TestFile
 * @todo: create factory for model.
 */
class TestFile extends BaseObject implements IFile
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string|null
     */
    protected $modelAlias;
    /**
     * @var int|null
     */
    protected $modelId;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $extension;
    /**
     * @var int|null
     */
    protected $size;
    /**
     * @var int|null
     */
    protected $type;
    /**
     * @var string|null
     */
    protected $mimeType;
    /**
     * @var string|null
     */
    protected $hash;
    /**
     * @var int|null
     */
    protected $createdAt;
    /**
     * @var int|null
     */
    protected $updatedAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
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
        return $this->modelAlias;
    }

    /**
     * @param string $alias
     */
    public function setModelAlias(string $alias): void
    {
        $this->modelAlias = $alias;
    }

    /**
     * Id of associated model
     * @return int|null
     */
    public function getModelId(): ?int
    {
        return $this->modelId ? (int) $this->modelId : null;
    }

    /**
     * @param int $modelId
     */
    public function setModelId(int $modelId): void
    {
        $this->modelId = $modelId;
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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(int $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
