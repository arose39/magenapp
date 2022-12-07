<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Logger;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Palamarchuk\LuxuryTax\Model\LuxuryTax;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;

class CustomerPersonalInfoLuxuryTax extends PersonalInfo
{
    protected $luxuryTaxRepository;

    public function __construct(
        LuxuryTaxRepository                $luxuryTaxRepository,
        Context                            $context,
        AccountManagementInterface         $accountManagement,
        GroupRepositoryInterface           $groupRepository,
        CustomerInterfaceFactory           $customerDataFactory,
        Address                            $addressHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        Registry                           $registry,
        Mapper                             $addressMapper,
        DataObjectHelper                   $dataObjectHelper,
        Logger                             $customerLogger,
        array                              $data = []
    ) {
        $this->luxuryTaxRepository = $luxuryTaxRepository;
        parent::__construct(
            $context,
            $accountManagement,
            $groupRepository,
            $customerDataFactory,
            $addressHelper,
            $dateTime,
            $registry,
            $addressMapper,
            $dataObjectHelper,
            $customerLogger,
            $data
        );
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
