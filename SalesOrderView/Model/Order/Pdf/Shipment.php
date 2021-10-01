<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\SalesOrderView\Model\Order\Pdf;


use Magento\Sales\Model\Order\Pdf\Config;
use Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * Sales Order PDF abstract model
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{

    public $_pricingHelper;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = [],
        Database $fileStorageDatabase = null
    )
    {
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $localeResolver,
            $data
        );
        $this->_pricingHelper = $pricingHelper;
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(Database::class);
    }

    /**
     * Draw table header for product items
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
       // $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 20);
        $this->y -= 15;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));


        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 110, 'font_size' => 11, 'font' => 'bold'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 30, 'font_size' => 11, 'font' => 'bold'];

        $lines[0][] = ['text' => __('Unit Price'), 'feed' => 325, 'font_size' => 11, 'font' => 'bold'];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 90, 'align' => 'right', 'font_size' => 11, 'font' => 'bold'];

        $lines[0][] = ['text' => __('Pkg Weight'), 'feed' => 385, 'font_size' => 11, 'font' => 'bold'];

        $lines[0][] = ['text' => __('Total Amount'), 'feed' => 500, 'font_size' => 11, 'font' => 'bold'];

        $lines[0][] = ['text' => __('Weight'), 'feed' => 450, 'font_size' => 11, 'font' => 'bold'];


        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param \Magento\Sales\Model\Order\Shipment[] $shipments
     * @return \Zend_Pdf
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($shipments as $shipment) {

            if ($shipment->getStoreId()) {
                $this->_localeResolver->emulate($shipment->getStoreId());
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }

            $page = $this->newPage();
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());

            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );

            // add comment
            if($order->getExtensionAttributes()->getUmOrderComment()) {
                
                $comment = $order->getExtensionAttributes()->getUmOrderComment();
                // $commentLines[0] = substr($comment, 0, 108);
                // if(strlen($comment) > 109){
                //     $restLines = str_split(substr($comment, 108), 128); 
                // $commentLines = array_merge($commentLines,$restLines);
                // }
                // $this->y += 15;
                // $xpoint = $this->y - 15;
                // $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1)); // background color order nu /date
                // $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
                // $_height = count($commentLines) > 2 ? 50 : 40; 
                // $page->drawRectangle(25, $this->y, 570, $this->y-$_height);
                // $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                // $this->_setFontBold($page, 11);
                // $page->drawText(__('Order Comment :'), 35, $xpoint, 'UTF-8');
                // $this->_setFontRegular($page, 10);
               
                // $space = 130;
                // foreach ($commentLines as $key => $line) {
                //     $page->drawText($line, $space, $xpoint, 'UTF-8');
                //     $xpoint -= 15;
                //     $space = 35;
                // }
                // $this->y -= $_height+15;

                $initialPoint = 35;
                $startPoint = $this->y + 15;
                $this->_setFontBold($page, 11);
                $page->drawText(__('Order Comment :'), $initialPoint, $this->y, 'UTF-8');
                $this->_setFontRegular($page, 10);
                $textChunk = wordwrap($comment, 100, "\n");
                $pos = strpos($textChunk, "\n"); 
                $firstChunk = substr($textChunk, 0, $pos) . "\n";
                $secondChunk = substr($comment, $pos);
                $secondChunk = wordwrap($secondChunk, 120, "\n");
                $textChunk = $firstChunk . $secondChunk;

                $initialPoint += 90;
                foreach(explode("\n", $textChunk) as $textLine){
                  if ($textLine!=='') {
                    $page->drawText(strip_tags(ltrim($textLine)), $initialPoint, $this->y, 'UTF-8');
                    $this->y -=14;
                    $initialPoint = 35;
                  }
                }
                $page->drawLine(25, $startPoint, 25, $this->y);                
                $page->drawLine(570, $startPoint, 570, $this->y);     
                $page->drawLine(25, $startPoint, 570, $startPoint);     
                $page->drawLine(25, $this->y, 570, $this->y);     
                $this->y -= 15;
            }
            // add comment

            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }

            if ($shipment->getStoreId()) {
                $this->_localeResolver->revert();
            }

            $this->insertTotalsInfo($page, $order);

        }
        $this->_afterGetPdf();
        return $pdf;
    }


    protected function insertTotalsInfo(\Zend_Pdf_Page $page, $order)
    {
        $this->y = $this->y ? $this->y-15 : 815;
        $top = $this->y;

        //$page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.48));//old
        //$page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));

        //$page->setLineColor(new \Zend_Pdf_Color_GrayScale(0)); //old
        //$page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));//new
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));//new

        $page->setLineWidth(0.5);

        $page->drawRectangle(25, $top, 570, $top - 105, 1);
        //$page->setFillColor(new \Zend_Pdf_Color_GrayScale(1)); //old
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        $lineBlock = ['lines' => [], 'height' => 15];

        /*draw qty*/
        $lineBlock['lines'][0] = [
            [
                'text' => 'Total Qty',
                'feed' => 30,
                // 'align' => 'right',
                'font_size' => 12,
                'font' => 'normal',
            ],
            [
                'text' => number_format($order->getTotalQtyOrdered(), 2),
                'feed' => 90,
                // 'align' => 'right',
                'font_size' => 12,
                'font' => 'normal'
            ],
        ];

        $lineBlock['lines'][1] = [
            [
                'text' => 'Total Weight',
                'feed' => 475,
                'align' => 'right',
                'font_size' => 12,
                'font' => 'normal',
            ],
            [
                'text' => number_format($order->getWeight(), 2),
                'feed' => 565,
                'align' => 'right',
                'font_size' => 12,
                'font' => 'normal'
            ],
        ];

        $lineBlock['lines'][2] = [
            [
                'text' => 'Subtotal',
                'feed' => 475,
                'align' => 'right',
                'font_size' => 12,
                'font' => 'normal',
            ],
            [
                'text' => $this->_pricingHelper->currency($order->getSubtotal(), true, false),
                'feed' => 565,
                'align' => 'right',
                'font_size' => 12,
                'font' => 'normal'
            ],
        ];


        if ($order->getDiscountAmount() != 0):

            $lineBlock['lines'][3] = [
                [
                    'text' => 'Discount ( ' . $order->getDiscountDescription() . ' ) ',
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal',
                ],
                [
                    'text' => $this->_pricingHelper->currency($order->getDiscountAmount(), true, false),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal'
                ],
            ];

        endif;
        if ($order->getCustomdiscount() != 0):
            $lineBlock['lines'][4] = [
                [
                    'text' => 'Green Discount',
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal',
                ],
                [
                    'text' => '-' . $this->_pricingHelper->currency($order->getCustomdiscount(), true, false),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal'
                ],
            ];
        endif;

        if ($order->getShippingAmount() != 0):

            $lineBlock['lines'][5] = [
                [
                    'text' => $order->getShippingDescription(),
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal',
                ],
                [
                    'text' => $order->getShippingAmount(),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal'
                ],
            ];

        endif;

        if ($order->getFee() != 0):
            $lineBlock['lines'][6] = [
                [
                    'text' => 'Small order handling',
                    'feed' => 475,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal',
                ],
                [
                    'text' => $this->_pricingHelper->currency($order->getFee(), true, false),
                    'feed' => 565,
                    'align' => 'right',
                    'font_size' => 12,
                    'font' => 'normal'
                ],
            ];
        endif;

        $lineBlock['lines'][7] = [
            [
                'text' => 'Grand Total',
                'feed' => 475,
                'align' => 'right',
                'font_size' => 12,
                'font' => 'bold',
            ],
            [
                'text' => $this->_pricingHelper->currency($order->getGrandTotal(), true, false),
                'feed' => 565,
                'align' => 'right',
                'font_size' => 12,
                'font' => 'bold'
            ],
        ];

        if ($order->getDiscountAmount() != 0){

            if($order->getSplitDiscount()) {

                $discountData = "[" . $order->getSplitDiscount() . "]";
                $discountData = json_decode($discountData, true);
                $discountArray = [];
                if(count($discountData)){
                    foreach ($discountData as $key => $discount) {
                        $discountArray[] = [
                            [
                                'text' => $discount['description'],
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => 12,
                                'font' => 'normal',
                            ],
                            [
                                'text' => $discount['amount'],
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => 12,
                                'font' => 'normal'
                            ]
                        ];
                    }
                    $totalsArray = $lineBlock['lines'];
                    $initialSlice = array_slice($totalsArray, 0, 3);
                    $lastSlice = array_slice($totalsArray, 4);
                    $totalsArray = count($lastSlice) ? array_merge($initialSlice, $discountArray, $lastSlice) : array_merge($initialSlice, $discountArray);
                    $lineBlock['lines'] = $totalsArray;
                }
            }
        }


        $startTable = $this->y -= 20;

        // if($top < 90 && count($lineBlock['lines'])<=5)
        // {
        //     $top = 815;
        //     $this->y = 805; 
        //     $page = $this->newPage();
        // }

        // if($top < 100 && count($lineBlock['lines'])>5)
        // {
        //     $top = 815;
        //     $page = $this->newPage();
        // }
        if($this->y < 100 && count($lineBlock['lines']) > 5) {
            $page = $this->newPage();
            $this->y = 750;
            $top = $this->y + 20;
        }

        $customTop = $top - (count($lineBlock['lines'])-1)*20;

        /*left*/
        $page->drawLine(25, $top, 25, $customTop);

        /*top*/
        $page->drawLine(25, $top, 570, $top);

        /*right*/
        $page->drawLine(570, $top, 570, $customTop);

        /*bottom*/
        $page->drawLine(570, $customTop, 25, $customTop);

        $page = $this->drawLineBlocks($page, [$lineBlock]);
    }

    /**
     * Insert order to pdf page.
     *
     * @param \Zend_Pdf_Page $page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $_linePoint = $order->getShippingMethod()=='freeshipping_freeshipping' ? 85 : 55;
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1)); // background color order nu /date
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 570, $top - $_linePoint);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - $_linePoint]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 15, 'UTF-8');
            $top += 15;
        }

        $top -= 30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top,
            'UTF-8'
        );

        // pickup at ranch
        if($order->getShippingMethod()=='freeshipping_freeshipping') {
            $this->_setFontBold($page, 11);
            $page->drawText(__("Pick up at Ranch"), 35, $top -= 15, 'UTF-8');
        }
        // pickup at ranch

        $top -= 10;
        //$page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1)); //'ship to' background color
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $phone = $order->getBillingAddress()->getTelephone();
        if($phone){
            $formatted_number = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $phone);
            $order->getBillingAddress()->setTelephone($formatted_number);
        }
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $order->getShippingAddress()->setTelephone('');
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );

            if ($order->getCustomerEmail()):
                array_push($shippingAddress, 'E-mail : ' . $order->getCustomerEmail());
            endif;

            if ($order->getShippingAddress()->getCustomerAddressType()):
                array_push($shippingAddress, 'Type : ' . $order->getShippingAddress()->getCustomerAddressType());
            endif;
            
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {

        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {

            if ($value !== '') {

                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        if ($order->getIsVirtual()) {

            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);
            $this->y = $yPayments - 15;

        } else {
            $this->y -= 15;
        }
    }


    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 750;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/sales/store/logo/' . $image;
            if ($this->fileStorageDatabase->checkDbUsage() &&
                !$this->_mediaDirectory->isFile($imagePath)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($imagePath);
            }
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $top = 790;
                //top border of the page
                $widthLimit = 270;
                //half of the page width
                $heightLimit = 270;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth()*7/10;
                $height = $image->getPixelHeight()*7/10;

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x2 = 570;
                $x1 = $x2 - $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 20;
            }
        }
    }



    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        $this->pageSettings = $pageSettings;
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;
            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        //
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                   
                }
                $itemsProp['shift'] = $shift;
            }

            // if ($this->y - $itemsProp['shift'] < 15) {
            //     $page = $this->newPage($pageSettings);
            // }
            $this->correctLines($lines, $page, $height);
        }

        return $page;
    }


    protected function correctLines($lines, $page, $height) :void
    {
        foreach ($lines as $line) {
            $maxHeight = 0;
            $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
            foreach ($line as $column) {
                $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                if ($this->y - $lineSpacing < 25) {
                    $page = $this->newPage();
                    $this->y = 750;
                    if($lines[0][0]['text']!='Total Qty')
                        $this->_drawHeader($page);
                }
                if (!empty($column['font_file'])) {
                    $font = \Zend_Pdf_Font::fontWithPath($column['font_file']);
                    $page->setFont($font, $fontSize);
                } else {
                    $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                    switch ($fontStyle) {
                        case 'bold':
                            $font = $this->_setFontBold($page, $fontSize);
                            break;
                        case 'italic':
                            $font = $this->_setFontItalic($page, $fontSize);
                            break;
                        default:
                            $font = $this->_setFontRegular($page, $fontSize);
                            break;
                    }
                }

                if (!is_array($column['text'])) {
                    $column['text'] = [$column['text']];
                }
                
                $top = $this->correctText($column, $height, $font, $page);
                $maxHeight = $top > $maxHeight ? $top : $maxHeight;
            }
            if($line[0]['text']!='Total Qty'){
                $this->y -= $maxHeight;
            }   
        }
    }

     
    protected function correctText($column, $height, $font, $page) :int
    {
        $top = 0;
        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
        $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
        foreach ($column['text'] as $part) {
            if ($this->y - $lineSpacing < 15) {
                $page = $this->newPage($this->pageSettings);
            }

            $feed = $column['feed'];
            $textAlign = empty($column['align']) ? 'left' : $column['align'];
            $width = empty($column['width']) ? 0 : $column['width'];
            switch ($textAlign) {
                case 'right':
                    if ($width) {
                        $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                    } else {
                        $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                    }
                    break;
                case 'center':
                    if ($width) {
                        $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                    }
                    break;
                default:
                    break;
            }
            $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
            $top += $lineSpacing;
        }
        return $top;
    }
}
