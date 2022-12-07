<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Block\Sales\Order\Tax;
use Magento\Tax\Model\Config;

class LuxuryTax extends Template
{
    /**
     * Tax configuration model
     *
     * @var Config
     */
    protected $_config;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var DataObject
     */
    protected $_source;

    /**
     * @param Context $context
     * @param Config $taxConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $taxConfig,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary(): bool
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return DataObject
     */
    public function getSource(): DataObject
    {
        return $this->_source;
    }

    public function getStore(): StoreInterface
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties(): array
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties(): array
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return Tax
     */
    public function initTotals(): self
    {

        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->getStore();

        $luxuryTax = new DataObject(
            [
                'code' => 'luxury_tax',
                'strong' => false,
                'value' => $this->_order->getLuxuryTaxAmount(),
                'base_value' => $this->_order->getBaseLuxuryTaxAmount(),
                'label' => __('Luxury tax'),
            ]
        );

        $parent->addTotal($luxuryTax, 'luxury_tax');

        return $this;
    }

}
