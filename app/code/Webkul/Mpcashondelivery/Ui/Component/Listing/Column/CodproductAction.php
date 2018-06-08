<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Ui\Component\Listing\Column;
 
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\Mpcashondelivery\Helper\Data;
 
class CodproductAction extends Column
{
    /**
     * $_mpcodHelper
     * @var Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Data               $mpcodHelper
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $mpcodHelper,
        array $components = [],
        array $data = []
    ) {
        $this->_mpcodHelper = $mpcodHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $productId = $item['mageproduct_id'];
                $product = $this->_mpcodHelper->getProduct($productId);
                $type = $product->getTypeId();
                $typeArray = ['downloadable','grouped','virtual'];
                $codStatus = $product->getCodAvailable();
                if ($codStatus==1) {
                    $item[$this->getData('name')] = __('Enable');
                } elseif (in_array($type, $typeArray)) {
                    $item[$this->getData('name')] = __('N/A');
                } else {
                    $item[$this->getData('name')] = __('Disable');
                }
            }
        }
        return $dataSource;
    }
}
