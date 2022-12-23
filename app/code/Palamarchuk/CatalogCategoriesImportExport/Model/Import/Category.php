<?php

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Import;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEav;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class Category extends AbstractEav
{
    private \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    private \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection;
    private \Magento\Catalog\Model\CategoryFactory $categoryFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;
    protected $masterAttributeCode = 'name';
    public const XML_PATH_BUNCH_SIZE = 'import/format_v2/category_bunch_size';
    private \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory;
    private CategoryListInterface $categoryList;
    private \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria;
    private $lastCategoryId;


    public function __construct(
        \Magento\Framework\Stdlib\StringUtils                              $string,
        \Magento\Framework\App\Config\ScopeConfigInterface                 $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory                          $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper                   $resourceHelper,
        \Magento\Framework\App\ResourceConnection                          $resource,
        ProcessingErrorAggregatorInterface                                 $errorAggregator,
        \Magento\Store\Model\StoreManagerInterface                         $storeManager,
        \Magento\ImportExport\Model\Export\Factory                         $collectionFactory,
        \Magento\Eav\Model\Config                                          $eavConfig,
        \Magento\Catalog\Api\CategoryRepositoryInterface                   $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\Collection           $categoryCollection,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory    $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection $attributeCollection,
        \Magento\Catalog\Api\CategoryListInterface                         $categoryList,
        \Magento\Catalog\Model\CategoryFactory                             $categoryFactory,
        \Magento\Framework\Api\SearchCriteriaInterface                     $searchCriteria,
        JoinProcessorInterface                                             $extensionAttributesJoinProcessor,
        array                                                              $data = []
    )
    {
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
        $this->categoryList = $categoryList;
        $this->categoryCollection = $categoryCollection;
        $this->categoryFactory = $categoryFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->attributeCollection = $attributeCollection;
        $this->searchCriteria = $searchCriteria;
        $this->lastCategoryId = (int)$categoryCollection->getLastItem()->getId();
    }

    public function getEntityTypeCode()
    {
        return 'catalog_category';
    }

    public function validateRow(array $rowData, $rowNumber)
    {
        return true;
    }

    protected function _importData()
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntities();
                break;
            case Import::BEHAVIOR_ADD_UPDATE:
                $this->addAndUpdateEntities();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->replaceAndAddEntities();
                break;
        }
        $this->indexerProcessor->markIndexerAsInvalid();
        return true;
    }


    private function addAndUpdateEntities()
    {
        $dividedEntities = $this->divideEntitiesToUpdatedAndCreated();
        $entitiesToCreate = $dividedEntities['entities_to_create'];
        $entitiesToUpdate = $dividedEntities['entities_to_update'];
        $allEntities = array_merge($entitiesToCreate, $entitiesToUpdate);

        //

        // Code below sets new IDs and updates the hierarchy
        /** @var \Magento\Catalog\Model\Category $newEntity */
        foreach ($entitiesToCreate as $newEntity) {
            $explodedPath = explode('/', $newEntity->getData('path'));
            $originalEntityId = $explodedPath[$newEntity->getData('level')];
            $newEntityId = $explodedPath[$newEntity->getData('level')] = (int)$explodedPath[$newEntity->getData('level')] + $this->lastCategoryId;
            $newEntity->setData('entity_id', $newEntityId);
            $implodedPath = implode('/', $explodedPath);
            $newEntity->setData('path', $implodedPath);
            //set parent Id to newEntity if parent exists
            if (count($explodedPath) > 1) {
                $newEntityParentId = (int)$explodedPath[($newEntity->getData('level') - 1)];
                $newEntity->setParentId($newEntityParentId);
            } else {
                //root category
                $newEntity->setParentId(0);
            }

            foreach ($allEntities as $changePathEntity) {
                if ($changePathEntity->getData('path') === $implodedPath) {
                    continue;
                }
                $explodedChangedPath = explode('/', $changePathEntity->getData('path') ?? "");
                if (isset($explodedChangedPath[$newEntity->getData('level')])) {
                    //check if $changePathEntity is under hierarchy of $newEntity
                    if (((int)($explodedChangedPath[$newEntity->getData('level')])) === ((int)$originalEntityId)) {
                        $explodedChangedPath[$newEntity->getData('level')] = $newEntityId;
                        //check if $changePathEntity is child of $newEntity
                        if (((int)$changePathEntity->getData('level') - (int)$newEntity->getData('level')) === 1) {
                            $changePathEntity->setParentId($newEntityId);
                        }

                        $implodedChangedPath = implode('/', $explodedChangedPath);
                        $changePathEntity->setData('path', $implodedChangedPath);
                    }
                }
            }
        }


        $I = $entitiesToCreate;
        // get last ID of entities
        // add new pathes , id, and parent ids to new entity
        //save to db
    }

    protected function divideEntitiesToUpdatedAndCreated()
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        $entityCollection = $this->categoryList->getList($this->searchCriteria)->getItems();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                $updateFlag = false;
                foreach ($entityCollection as $entity) {
                    $entityName = $entity->getData('name');
                    if (!$this->validateRow($rowData, $rowNumber)) {
                        continue;
                    }
                    if ($this->getErrorAggregator()->hasToBeTerminated()) {
                        $this->getErrorAggregator()->addRowToSkip($rowNumber);
                        continue;
                    }

                    if ($entityName === $rowData['name']) {
                        // todo validate if exist in array such fields
                        $path = $rowData['path'];
                        unset($rowData['children_count'], $rowData['path'], $rowData['name']);
                        $entity->setData($rowData);
                        if ($entity->getPath() !== $path) {
                            $entity->setPath($path);
                            $explodedPath = explode('/', $path ?? '');
                            if (is_array($explodedPath) && (count($explodedPath) > 1)) {
                                $newEntityParentId = (int)$explodedPath[($rowData['level'] - 1)];
                                $entity->setParentId($newEntityParentId);
                            } else {
                                //root category
                                $entity->setParentId(0);
                            }
                        }
                        $entitiesToUpdate[] = $entity;
                        $updateFlag = true;
                        break;
                    }
                }
                if (!$updateFlag) {
                    /** @var \Magento\Catalog\Model\Category $newEntity */
                    $newEntity = $this->categoryFactory->create();
                    //todo make validation on fiels wich are being set
                    unset($rowData['children_count']);
                    $newEntity->setData($rowData);
                    $entitiesToCreate[] = $newEntity;
                }
            }
        }

        return ['entities_to_create' => $entitiesToCreate, 'entities_to_update' => $entitiesToUpdate];
    }

    private function replaceAndAddEntities()
    {
        return true;
    }


    private function deleteEntities()
    {
        return true;
    }
}
