<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api;

use Magento\Catalog\Model\Product;
use Plenty\Core\Api\ProfileEntityManagementInterface;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Core\Api\Data\Profile\HistoryInterface;

/**
 * Interface ProductVariationExportManagementInterface
 * @package Plenty\Item\Api
 */
interface ProductVariationExportManagementInterface extends ProfileEntityManagementInterface
{
    /**
     * @return ProductExportInterface
     */
    public function getProfileEntity() : ProductExportInterface;

    /**
     * @param ProductExportInterface $profileEntity
     * @return $this
     */
    public function setProfileEntity(ProductExportInterface $profileEntity);

    /**
     * @return HistoryInterface
     */
    public function getProfileHistory() : HistoryInterface;

    /**
     * @param HistoryInterface $history
     * @return $this
     */
    public function setProfileHistory(HistoryInterface $history);

    /**
     * @param Product $product
     * @return $this
     */
    public function execute(Product $product);
}