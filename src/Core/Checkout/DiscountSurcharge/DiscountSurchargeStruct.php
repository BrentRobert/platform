<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\DiscountSurcharge;

use Shopware\Core\Content\Rule\RuleStruct;
use Shopware\Core\Framework\ORM\Entity;
use Shopware\Core\Framework\ORM\Search\EntitySearchResult;
use Shopware\Core\Framework\Rule\Rule;

class DiscountSurchargeStruct extends Entity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Rule
     */
    protected $filterRule;

    /**
     * @var string
     */
    protected $ruleId;

    /**
     * @var RuleStruct
     */
    protected $rule;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var null|EntitySearchResult
     */
    protected $translations;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRuleId(): string
    {
        return $this->ruleId;
    }

    public function setRuleId(string $ruleId): void
    {
        $this->ruleId = $ruleId;
    }

    public function getRule(): RuleStruct
    {
        return $this->rule;
    }

    public function setRule(RuleStruct $rule): void
    {
        $this->rule = $rule;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getFilterRule(): Rule
    {
        return $this->filterRule;
    }

    public function setFilterRule(Rule $filterRule): void
    {
        $this->filterRule = $filterRule;
    }

    public function getTranslations(): ?EntitySearchResult
    {
        return $this->translations;
    }

    public function setTranslations(EntitySearchResult $translations): void
    {
        $this->translations = $translations;
    }
}
