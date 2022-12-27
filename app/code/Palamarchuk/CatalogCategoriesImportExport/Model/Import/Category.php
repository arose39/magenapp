<?php

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Import;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
    protected array $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];
    protected $masterAttributeCode = 'name';
    protected CategoryResource $categoryResource;
    private CategoryListInterface $categoryList;
    private SearchCriteriaInterface $searchCriteria;
    private int $lastCategoryId;
    private CategoryRepositoryInterface $categoryRepository;
    private Collection $categoryCollection;
    private CategoryFactory $categoryFactory;


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
        $this->categoryRepository = $categoryRepository;
        $this->categoryList = $categoryList;
        $this->categoryCollection = $categoryCollection;
        $this->categoryFactory = $categoryFactory;
        $this->searchCriteria = $searchCriteria;
        $this->lastCategoryId = (int)$categoryCollection->getLastItem()->getId();
        $this->categoryResource = $categoryResource;

        $this->addMessageTemplate(
            self::ERROR_PATH_LEVEL_VALUE,
            'Value for PATH attribute in row with category name \'%s\' contains incorrect path value nesting should correspond with level value'
        );
        $this->addMessageTemplate(
            self::ERROR_PATH_VALUE,
            'Value for PATH attribute in row with category name \'%s\'  incorrect. Some Ids from path not found nor in file not in existing categories.
            Search was provided by level of nesting'
        );
    }

    use PredefinedId;

    public function getEntityTypeCode()
    {
        return 'catalog_category';
    }

    public function validateData()
    {
        parent::validateData();
        if ($this->getErrorAggregator()->getErrorsCount()) {
            return $this->getErrorAggregator();
        }
        $data = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                $data[$rowNumber] = $rowData;
            }
        }

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
                foreach ($newLeveledIds[$level] as $idFromList) {
                    if ($id === $idFromList) {
                        $pathIsCorrectCounter++;
                        $findInNewLeveledIds = true;
                        break;
                    }
                }
                if (!$findInNewLeveledIds){
                    foreach ($existingLeveledIds[$level] as $idFromList) {
                        if ($id === $idFromList) {
                            $pathIsCorrectCounter++;
                            break;
                        }
                    }
                }
            }
            if ($pathIsCorrectCounter !== count($explodedPath)){
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
//        $this->indexerProcessor->markIndexerAsInvalid();
        return true;
    }


    private function addAndUpdateEntities()
    {
        $dividedEntities = $this->getListsOfUpdatedAndCreatedEntities();
        $entitiesToCreate = $dividedEntities['entities_to_create'];
        $entitiesToUpdate = $dividedEntities['entities_to_update'];
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

        foreach ($entitiesToCreate as $entity) {
            $entityArray = $entity->toArray();
            $coorectArrayEntity = [
                'entity_id' => $entityArray['entity_id'],
                'attribute_set_id' => 3,
                'parent_id' => $entityArray['parent_id'],
                'path' => $entityArray['path'],
                'position' => $entityArray['position'],
                'level' => $entityArray['level']

            ];
            $this->categoryResource->getConnection()->insertOnDuplicate('catalog_category_entity', $coorectArrayEntity);

            $this->categoryRepository->save($entity);
        }
        foreach ($entitiesToUpdate as $entity) {
            $this->categoryRepository->save($entity);
        }

        return true;
    }

    protected function getListsOfUpdatedAndCreatedEntities()
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        $entityCollection = $this->categoryList->getList($this->searchCriteria)->getItems();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNumber => $rowData) {
                $updateFlag = false;
                foreach ($entityCollection as $entity) {
                    $entityName = $entity->getData('name');
                    $entityId = $entity->getData('entity_id');
                    $entityPath = $entity->getData('path');

//                    if (!$this->validateRow($rowData, $rowNumber)) {
//                        continue;
//                    }
//                    if ($this->getErrorAggregator()->hasToBeTerminated()) {
//                        $this->getErrorAggregator()->addRowToSkip($rowNumber);
//                        continue;
//                    }

                    if ($entityName === $rowData['name']) {
                        // todo validate if exist in array such fields
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


    private function deleteEntities()
    {
        return true;
    }
}
