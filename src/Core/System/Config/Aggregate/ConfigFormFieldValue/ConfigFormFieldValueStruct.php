<?php declare(strict_types=1);

namespace Shopware\Core\System\Config\Aggregate\ConfigFormFieldValue;

use Shopware\Core\Framework\ORM\Entity;
use Shopware\Core\System\Config\Aggregate\ConfigFormField\ConfigFormFieldStruct;

class ConfigFormFieldValueStruct extends Entity
{
    /**
     * @var string
     */
    protected $configFormFieldId;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     */
    protected $updatedAt;

    /**
     * @var ConfigFormFieldStruct|null
     */
    protected $configFormField;

    public function getConfigFormFieldId(): string
    {
        return $this->configFormFieldId;
    }

    public function setConfigFormFieldId(string $configFormFieldId): void
    {
        $this->configFormFieldId = $configFormFieldId;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getConfigFormField(): ?ConfigFormFieldStruct
    {
        return $this->configFormField;
    }

    public function setConfigFormField(ConfigFormFieldStruct $configFormField): void
    {
        $this->configFormField = $configFormField;
    }
}
