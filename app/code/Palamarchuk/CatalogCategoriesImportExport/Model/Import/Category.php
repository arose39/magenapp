<?php

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Import;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Customer\Model\Indexer\Processor;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\PredefinedId;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEav;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\Store\Model\StoreManagerInterface;

class Category extends AbstractEav
{
    public const XML_PATH_BUNCH_SIZE = 'import/format_v2/category_bunch_size';
    public const ERROR_PATH_LEVEL_VALUE = 'pathDoesNotCorrespondLevel';
    public const ERROR_PATH_VALUE = 'wrongPathValue';
    public const URL_KEY_VALUE_DOUBLED = 'urlKeyValueDoubled';
    public const DATA_IS_REQUIRED = "dataIsRequiered";
    protected array $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];
    protected $masterAttributeCode = 'name';
    protected CategoryResource $categoryResource;
    protected $nessessaryColumns = [
        'available_sort_by',
        'custom_apply_to_products',
        'custom_use_parent_settings',
        'default_sort_by',
        'description',
        'display_mode',
        'filter_price_range',
        'include_in_menu',
        'is_active',
        'is_anchor',
        'level',
        'name',
        'page_layout',
        'path',
        'url_key',
        'url_path'
    ];

    private CategoryListInterface $categoryList;
    private SearchCriteriaInterface $searchCriteria;
    private int $lastCategoryId;
    private CategoryRepositoryInterface $categoryRepository;
    private Collection $categoryCollection;
    private CategoryFactory $categoryFactory;
    private Processor $indexerProcessor;
    private StoreManagerInterface $storeManager;
    private CollectionFactory $categoryCollectionFactory;
    private RequestInterface $request;


    public function __construct(
        StringUtils                        $string,
        ScopeConfigInterface               $scopeConfig,
        ImportFactory                      $importFactory,
        Helper                             $resourceHelper,
        ResourceConnection                 $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        StoreManagerInterface              $storeManager,
        Factory                            $collectionFactory,
        Config                             $eavConfig,
        CategoryRepositoryInterface        $categoryRepository,
        Collection                         $categoryCollection,
        CategoryListInterface              $categoryList,
        CategoryFactory                    $categoryFactory,
        SearchCriteriaInterface            $searchCriteria,
        CategoryResource                   $categoryResource,
        RequestInterface    $request,
        CollectionFactory                  $categoryCollectionFactory,
        Processor                          $indexerProcessor,
        array                              $data = []
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
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->categoryList = $categoryList;
        $this->categoryCollection = $categoryCollection;
        $this->categoryFactory = $categoryFactory;
        $this->searchCriteria = $searchCriteria;
        $this->request = $request;
        $this->indexerProcessor = $indexerProcessor;
        $this->lastCategoryId = (int)$categoryCollection->getLastItem()->getId();
        $this->categoryResource = $categoryResource;
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        $this->addMessageTemplate(
            self::ERROR_PATH_LEVEL_VALUE,
            'Value for PATH attribute in row with category name \'%s\' contains incorrect path value nesting should correspond with level value'
        );
        $this->addMessageTemplate(
            self::ERROR_PATH_VALUE,
            'Value for PATH attribute in row with category name \'%s\'  incorrect. Some Ids from path not found nor in file not in existing categories.
            Search was provided by level of nesting'
        );
        $this->addMessageTemplate(
            self::URL_KEY_VALUE_DOUBLED,
            'Value for URL_KEY attribute should be unique, should appear in uploaded file once. Non unique url keys: \'%s\'.'
        );
        $this->addMessageTemplate(
            self::DATA_IS_REQUIRED,
            'For correct import requiered field \'%s\'.'
        );
    }

    use PredefinedId;

    public function getEntityTypeCode()
    {
        return 'catalog_category';
    }

    public function validateData()
    {
      $ss =   $this->storeManager->getStores();
//        $sss = $this->request->getParams();
        $s = $this->getEntityCollectionForDistinctStoreId(2);
//        $ss = $this->getEntityCollectionForDistinctStoreId(0);
//        $this->categoryCollectionFactory->create()->setStoreId(2)->addAttributeToSelect('*')->getItems();


        parent::validateData();
        if ($this->getErrorAggregator()->getErrorsCount()) {
            return $this->getErrorAggregator();
        }

        if ($this->getBehavior() === Import::BEHAVIOR_DELETE) {
            return $this->getErrorAggregator();
        }

        $data = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                $data[$rowNumber] = $rowData;
            }
        }

        foreach ($this->nessessaryColumns as $columnName) {
            if (!in_array($columnName, $this->getSource()->getColNames())) {
                $this->addErrors(self::DATA_IS_REQUIRED, [$columnName]);
            }
        }


        $dataUrlKeys = [];
        foreach ($data as $rowData) {
            $dataUrlKeys[] = $rowData['url_key'] ?? '';
        }
        $counts = array_count_values($dataUrlKeys);
        unset($counts['']);
        $repeatedUrlKeys = [];
        foreach ($counts as $key => $item) {
            if ($item > 1) {
                $repeatedUrlKeys[] = $key;
            }
        }
        $this->addErrors(self::URL_KEY_VALUE_DOUBLED, $repeatedUrlKeys);


        $newLeveledIds = [];
        foreach ($data as $rowNumber => $rowData) {
            $explodedPath = explode('/', $rowData['path']);
            if (isset($explodedPath[$rowData['level']]) && ($explodedPath[$rowData['level']] === end($explodedPath))) {
                $newLeveledIds[$rowData['level']][] = explode('/', $rowData['path'])[$rowData['level']];
            } else {
                $this->addErrors(self::ERROR_PATH_LEVEL_VALUE, [$rowData['name']]);
            }
        }
        $existingLeveledIds = [];
        foreach ($this->categoryCollection as $category) {
            $existingLeveledIds[$category->getLevel()][] = $category->getId();
        }
        foreach ($data as $rowNumber => $rowData) {
            $explodedPath = explode('/', $rowData['path']);
            $pathIsCorrectCounter = 0;
            foreach ($explodedPath as $level => $id) {
                $findInNewLeveledIds = false;
                if (isset($newLeveledIds[$level])) {
                    foreach ($newLeveledIds[$level] as $idFromList) {
                        if ($id === $idFromList) {
                            $pathIsCorrectCounter++;
                            $findInNewLeveledIds = true;
                            break;
                        }
                    }
                }
                if (!$findInNewLeveledIds && isset($existingLeveledIds[$level])) {
                    foreach ($existingLeveledIds[$level] as $idFromList) {
                        if ($id === $idFromList) {
                            $pathIsCorrectCounter++;
                            break;
                        }
                    }
                }
            }
            if ($pathIsCorrectCounter !== count($explodedPath)) {
                $this->addErrors(self::ERROR_PATH_VALUE, [$rowData['name']]);
            }
        }

        return $this->getErrorAggregator();
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
        }
        $this->indexerProcessor->markIndexerAsInvalid();

        return true;
    }


    private function addAndUpdateEntities()
    {
        $dividedEntities = $this->getListsOfUpdatedAndCreatedEntities();
        $entitiesToCreate = $dividedEntities['entities_to_create'];
        $entitiesToUpdate = $dividedEntities['entities_to_update'];


        foreach ($entitiesToCreate as $entity) {
            $entityArray = $entity->toArray();
            $coorectArrayEntity = [
                'entity_id' => $entityArray['entity_id'],
                'attribute_set_id' => 3,
                'parent_id' => $entityArray['parent_id'],
                'path' => $entityArray['path'],
                'position' => $entityArray['position'],
                'level' => $entityArray['level'],
            ];

            $this->categoryResource->getConnection()->insertOnDuplicate('catalog_category_entity', $coorectArrayEntity);
            $currentStoreId = (int)$this->storeManager->getStore()->getId();

            if ($entity->getLevel() == 1) {
                $this->storeManager->setCurrentStore(0);
                $this->categoryRepository->save($entity);
                $this->storeManager->setCurrentStore($currentStoreId);
            }

            $this->storeManager->setCurrentStore(3);
            $this->categoryRepository->save($entity);

            $this->storeManager->setCurrentStore($currentStoreId);
        }
        foreach ($entitiesToUpdate as $entity) {
            $this->storeManager->setCurrentStore(0);
            $this->categoryRepository->save($entity);
            $this->storeManager->setCurrentStore($currentStoreId);
        }

        return true;
    }

    protected function getListsOfUpdatedAndCreatedEntities()
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];

        // todo make right collection
        $entityCollection = $this->categoryList->getList($this->searchCriteria)->getItems();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                $updateFlag = false;
                foreach ($entityCollection as $entity) {
                    if (($rowData['name'] === 'Root Catalog') && ($rowData['level'] == 0)) {
                        $updateFlag = true;
                        break;
                    }
                    if ($entity->getData('url_key') === $rowData['url_key']) {
                        $entitiesToUpdate[] = $this->updateEntityData($entity, $rowData);
                        $updateFlag = true;
                        break;
                    }
                }
                if (!$updateFlag) {
                    /** @var \Magento\Catalog\Model\Category $newEntity */
                    $newEntity = $this->categoryFactory->create();
                    unset($rowData['children_count']);
                    $newEntity->setData($rowData);
                    $entitiesToCreate[] = $newEntity;
                }
            }
        }
        $this->setNewIdsAndUpdateHierarchy($entitiesToCreate, $entitiesToUpdate);

        return ['entities_to_create' => $entitiesToCreate, 'entities_to_update' => $entitiesToUpdate];
    }

    private function updateEntityData($entity, $rowData)
    {
        $entityId = $entity->getData('entity_id');
        $entityPath = $entity->getData('path');
        $newPath = $rowData['path'];
        $isActive = ($rowData['is_active'] === "Yes") ? true : false;
        $includeInMenu = ($rowData['include_in_menu'] === "Yes") ? true : false;
        $isAnchor = ($rowData['is_anchor'] === "Yes") ? true : false;
        unset($rowData['is_active'], $rowData['include_in_menu'], $rowData['is_anchor']);
        $entity->setData($rowData);
        $entity->setId($entityId);
        $entity->setIsActive($isActive);
        $entity->setIncludeInMenu($includeInMenu);
        $entity->setIsAnchor($isAnchor);
        if ($entityPath !== $newPath) {
            $entity->setPath($newPath);

            $explodedPath = explode('/', $newPath ?? '');
            if (is_array($explodedPath) && (count($explodedPath) > 2)) {
                $newEntityParentId = (int)$explodedPath[($rowData['level'] - 1)];
                $entity->setParentId($newEntityParentId);
            } else {
                //default category
                $entity->setParentId(1);
            }
        }

        return $entity;
    }

    private function setNewIdsAndUpdateHierarchy($entitiesToCreate, $entitiesToUpdate)
    {
        //changing objects in $allEntities array, we also canged objects in $entitiesToCreate, $entitiesToUpdate arrys, becouse objects is given by link
        $allEntities = array_merge($entitiesToCreate, $entitiesToUpdate);
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
            if (count($explodedPath) > 2) {
                $newEntityParentId = (int)$explodedPath[($newEntity->getData('level') - 1)];
                $newEntity->setParentId($newEntityParentId);
            } else {
                //default category
                $newEntity->setParentId(1);
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
    }

    private function deleteEntities()
    {
        // todo make right collection
        $entityCollection = $this->categoryList->getList($this->searchCriteria)->getItems();


        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowData) {
                foreach ($entityCollection as $entity) {
                    if ($entity->getUrlKey() === $rowData['url_key']) {
                        if ($rowData['name'] === 'Default Category' || $rowData['name'] === 'Root Catalog') {
                            break;
                        }

                        $this->categoryRepository->delete($entity);
                    }
                }
            }
        }

        return true;
    }

    private function getEntityCollectionForDistinctStoreId($storeId)
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
