<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customoption
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customoption\Block\Options\Type;

class Text extends \Webkul\Customoption\Block\Options\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_Customoption::options/type/text.phtml';
     /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Webkul_Customoption::options/type/text.phtml');
    }
}
