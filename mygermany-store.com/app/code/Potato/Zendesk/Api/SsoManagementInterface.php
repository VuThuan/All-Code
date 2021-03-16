<?php

namespace Potato\Zendesk\Api;

interface SsoManagementInterface
{
    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return mixed
     */
    public function getLocationByCustomer($customer);
}
