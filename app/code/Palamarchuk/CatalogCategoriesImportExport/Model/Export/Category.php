<?php

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Export;

use Magento\Framework\Model\AbstractModel;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\ImportExport\Model\Import;

class Category extends AbstractEav
{
    const COLUMN_WEBSITE = '_website';

    const COLUMN_STORE = '_store';

    /**
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection::class;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_entityCollectionFactory;
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $_entityCollection;
    protected $_attributeCollection;
    private $_logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface                         $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface                                 $storeManager,
        \Magento\ImportExport\Model\Export\Factory                                 $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface                       $localeDate,
        \Magento\Eav\Model\Config                                                  $eavConfig,
//        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory           $categoryColFactory,
        \Magento\Catalog\Model\ResourceModel\Category\Collection                   $collection,
        \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection         $attributeCollection,
        \Psr\Log\LoggerInterface                                                   $logger,
//        \Magento\ImportExport\Model\Export\Adapter\Csv      $writer,
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
//        $this->_entityCollectionFactory = $categoryColFactory;
        $this->_attributeCollection = $attributeCollection;
        $this->_entityCollection = $collection;
        $this->_logger = $logger;
//        $this->_writer = $writer;

        $this->_initAttributeValues()->_initAttributeTypes()->_initStores()->_initWebsites(true);

//        $this->export();
    }

    public function export()
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

    public function exportItem($item)
    {
        try {
            $item = $this->prepareItemToAddAttributeValuesToRow($item);
            $row = $this->_addAttributeValuesToRow($item);
//        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
//        $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];

            $this->getWriter()->writeRow($row);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    public function getEntityTypeCode()
    {
        return "catalog_category";
    }

    protected function _getHeaderColumns()
    {
        try {
            return $this->_getExportAttributeCodes();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    protected function _getEntityCollection()
    {
//        if ($resetCollection || empty($this->_entityCollection)) {
//            $this->_entityCollection = $this->_entityCollectionFactory->create();
//        }

        return $this->_entityCollection;
    }

//    public function getAttributeCollection()
//    {
//        return $this->_attributeCollection;
//    }

//    public function getWriter()
//    {
//        return $this->_writer;
//    }

    /**
     * fix issues with null != "" in php8+ versions
     * @param AbstractModel $item
     * @return AbstractModel
     */
    protected function prepareItemToAddAttributeValuesToRow(\Magento\Framework\Model\AbstractModel $item)
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        // go through all valid attribute codes
        foreach ($validAttributeCodes as $attributeCode) {
            if ($item->getData($attributeCode)===null) {
                $item->setData($attributeCode, '0');
            }
        }

        return $item;
    }
}
