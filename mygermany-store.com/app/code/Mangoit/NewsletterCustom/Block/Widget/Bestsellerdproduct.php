<?php
namespace Mangoit\NewsletterCustom\Block\Widget;

class Bestsellerdproduct extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	protected $_template = 'widget/bestsellerdproduct.phtml';
	 
	/**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;
    const DEFAULT_IMAGE_WIDTH = 150;
    const DEFAULT_IMAGE_HEIGHT = 150;

    /**
     * Products Report Collection Factory
     *
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory
     */
    protected $_resourceFactory;

    /**
     * Image helper
     *
     * @var Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_resourceFactory = $resourceFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_imageHelper = $context->getImageHelper();
        $this->_cartHelper = $context->getCartHelper();
        parent::__construct($context, $data);
    }

 	/**
     * Image helper Object
     */
 	public function imageHelperObj(){
        return $this->_imageHelper;
    }

    /**
     * get featured product collection
     */
    public function getBestsellerProduct(){
        $limit = $this->getProductLimit();

		$resourceCollection = $this->_resourceFactory->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');

		// $resourceCollection->setPageSize($limit);
        $productIds = [];
        foreach ($resourceCollection as $value) {
            $productIds[] = $value->getProductId();
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter(
                'entity_id',
                [
                    'in' => $productIds
                ]
            )->setPageSize(
                $limit
            );

		return $collection;
    }
   
    /**
     * Get the configured limit of products
     * @return int
     */
    public function getProductLimit() {
		if($this->getData('productcount')==''){
			return self::DEFAULT_PRODUCTS_COUNT;
		}
        return $this->getData('productcount');
    }

    /**
     * Get the widht of product image
     * @return int
     */
    public function getProductimagewidth() {
		if($this->getData('imagewidth')==''){
			return self::DEFAULT_IMAGE_WIDTH;
		}
        return $this->getData('imagewidth');
    }

    /**
     * Get the height of product image
     * @return int
     */
    public function getProductimageheight() {
		if($this->getData('imageheight')==''){
			return self::DEFAULT_IMAGE_HEIGHT;
		}
        return $this->getData('imageheight');
    }

    /**
     * Get the add to cart url
     * @return string
     */
     public function getAddToCartUrl($product, $additional = [])
    {
			return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['zone'] = isset($arguments['zone'])
            ? $arguments['zone']
            : $renderZone;
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }
}
