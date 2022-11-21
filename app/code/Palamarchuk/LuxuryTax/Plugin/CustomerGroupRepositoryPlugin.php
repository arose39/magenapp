<?php

namespace Palamarchuk\LuxuryTax\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;

class CustomerGroupRepositoryPlugin
{
    public function __construct(
        private \Magento\Customer\Model\GroupRegistry $groupRegistry,
    )
    {
    }

    public function afterGetList(
        GroupRepositoryInterface $groupRepository,
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
}
