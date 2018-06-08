<?php
/*------------------------------------------------------------------------
# SM Recently Viewed - Version 1.0.0
# Copyright (c) 2017 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
 
namespace Sm\RecentlyViewed\Controller\Viewed;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Clear extends \Magento\Framework\App\Action\Action {
	/** @var  \Magento\Framework\View\Result\Page */
	protected $resultPageFactory;
	protected $jsonEncoder;
	protected $_layout;
	protected $response;
	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 */
	public function __construct(
		Context $context, 
		PageFactory $resultPageFactory,
		\Magento\Framework\Json\EncoderInterface $jsonEncoder,
		\Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
		 \Magento\Framework\App\Response\Http $response)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->jsonEncoder = $jsonEncoder;
		$this->_layout = $layout;
		$this->response = $response;
		parent::__construct($context);
	}

	/**
	 * Blog Index, shows a list of recent blog posts.
	 *
	 * @return \Magento\Framework\View\Result\PageFactory
	 */
	public function execute()
	{
		$isAjax = $this->getRequest()->isAjax();
		$is_ajax_recently_viewed = (int)$this->getRequest()->getParam('is_ajax_recently_viewed');
		if ($isAjax && $is_ajax_recently_viewed ){
			$event_ids = (string)$this->getRequest()->getParam('event_ids');
			$storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$storeId = $storeManager->getStore()->getStoreId(); 
			$customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session');
			$customerVisitor = $this->_objectManager->get('\Magento\Customer\Model\Visitor');
			$subject_id = 1;
			if ($customerSession->isLoggedIn()) {
				$subject_id = $customerSession->getCustomerId();
			}  else {
				$subject_id = $customerVisitor->getId();
			}
			$resource = $this->_objectManager->get('\Magento\Framework\App\ResourceConnection');
			$connection  = $resource->getConnection();
			$table = $resource->getTableName('report_event');
			$result = [];
			$sql = $connection->delete($table,['event_type_id = 1 AND subject_id='. $subject_id.' AND store_id='.$storeId.'']);
			if ($sql){
				$result['success'] = true;
			}else{
				$result['success'] = false;
			}
			return $this->_jsonResponse($result);
		}
	}
	
	protected function _jsonResponse($result)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}