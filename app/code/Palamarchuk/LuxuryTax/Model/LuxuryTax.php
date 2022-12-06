<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

class LuxuryTax extends AbstractExtensibleModel
{
    public const ID = 'luxury_tax_id';

    public function _construct()
    {
        $this->_init(\Palamarchuk\LuxuryTax\Model\ResourceModel\LuxuryTax::class);
    }

    /**
     * @return mixed|null
     */
    public function getId(): mixed
    {
        return $this->_getData(self::ID);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setId(mixed $value): self
    {
        $this->setData(self::ID, $value);

        return $this;
    }
}
