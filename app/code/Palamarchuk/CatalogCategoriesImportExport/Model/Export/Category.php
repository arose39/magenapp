<?php

declare(strict_types=1);

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Export;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category extends AbstractEav
{
    const COLUMN_WEBSITE = '_website';
    const COLUMN_STORE = '_store';
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection::class;
    protected $_entityCollectionFactory;
    protected $_entityCollection;
    protected $_attributeCollection;
    private $_logger;
    private CollectionFactory $categoryCollectionFactory;
    private RequestInterface $request;
    private int $reqestStoreId;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface                         $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface                                 $storeManager,
        \Magento\ImportExport\Model\Export\Factory                                 $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface                       $localeDate,
        \Magento\Eav\Model\Config                                                  $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Category\Collection                   $collection,
        \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection         $attributeCollection,
        \Psr\Log\LoggerInterface                                                   $logger,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory                  $categoryCollectionFactory,
        \Magento\Framework\App\RequestInterface   $request,
        array                                                                      $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $data
        );
        $this->request = $request;
        $this->reqestStoreId = (int)$this->request->getParam('store_id');
        $this->_attributeCollection = $attributeCollection;
        $this->_entityCollection = $collection;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->_logger = $logger;
        $this->_initAttributeValues()->_initAttributeTypes()->_initStores()->_initWebsites(true);
    }

    public function export(): string
    {
        try {
            $this->_prepareEntityCollection($this->_getEntityCollection());
            $writer = $this->getWriter();

            // create export file
            $writer->setHeaderCols($this->_getHeaderColumns());
            $this->_exportCollectionByPages($this->_getEntityCollection());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $writer->getContents();
    }

    public function exportItem($item):void
    {
        try {
            $item = $this->prepareItemToAddAttributeValuesToRow($item);
            $row = $this->_addAttributeValuesToRow($item);
            $this->getWriter()->writeRow($row);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    public function getEntityTypeCode(): string
    {
        return "catalog_category";
    }

    protected function _getHeaderColumns():array
    {
        try {
            return $this->_getExportAttributeCodes();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    protected function _getEntityCollection(): Collection
    {
        return $this->getEntityCollectionForDistinctStoreId($this->reqestStoreId);
    }

    /**
     * fix issues with null != "" in php8+ versions
     * @param AbstractModel $item
     * @return AbstractModel
     */
    protected function prepareItemToAddAttributeValuesToRow(\Magento\Framework\Model\AbstractModel $item): AbstractModel
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        // go through all valid attribute codes
        foreach ($validAttributeCodes as $attributeCode) {
            if ($item->getData($attributeCode)===null) {
                $item->setData($attributeCode, '');
            }
        }

        return $item;
    }

    private function getEntityCollectionForDistinctStoreId($storeId): Collection
    {
        /** @var Collection $collection */
        $collection = $this->categoryCollectionFactory->create()->setStoreId($storeId)->addAttributeToSelect('*');
        foreach ($collection as $entity) {
            if (!$entity->getName()) {
                $collection->removeItemByKey($entity->getId());
            }
        }

        return $collection;
    }
}
