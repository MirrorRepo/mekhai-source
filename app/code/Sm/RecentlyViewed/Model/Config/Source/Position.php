<?php
/*------------------------------------------------------------------------
# SM Recently Viewed - Version 1.0.0
# Copyright (c) 2017 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\RecentlyViewed\Model\Config\Source;

class Position implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'right', 'label' => __('Right')],
			['value' => 'bottom', 'label' => __('Bottom')]
		];
	}
}