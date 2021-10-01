<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\CategoryNotice\Setup;


use Magento\Framework\Setup\InstallDataInterface;
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
class InstallData implements InstallDataInterface {



    public function __construct(CategorySetupFactory $categorySetupFactory) {
         $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        
        $setup->startSetup();


            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);

            $notice_attributes = [
                'notice_restock_date_status' => [
                    'type' => 'int',
                    'label' => 'Restock Date Enable',
                    'input' => 'select',
                    'required' => false,
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'sort_order' => 10,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Notice Content',
                ],
                'notice_restock_date' => [
                    'type' => 'datetime',
                    'label' => 'Restock Date',
                    'input' => 'date',
                    'backend' => Startdate::class,
                    'required' => false,
                    'sort_order' => 20,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Notice Content',
                ],
                'notice_description' => [
                        'type' => 'text',
                        'label' => 'Description',
                        'input' => 'textarea',
                        'sort_order' => 30,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'searchable' => true,
                        'comparable' => true,
                        'wysiwyg_enabled' => true,
                        'is_html_allowed_on_front' => true,
                        'visible_in_advanced_search' => true,
                        'group' => 'Notice Content',
                ]
            ];

            foreach($notice_attributes as $item => $data) {
                $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
            }
            $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Notice Content');

            foreach($notice_attributes as $item => $data) {
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $idg,
                    $item,
                    $data['sort_order']
                );
            }

        $setup->endSetup();
    }

}