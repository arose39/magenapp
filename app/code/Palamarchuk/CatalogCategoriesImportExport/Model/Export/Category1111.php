<?php

declare(strict_types=1);

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Export;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\ImportExport\Model\Export\Entity\AbstractEntity;

use Magento\Store\Model\Store;

class Category1111 extends AbstractEntity
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_entityCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $_attributeColFactory;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $_entityCollection;

    /**
     * @var CategoryListInterface
     */
    private $categoryList;

    /**
     * @var RequestInterface
     */
    private $request;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface                      $localeDate,
        \Magento\Eav\Model\Config                                                 $config,
        ResourceConnection                                                        $resource,
        \Magento\Store\Model\StoreManagerInterface                                $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory           $categoryColFactory,
        \Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory $attributeColFactory,
        \Psr\Log\LoggerInterface                                                  $logger,
        RequestInterface    $request,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryListInterface $categoryList,
    ) {
        parent::__construct($localeDate, $config, $resource, $storeManager);
        $this->_entityCollectionFactory = $categoryColFactory;
        $this->_attributeColFactory = $attributeColFactory;
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->request = $request;
        $this->_logger = $logger;
    }

    /**
     * @throws NoSuchEntityException
     */
    protected function _getHeaderColumns()
    {
        if (isset($this->_getEntityCollection()[0])) {
            return array_keys($this->_getEntityCollection()[0]->toArray());
        }

        throw new NoSuchEntityException("Catalog category entity is not found");
    }

    /**
     * Get entity collection
     *
     * @param bool $resetCollection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection($resetCollection = false)
    {
        if ($resetCollection || empty($this->_entityCollection)) {
            $this->_entityCollection = $this->_entityCollectionFactory->create();
//            $this->_entityCollection = $this->getCategoriesList();
        }

        return $this->_entityCollection;
    }

    /**
     * Export process
     *
     * @return string
     */
    public function export()
    {
        //Execution time may be very long
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        set_time_limit(0);
        try {
            $writer = $this->getWriter();
            $page = 0;
//            while (true) {
            ++$page;
//                $entityCollection = $this->_getEntityCollection(true);
//                $entityCollection->setOrder('entity_id', 'asc');
//                $entityCollection->setStoreId(Store::DEFAULT_STORE_ID);
//                $this->_prepareEntityCollection($entityCollection);
//                if ($entityCollection->count() == 0) {
//                    break;
//                }
            $exportData = $this->_getEntityCollection();
//                array_unshift($exportData, $this->_getHeaderColumns());
//                if ($page == 1) {
            $writer->setHeaderCols($this->_getHeaderColumns());
//                }
            foreach ($exportData as $dataRow) {
                $writer->writeRow($dataRow->toArray());
            }
//                if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
//                    break;
//                }
//            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $writer->getContents();
    }

    /**
     * Entity attributes collection getter.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection
     */
    public function getAttributeCollection()
    {
        return $this->_attributeColFactory->create();
    }

    public function getEntityTypeCode()
    {
        return "catalog_category";
    }

    private function getCategoriesList()
    {
        if (isset($this->request->getParams()['export_filter'])) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilters($this->request->getParams()['export_filter'])->create();

            return $this->categoryList->getList($searchCriteria)->getItems();
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->categoryList->getList($searchCriteria)->getItems();
    }

//    public function exportItem($item)
//    {
//        $row = $this->_addAttributeValuesToRow($item);
//        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
//        $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];
//
//        $this->getWriter()->writeRow($row);
//    }
}
