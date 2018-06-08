<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Adminhtml\Featuredcategories;
    use Magento\Backend\App\Action;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    use Magento\Backend\Model\View\Result\ForwardFactory;

    class NewAction extends Action  {

        protected $_resultPage;
        protected $_resultPageFactory;
        protected $resultForwardFactory;

        public function __construct(
            Context $context,
            PageFactory $resultPageFactory,
            ForwardFactory $resultForwardFactory
        ) {
            parent::__construct($context);
            $this->_resultPageFactory   = $resultPageFactory;
            $this->resultForwardFactory = $resultForwardFactory;
        }

        public function execute()   {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward("edit");
            return $resultForward;
        }

    }