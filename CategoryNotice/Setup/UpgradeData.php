<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\CategoryNotice\Setup;


use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Attribute\Backend\Startdate;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Category;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface {

	public function __construct(CategorySetupFactory $categorySetupFactory) {
	     $this->categorySetupFactory = $categorySetupFactory;
	}

	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
		$setup->startSetup();
		if (version_compare($context->getVersion(), '1.0.1') < 0) {
			$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
			$data = [ 'required' => false ];
			$entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
			$attribute = $categorySetup->getAttribute($entityTypeId, 'notice_description');

			if (isset($attribute['attribute_id'])) {
				$categorySetup->updateAttribute(
					$entityTypeId,
					$attribute['attribute_id'],
					'is_required',
					false
				);
			}
        }
        $setup->endSetup();
	}

}
