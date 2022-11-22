<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupExtensionFactory;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AlreadyExistsException;

class CustomerGroupRepositoryPlugin
{
    public function __construct(
        protected GroupRegistry   $groupRegistry,
        protected GroupExtensionFactory                   $extensionFactory,
        protected RequestInterface $request,
        protected Group $groupResourceModel,
    )
    {
    }

    public function afterGetList(
        GroupRepositoryInterface    $groupRepository,
        GroupSearchResultsInterface $groupSearchResults
    ): GroupSearchResultsInterface
    {
        foreach ($groupSearchResults->getItems() as $group) {
            $this->afterGetById($groupRepository, $group);
        }

        return $groupSearchResults;
    }

    public function afterGetById(
        GroupRepositoryInterface $groupRepository,
        GroupInterface           $group
    ): GroupInterface
    {
        $groupModel = $this->groupRegistry->retrieve($group->getId());
        $extensionAttributes = $group->getExtensionAttributes();
        $extensionAttributes->setLuxuryTaxId($groupModel->getData('luxury_tax_id'));
        $group->setExtensionAttributes($extensionAttributes);

        return $group;
    }

    /**
     * add luxury tax id to extension attributes
     */
    public function beforeSave(
        GroupRepositoryInterface $groupRepository,
        GroupInterface           $group
    ): array
    {
        $luxuryTaxId = $this->request->getParam('luxury_tax_id') ?? null;
        if ($luxuryTaxId !== null) {
            $customerGroupExtensionAttributes = $group->getExtensionAttributes() ?? $group->groupExtensionInterfaceFactory->create();
            $customerGroupExtensionAttributes->setLuxuryTaxId($luxuryTaxId);
            $group->setExtensionAttributes($customerGroupExtensionAttributes);
        }

        return [$group];
    }

    /**
     * add luxury tax id to customer_group table from extension attribute
     * @throws AlreadyExistsException
     */
    public function afterSave
    (
        GroupRepositoryInterface $groupRepository,
        GroupInterface $result,
        GroupInterface           $group
    ): GroupInterface
    {
        $extensionAttributes = $group->getExtensionAttributes();
        $luxuryTaxId = $extensionAttributes->getLuxuryTaxId();
        if ($luxuryTaxId !== null){
            $groupModel = $this->groupRegistry->retrieve($group->getId());
            $groupModel->setData('luxury_tax_id', $luxuryTaxId);
            $this->groupResourceModel->save($groupModel);
        }

        return $result;
    }
}
