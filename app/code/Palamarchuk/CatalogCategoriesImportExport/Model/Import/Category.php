<?php

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Import;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEav;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class Category extends AbstractEav
{
    private \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    private \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection;
    private \Magento\Catalog\Model\CategoryFactory $categoryFactory;

    protected $masterAttributeCode = 'name';
    public const XML_PATH_BUNCH_SIZE = 'import/format_v2/category_bunch_size';


    public function __construct(
        \Magento\Framework\Stdlib\StringUtils              $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory          $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper   $resourceHelper,
        \Magento\Framework\App\ResourceConnection          $resource,
        ProcessingErrorAggregatorInterface                 $errorAggregator,
        \Magento\Store\Model\StoreManagerInterface         $storeManager,
        \Magento\ImportExport\Model\Export\Factory         $collectionFactory,
        \Magento\Eav\Model\Config                          $eavConfig,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array                                              $data = []
    ) {
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $storeManager,
            $collectionFactory,
            $eavConfig,
            $data
        );
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollection = $categoryCollection;
        $this->categoryFactory = $categoryFactory;
    }


    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = [];
            $entitiesToUpdate = [];
            $entitiesToDelete = [];
            $attributesToSave = [];



            foreach ($bunch as $rowNumber => $rowData) {



                $existingEntity =  $this->categoryCollection->getItems();
                $existingEntity =  $this->categoryCollection->addAttributeToFilter([['attribute'=>'name', 'eq'=>$rowData['name']]])->getItems();
                $existingEntity =  $this->categoryCollection->addAttributeToFilter('name', ['eq'=>$rowData['name']])->getItems();

                $existingEntity =  $this->categoryCollection->getItemByColumnValue('name', $rowData['name']);
                $existingEntity =  $this->categoryCollection->get



                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
                    $entitiesToDelete[] = $this->categoryCollection->getSelect()->where("url_key =  ?", $rowData['url_key']);
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
                    $existingEntity =  $this->categoryCollection->addAttributeToFilter([['attribute'=>'url_key', 'eq'=>$rowData['url_key']]])->getItems();
                    if (!$existingEntity){
                        $existingEntity =  $this->categoryCollection->addAttributeToFilter([['attribute'=>'name', 'eq'=>$rowData['name']]])->getItems();
                    }
                    if ($existingEntity) {
                        /** @var \Magento\Catalog\Model\Category $existingEntity */
//                        $existingEntity->setData($rowData);
                        $entitiesToUpdate[] = $existingEntity;
                    } else {
                        $newEntity = $this->categoryFactory->create();
//                        $newEntity->setData($rowData);
                        $entitiesToCreate[] = $newEntity;
                    }

//                    foreach ($processedData[self::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
//                        if (!isset($attributesToSave[$tableName])) {
//                            $attributesToSave[$tableName] = [];
//                        }
//                        $attributes = array_diff_key($attributesToSave[$tableName], $customerAttributes);
//                        $attributesToSave[$tableName] = $attributes + $customerAttributes;
//                    }
                }
            }
//
//            $entitiesToCreate = array_merge([], ...$entitiesToCreate);
//            $entitiesToUpdate = array_merge([], ...$entitiesToUpdate);
//
//            $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);
//            /**
//             * Save prepared data
//             */
//            if ($entitiesToCreate || $entitiesToUpdate) {
//                $this->_saveCategoryEntities($entitiesToCreate, $entitiesToUpdate);
//            }
////            if ($attributesToSave) {
////                $this->_saveCustomerAttributes($attributesToSave);
////            }
//            if ($entitiesToDelete) {
//                $this->_deleteCategoryEntities($entitiesToDelete);
//            }
        }
        $this->indexerProcessor->markIndexerAsInvalid();
        return true;
    }

    public function getEntityTypeCode()
    {
        return 'catalog_category';
    }

    public function validateRow(array $rowData, $rowNumber)
    {
        return true;
    }

//    protected function _getCategorieId($urlKey)
//    {
//        $email = strtolower(trim($email));
//        if (isset($this->_websiteCodeToId[$websiteCode])) {
//            $websiteId = $this->_websiteCodeToId[$websiteCode];
//            return $this->_customerStorage->getCustomerId($email, $websiteId);
//        }
//
//        return false;
//    }

    private function _saveCategoryEntities(array $entitiesToCreate, array $entitiesToUpdate)
    {
        $createUpdateEntities = array_merge($entitiesToUpdate, $entitiesToCreate);
        foreach ($createUpdateEntities as $entity) {
            $entity;
//            $this->categoryRepository->save($entity);
        }
    }

    private function _deleteCategoryEntities(array $entitiesToDelete)
    {
    }
}
