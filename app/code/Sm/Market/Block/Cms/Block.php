<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sm\Market\Block\Cms;


/**
 * Cms block content block
 */
class Block extends \Magento\Cms\Block\Block
{
    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $blockId = $this->getBlockId();
        $html = '';
        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->_blockFactory->create();
            $block->setStoreId($storeId)->load($blockId);
            if ($block->isActive()) {
                $html = $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
				$html = $this->_usedLazyLoad($html);
            }
        }
		
		return $html;
    }
	
	private function _usedLazyLoad($html){
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper_config = $_objectManager->get('Sm\Market\Helper\Data');
		$useLazyload = $helper_config->getAdvanced('enable_ladyloading'); /*add config Lazyload*/
		if ($useLazyload && !empty($html)) {
			$storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
			$currentStore = $storeManager->getStore();
			$mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			$imageBlank = $mediaUrl.'lazyloading/blank.png';
			$dom = new \DomDocument('1.0', 'utf-8');
			libxml_use_internal_errors(true);
			$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
			$dom->loadHTML($html);
			libxml_use_internal_errors(false);
			$xpath = new \DOMXPath($dom); 
			$classname = 'lazyload-container';
			$nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
			if ($nodes->length){	
				for($i = 0; $i < $nodes->length ; $i++)	{
					$imgTag = $nodes->item($i)->getElementsByTagName('img');
					if ($imgTag->length){
						foreach ($imgTag as $img) {
							if (empty($img->getAttribute('data-src'))){
								$img->setAttribute('data-src', $img->getAttribute('src'));
								$img->setAttribute('src', $imageBlank);
								$img->setAttribute('class'," lazyload ");
							}
						}
						
					}
				}
				$html = mb_convert_encoding($dom->saveHTML(), 'UTF-8', 'HTML-ENTITIES');
			}
		}
		return $html;
	}
}
