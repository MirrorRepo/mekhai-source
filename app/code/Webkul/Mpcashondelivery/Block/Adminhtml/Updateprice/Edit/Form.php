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

namespace Webkul\Mpcashondelivery\Block\Adminhtml\Updateprice\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country
     */
    protected $_country;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\ResourceModel\Country\Collection $country,
        array $data = []
    ) {
        $this->_country = $country;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Cash On Delivery Rates'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('cod_pricerates');
        $form = $this->_formFactory->create(
            ['data' =>
                ['id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post']
            ]
        );
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }
        $fieldset->addField(
            'dest_country_id',
            'select',
            [
                'label' => __('Destination Country'),
                'title' => __('Destination Country'),
                'name' => 'dest_country_id',
                'required' => true,
                'values' => $this->getCountryList()
            ]
        );
        $fieldset->addField(
            'dest_region_id',
            'text',
            ['name' => 'dest_region_id',
            'label' => __('Destination Region'),
            'required' => true,
            'placeholder'=>'Enter Destination State/Region-use * to allow all'
            ]
        );
        $fieldset->addField(
            'is_range',
            'select',
            [
                'label' => __('Zip Code Type'),
                'title' => __('Zip Code Type'),
                'name' => 'is_range',
                'required' => true,
                'options' => ['1' => __('Specific'), '0' => __('Is Range')]
            ]
        );
        $fieldset->addField(
            'dest_zip_from',
            'text',
            ['name' => 'dest_zip_from',
            'label' => __('Destination Zip From'),
            'required' => true,
            'placeholder'=>'Enter Zip/Post Code Range(low)-use * to allow all'
            ]
        );
        $fieldset->addField(
            'dest_zip_to',
            'text',
            ['name' => 'dest_zip_to',
            'label' => __('Destination Zip To'),
            'required' => true,
            'placeholder'=>'Enter Zip/Post Code Range(high)-use * to allow all'
            ]
        );
        $fieldset->addField(
            'zipcode',
            'text',
            ['name' => 'zipcode',
            'label' => __('Zip code'),
            'required' => true,
            'placeholder'=>'Enter Zip/Post Code'
            ]
        );
        $fieldset->addField(
            'price_type',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'price_type',
                'required' => true,
                'options' => ['1' => __('Percentage'), '0' => __('Fixed')]
            ]
        );
        $fieldset->addField(
            'fixed_price',
            'text',
            ['name' => 'fixed_price',
            'label' => __('Fixed Price'),
            'required' => true,
            'placeholder'=>'Enter Fixed Rate'
            ]
        );
        $fieldset->addField(
            'percentage_price',
            'text',
            ['name' => 'percentage_price',
            'label' => __('Percentage Price'),
            'required' => true,
            'placeholder'=>'Enter Percentage(%) Rate'
            ]
        );
        $fieldset->addField(
            'weight_from',
            'text',
            ['name' => 'weight_from',
            'label' => __('Weight From'),
            'required' => true,
            'placeholder'=>'Enter Weight Range(low)'
            ]
        );
        $fieldset->addField(
            'weight_to',
            'text',
            ['name' => 'weight_to',
            'label' => __('Weight To'),
            'required' => true,
            'placeholder'=>'Enter Weight Range(high)'
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    // country list in array format
    public function getCountryList()
    {
        $countryOption = '';
        $countries = $this->_country->loadByStore()->toOptionArray();
        $countries[0]['label'] = 'Please select country';
        foreach ($countries as $key => $value) {
            $countryOption[$value['value']] = $value['label'];
        }
        return $countryOption;
    }
}
