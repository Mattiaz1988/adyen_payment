<?php

class Adyen_Payment_Model_Sales_Order extends Mage_Sales_Model_Order
{
    /**
     * Whether specified state can be set from outside
     * @param $state
     * @return bool
     */
    public function isStateProtected($state)
    {
        return false;
    }

   
}
