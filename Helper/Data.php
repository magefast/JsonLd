<?php

namespace Dragonfly\JsonLd\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @param $string
     * @return string
     */
    public function cleanString($string): string
    {
        if (!empty($string)) {
            return strip_tags(addcslashes($string, '"\\/'));
        } else {
            return '';
        }
    }
}
