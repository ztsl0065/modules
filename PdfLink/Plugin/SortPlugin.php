<?php

namespace Zehntech\PdfLink\Plugin;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\ObjectManager;

class SortPlugin
{

	public function __construct(
		\Magento\Backend\Model\UrlInterface $backendUrl,
		\Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages,
		\Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
		\Magento\Framework\View\Asset\Repository $assetRepo,
		\Magento\MediaStorage\Model\File\Storage\FileFactory $storageFileFactory,
		\Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
		\Magento\Framework\Filesystem\DriverInterface $file = null,
		\Psr\Log\LoggerInterface $logger = null
	){
		$this->_backendUrl = $backendUrl;
		$this->_cmsWysiwygImages = $cmsWysiwygImages;
		$this->_coreFileStorageDb = $coreFileStorageDb;
		$this->_assetRepo = $assetRepo;
		$this->_storageFileFactory = $storageFileFactory;
		$this->_storageDatabaseFactory = $storageDatabaseFactory;
		$this->logger = $logger ?: ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
		$this->file = $file ?: ObjectManager::getInstance()->get(\Magento\Framework\Filesystem\Driver\File::class);
	}

	public function afterGetFilesCollection(Storage $storage, $result, $path, $type = null){
		if ($this->_coreFileStorageDb->checkDbUsage()) {
		    $files = $this->_storageDatabaseFactory->create()->getDirectoryFiles($path);

		    /** @var \Magento\MediaStorage\Model\File\Storage\File $fileStorageModel */
		    $fileStorageModel = $this->_storageFileFactory->create();
		    foreach ($files as $file) {
		        $fileStorageModel->saveFile($file);
		    }
		}

		$collection = $storage->getCollection(
		    $path
		)->setCollectDirs(
		    false
		)->setCollectFiles(
		    true
		)->setCollectRecursively(
		    false
		)->setOrder(
		    'basename',
		    \Magento\Framework\Data\Collection::SORT_ORDER_ASC
		);
		
		// Add files extension filter
		if ($allowed = $storage->getAllowedExtensions($type)) {
		    $collection->setFilesFilter('/\.(' . implode('|', $allowed) . ')$/i');
		}

		// prepare items
		foreach ($collection as $item) {
		    $item->setId($this->_cmsWysiwygImages->idEncode($item->getBasename()));
		    $item->setName($item->getBasename());
		    $item->setShortName($this->_cmsWysiwygImages->getShortFilename($item->getBasename()));
		    $item->setUrl($this->_cmsWysiwygImages->getCurrentUrl() . $item->getBasename());
		    $itemStats = $this->file->stat($item->getFilename());
		    $item->setSize($itemStats['size']);
		    $item->setMimeType(\mime_content_type($item->getFilename()));

		    if ($storage->isImage($item->getBasename())) {
		        $thumbUrl = $storage->getThumbnailUrl($item->getFilename(), true);
		        // generate thumbnail "on the fly" if it does not exists
		        if (!$thumbUrl) {
		            $thumbUrl = $this->_backendUrl->getUrl('cms/*/thumbnail', ['file' => $item->getId()]);
		        }

		        try {
		            $size = getimagesize($item->getFilename());

		            if (is_array($size)) {
		                $item->setWidth($size[0]);
		                $item->setHeight($size[1]);
		            }
		        } catch (\Error $e) {
		            $this->logger->notice(sprintf("GetImageSize caused error: %s", $e->getMessage()));
		        }
		    } else {
		        $thumbUrl = $this->_assetRepo->getUrl(self::THUMB_PLACEHOLDER_PATH_SUFFIX);
		    }

		    $item->setThumbUrl($thumbUrl);
		}

		return $collection;
	}
}