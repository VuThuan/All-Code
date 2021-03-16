<?php
namespace Mangoit\Marketplace\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'dropship';

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    protected $mangoitHelper;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Mangoit\Marketplace\Helper\Data $mangoitHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory; 
        $this->mangoitHelper = $mangoitHelper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        // if (isset($request->debug()['limit_carrier'])) {
        //     if ($request->debug()['limit_carrier'] == 'warehouse') {
        //        $amount = 25;
        //     }
        // }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $methodParams = $this->_checkoutSession->getMISCheckout();
        if (!empty($methodParams)) {
            $explodedParams = explode('_', $methodParams);
            $methodTitle = $explodedParams[0].' ('.$explodedParams[2].') ';
            // $method->setMethodTitle($this->getConfigData('name'));
            $amount = preg_replace( '/[^0-9,"."]/', '', $explodedParams[1] );
            // echo $amount = preg_replace('/[^0-9]/', '', $amount);
            // die('died');
           //print_r($methodParams);
            $method->setMethodTitle($methodTitle);
            $method->setPrice($amount);
            $method->setCost($amount);
        } else {
            // $method->setMethodTitle($this->getConfigData('name'));
            $method->setMethodTitle($this->getConfigData('name'));
            $amount = $this->getConfigData('price');
            $method->setPrice($amount);
            $method->setCost($amount);
        }
        // $method->setMethodTitle($this->getConfigData('name'));
        
        // $amount = $this->getConfigData('price');
        // if (isset($request->debug()['limit_carrier'])) {
        //     if ($request->debug()['limit_carrier'] == 'dropship') {
        //     }
        // }
        //$amount = $this->mangoitHelper->getDropshipCharge();

        $result->append($method);
        //$this->_checkoutSession->unsMISCheckout();

        return $result;
    }
}