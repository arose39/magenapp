<?php

namespace Palamarchuk\LuxuryTax\Model\Total;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\QuoteValidator;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;

class LuxuryTaxTotal extends AbstractTotal
{
    const NOT_LOGGED_IN_CUSTOMER_GROUP = 0;
    /**
     * Collect grand total address amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    protected $quoteValidator = null;
    private $groupRepository;
    private $luxuryTaxRepository;

    public function __construct(
        QuoteValidator           $quoteValidator,
        GroupRepositoryInterface $groupRepository,
        LuxuryTaxRepository      $luxuryTaxRepository
    )
    {
        $this->quoteValidator = $quoteValidator;
        $this->groupRepository = $groupRepository;
        $this->luxuryTaxRepository = $luxuryTaxRepository;
    }

    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        $luxuryTaxAmount = $this->getLuxuryTaxAmount($quote);
        $total->setTotalAmount('luxury_tax', $luxuryTaxAmount);
        $total->setBaseTotalAmount('luxury_tax', $luxuryTaxAmount);

        $total->setLuxuryTax($luxuryTaxAmount);
        $total->setBaseLuxuryTax($luxuryTaxAmount);

        return $this;
    }

    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setTotalAmount('luxury_tax', 0);
        $total->setBaseTotalAmount('luxury_tax', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
    /**
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     */
    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total)
    {
        $luxuryTaxAmount = $this->getLuxuryTaxAmount($quote);

        return [
            'code' => 'luxury_tax',
            'title' => 'Luxury tax',
            'value' => $luxuryTaxAmount,
            'base_value' => $luxuryTaxAmount
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Luxury tax');
    }

    private function getLuxuryTaxAmount(Quote $quote)
    {
        if ($quote->getCustomerIsGuest()) {
            $customerGroupId = self::NOT_LOGGED_IN_CUSTOMER_GROUP;
        } else {
            $customerGroupId = $quote->getCustomerGroupId();
        }
        $luxuryTaxId = $this->groupRepository->getById($customerGroupId)->getExtensionAttributes()->getLuxuryTaxId();
        //getLuxuryTaxAmount -> getLuxuryTaxRate . Wrong naming in luxuryTaxEntity
        $luxuryTaxPercent = $this->luxuryTaxRepository->get($luxuryTaxId)->getLuxuryTaxAmount() / 100;

        return $quote->getBaseSubtotal() * $luxuryTaxPercent;
    }
}
