<?php

declare(strict_types=1);

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Import;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CatalogCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Customer\Model\Indexer\Processor;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
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
    public const CATEGORY_ATTRIBUTE_SET_ID = 3;
    public const ROOT_CATEGORY_LEVEL = 0;
    public const DEFAULT_CATEGORY_ID = 1;
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

    private CategoryRepositoryInterface $categoryRepository;
    private Collection $categoryCollection;
    private CategoryFactory $categoryFactory;
    private Processor $indexerProcessor;
    private StoreManagerInterface $storeManager;
    private CollectionFactory $categoryCollectionFactory;
    private RequestInterface $request;
    private int $reqestStoreId;
    private int $lastCategoryId;


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
        CategoryFactory                    $categoryFactory,
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
        $this->categoryCollection = $categoryCollection;
        $this->categoryFactory = $categoryFactory;
        $this->indexerProcessor = $indexerProcessor;
        $this->lastCategoryId = (int)$categoryCollection->getLastItem()->getId();
        $this->categoryResource = $categoryResource;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->request = $request;
        $this->reqestStoreId = (int)$this->request->getParam('store_id');

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

    public function getEntityTypeCode(): string
    {
        return 'catalog_category';
    }

    public function validateData(): ProcessingErrorAggregatorInterface
    {
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
        $this->validateRequiredData();
        $this->validateUrlKeys($data);
        $this->validateCorrectPath($data);

        return $this->getErrorAggregator();
    }

    public function validateRow(array $rowData, $rowNumber): bool
    {
        return true;
    }

    protected function _importData(): bool
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

    private function addAndUpdateEntities(): bool
    {
        $dividedEntities = $this->getListsOfUpdatedAndCreatedEntities();
        $entitiesToCreate = $dividedEntities['entities_to_create'];
        $entitiesToUpdate = $dividedEntities['entities_to_update'];
        foreach ($entitiesToCreate as $entity) {
            if ((int)$entity->getLevel() === self::ROOT_CATEGORY_LEVEL) {
                continue;
            }
            $entityArray = $entity->toArray();
            $corectArrayEntity = [
                'entity_id' => $entityArray['entity_id'],
                'attribute_set_id' => self::CATEGORY_ATTRIBUTE_SET_ID,
                'parent_id' => $entityArray['parent_id'],
                'path' => $entityArray['path'],
                'position' => $entityArray['position'],
                'level' => $entityArray['level'],
            ];
            $this->categoryResource->getConnection()->insertOnDuplicate('catalog_category_entity', $corectArrayEntity);
            $currentStoreId = (int)$this->storeManager->getStore()->getId();
            $this->storeManager->setCurrentStore($this->reqestStoreId);
            $this->categoryRepository->save($entity);
            $this->storeManager->setCurrentStore($currentStoreId);
        }
        foreach ($entitiesToUpdate as $entity) {
            $this->storeManager->setCurrentStore($this->reqestStoreId);
            $this->categoryRepository->save($entity);
            $this->storeManager->setCurrentStore($currentStoreId);
        }

        return true;
    }

    protected function getListsOfUpdatedAndCreatedEntities(): array
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        $entityCollection = $this->getEntityCollectionForDistinctStoreId($this->reqestStoreId);
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                $updateFlag = false;
                foreach ($entityCollection as $entity) {
                    if (($rowData['name'] === 'Root Catalog') && ($rowData['level'] == self::ROOT_CATEGORY_LEVEL)) {
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
                    /** @var CatalogCategory $newEntity */
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

    private function updateEntityData($entity, $rowData): CatalogCategory
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
                $entity->setParentId(self::DEFAULT_CATEGORY_ID);
            }
        }

        return $entity;
    }

    private function setNewIdsAndUpdateHierarchy($entitiesToCreate, $entitiesToUpdate): void
    {
        //changing objects in $allEntities array, we also changed objects in $entitiesToCreate, $entitiesToUpdate arrays, because objects is given by link
        $allEntities = array_merge($entitiesToCreate, $entitiesToUpdate);
        // Code below sets new IDs and updates the hierarchy
        /** @var CatalogCategory $newEntity */
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
                $newEntity->setParentId(self::DEFAULT_CATEGORY_ID);
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

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InputException
     */
    private function deleteEntities(): bool
    {
        $entityCollection = $this->getEntityCollectionForDistinctStoreId($this->reqestStoreId);
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

    private function validateUrlKeys(array $data): void
    {
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
    }

    private function validateRequiredData(): void
    {
        foreach ($this->nessessaryColumns as $columnName) {
            if (!in_array($columnName, $this->getSource()->getColNames(), true)) {
                $this->addErrors(self::DATA_IS_REQUIRED, [$columnName]);
            }
        }
    }

    private function validateCorrectPath(array $data): void
    {
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
    }
}
