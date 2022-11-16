<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ImageUploader
{
    private Database $coreFileStorageDatabase;
    private Filesystem\Directory\WriteInterface $mediaDirectory;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;
    private string $baseTmpPath;
    private string $basePath;
    private array $allowedExtensions;
    private UploaderFactory $uploaderFactory;
    private ManagerInterface $messageManager;

    /**
     * @throws FileSystemException
     */
    public function __construct(
        Database              $coreFileStorageDatabase,
        Filesystem            $filesystem,
        UploaderFactory       $uploaderFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface       $logger,
        ManagerInterface      $messageManager,
        string                $baseTmpPath = 'store_locator/tmp/store_images',
        string                $basePath = 'store_locator/store_images',
        array                 $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png']
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->baseTmpPath = $baseTmpPath;
        $this->basePath = $basePath;
        $this->allowedExtensions = $allowedExtensions;
        $this->messageManager = $messageManager;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function saveImageToTmpDir(string $fileId): bool|array
    {
        return $this->saveImage($fileId, $this->baseTmpPath);
    }

    public function moveImageFromTmp(string $imageName): string
    {
        $baseTmpPath = $this->baseTmpPath;
        $basePath = $this->basePath;
        $baseImagePath = $this->getFilePath($basePath, $imageName);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $imageName);
        try {
            if ($this->isExist($imageName, $this->baseTmpPath)) {
                $this->coreFileStorageDatabase->copyFile(
                    $baseTmpImagePath,
                    $baseImagePath
                );
                $this->mediaDirectory->renameFile(
                    $baseTmpImagePath,
                    $baseImagePath
                );
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $imageName;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function saveImage(string $fileId, ?string $path = null): bool|array
    {
        if (!$path) {
            $path = $this->basePath;
        }
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->allowedExtensions);
        $newFileName = date('d-M-y_H-i-s') . '.' . $uploader->getFileExtension();
        $result = $uploader->save($this->mediaDirectory->getAbsolutePath($path), $newFileName);
        unset($result['path']);
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }
        $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
        $result['url'] = $this->storeManager
                ->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $this->getFilePath($path, $result['file']);
        $result['name'] = $result['file'];
        if (isset($result['file'])) {
            try {
                $relativePath = rtrim($path, '/') . '/' . ltrim($result['file'], '/');
                $this->coreFileStorageDatabase->saveFile($relativePath);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while saving the file(s).')
                );
            }
        }

        return $result;
    }

    public function deleteImage(string $fileName): bool
    {
        if (!$fileName) {
            return true;
        }
        $filePath = $this->basePath . '/' . ltrim($fileName, '/');
        if ($this->mediaDirectory->isExist($filePath)) {
            try {
                $this->mediaDirectory->delete($filePath);
            } catch (FileSystemException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());

                return false;
            }
        }

        return true;
    }

    public function deleteTmpImages(): bool
    {
        $files = $this->mediaDirectory->search("$this->baseTmpPath/*");
        foreach ($files as $file) {
            if ($this->mediaDirectory->isExist($file)) {
                try {
                    $this->mediaDirectory->delete($file);
                } catch (FileSystemException $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());

                    return false;
                }
            }
        }

        return true;
    }

    private function getFilePath(string $path, string $imageName): string
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }

    private function getStat(string $fileName): array
    {
        $filePath = $this->basePath . '/' . ltrim($fileName, '/');

        return $this->mediaDirectory->stat($filePath);
    }

    private function isExist(string $fileName, string|bool $baseTmpPath = false): bool
    {
        $filePath = $this->basePath . '/' . ltrim($fileName, '/');
        if ($baseTmpPath) {
            $filePath = $this->baseTmpPath . '/' . ltrim($fileName, '/');
        }

        return $this->mediaDirectory->isExist($filePath);
    }
}
