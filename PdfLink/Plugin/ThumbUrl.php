<?php

namespace Zehntech\PdfLink\Plugin;

use Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content\Files;

class ThumbUrl
{
	public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)  {
		$this->storeManager = $storeManager->getStore();
	}

	public function afterGetFileThumbUrl(Files $fileObj, $result, \Magento\Framework\DataObject $file){
		if($file->getData('mime_type')=='application/pdf') {
			return $this->storeManager->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'/wysiwyg/pdf-icon.png';
		}
		return $result;
	}	
}