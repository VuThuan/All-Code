<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model\Config\Source;

class AmazonMarketplace implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray($store = null)
    {
        $optionArray = [
            'A2EUQ1WTGCTBG2' => 'Canada',
            'ATVPDKIKX0DER' => 'US',
            'A1AM78C64UM0Y8' => 'Mexico',
            'A1PA6795UKMFR9' => 'Germany',
            'A1RKKUPIHCS9HS' => 'Spain',
            'A13V1IB3VIYZZH' => 'France',
            'A21TJRUUN4KGV' => 'India',
            'APJ6JRA9NG5V4' => 'Italy',
            'A1F83G8C2ARO7P' => 'UK',
            'A1VC38T7YXB528' => 'Japan',
            'AAHKV2X7AFYLW' => 'China',
            'A39IBJ37TRP1C6' => 'Australia',
            'A2Q3Y263D00KWC' => 'Brazil'
        ];
        asort($optionArray);

        return $optionArray;
    }
    
    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
