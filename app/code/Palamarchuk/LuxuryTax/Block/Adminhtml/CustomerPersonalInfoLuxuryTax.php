<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Block\Adminhtml;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Palamarchuk\LuxuryTax\Model\LuxuryTax;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;

class CustomerPersonalInfoLuxuryTax extends PersonalInfo
{
    protected $luxuryTaxRepository;

    public function __construct(
        LuxuryTaxRepository                                 $luxuryTaxRepository,
        \Magento\Backend\Block\Template\Context             $context,
        AccountManagementInterface                          $accountManagement,
        \Magento\Customer\Api\GroupRepositoryInterface      $groupRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Helper\Address                    $addressHelper, \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Registry                         $registry, Mapper $addressMapper,
        \Magento\Framework\Api\DataObjectHelper             $dataObjectHelper,
        \Magento\Customer\Model\Logger                      $customerLogger,
        array                                               $data = [])
    {
        $this->luxuryTaxRepository = $luxuryTaxRepository;
        parent::__construct($context, $accountManagement, $groupRepository, $customerDataFactory, $addressHelper, $dateTime, $registry, $addressMapper, $dataObjectHelper, $customerLogger, $data);
    }

    public function getCustomerLuxuryTaxName(): string
    {
        return $this->getCustomerLuxuryTax()->getName();
    }

    public function getCustomerLuxuryTaxDescription(): string
    {
        return $this->getCustomerLuxuryTax()->getDescription();
    }

    public function getCustomerLuxuryTaxAmount(): int
    {
        return (int)$this->getCustomerLuxuryTax()->getLuxuryTaxAmount();
    }

    public function getCustomerLuxuryTaxStatus(): string
    {
        return $this->getCustomerLuxuryTax()->getStatus() ? 'active' : 'no active';
    }

    protected function getCustomerLuxuryTax(): ?LuxuryTax
    {
        $customer = $this->getCustomer();
        if ($groupId = $customer->getId() ? $customer->getGroupId() : null) {


            if ($group = $this->getGroup($groupId)) {
                $luxuryTaxId = (int)$group->getExtensionAttributes()->getLuxuryTaxId();

                return $this->luxuryTaxRepository->get($luxuryTaxId);
            }
        }

        return null;
    }

    /**
     * Retrieve customer group by id
     *
     * @param int $groupId
     * @return GroupInterface|null
     */
    protected function getGroup(int $groupId): ?GroupInterface
    {
        try {
            $group = $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            $group = null;
        }
        return $group;
    }
}
