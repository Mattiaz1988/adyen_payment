<?php

/**
 * Adyen Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category	Adyen
 * @package	Adyen_Payment
 * @copyright	Copyright (c) 2011 Adyen (http://www.adyen.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @category   Payment Gateway
 * @package    Adyen_Payment
 * @author     Adyen
 * @property   Adyen B.V
 * @copyright  Copyright (c) 2014 Adyen BV (http://www.adyen.com)
 */
class Adyen_Payment_Model_Resource_Adyen_Event_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
   
   
    /**
     * Order ID filter
     *
     * @var int
     */
    protected $_orderId                = null;

    /**
     * Columns of order info that should be selected
     *
     * @var array
     */
    protected $_addOrderInformation    = array();
    
    /**
     * Columns of payment info that should be selected
     *
     * @var array
     */
    protected $_addPaymentInformation  = array();


    /**
     * Payment ID filter
     *
     * @var int
     */
    protected $_paymentId              = null;

    
    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField             = 'order_id';

    /**
     * Initialize collection items factory class
     */
    protected function _construct()
    {
        $this->_init('adyen/adyen_event');
        $this->setItemObjectClass('adyen/event');
        parent::_construct();
        
    }

    /**
     * Join order information
     *
     * @param array $keys
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    public function addOrderInformation(array $keys)
    {
        $this->_addOrderInformation = array_merge($this->_addOrderInformation, $keys);
        $this->addFilterToMap('created_at', 'main_table.created_at');
        return $this;
    }

    /**
     * Join payment information
     *
     * @param array $keys
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    public function addPaymentInformation(array $keys)
    {
        $this->_addPaymentInformation = array_merge($this->_addPaymentInformation, $keys);
        return $this;
    }

    /**
     * Order ID filter setter
     *
     * @param int $orderId
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    public function addOrderIdFilter($orderId)
    {
        $this->_orderId = (int)$orderId;
        return $this;
    }

    /**
     * Payment ID filter setter
     * Can take either the integer id or the payment instance
     *
     * @param Mage_Sales_Model_Order_Payment|int $payment
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    public function addPaymentIdFilter($payment)
    {
        $id = $payment;
        if (is_object($payment)) {
            $id = $payment->getId();
        }
        $this->_paymentId = (int)$id;
        return $this;
    }

  /**
     * Prepare filters
     *
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        if ($this->isLoaded()) {
            return $this;
        }

        // filters
        if ($this->_paymentId) {
            $this->getSelect()->where('main_table.payment_id = ?', $this->_paymentId);
        }
        if ($this->_orderId) {
            $this->getSelect()->where('main_table.order_id = ?', $this->_orderId);
        }
        if ($this->_addPaymentInformation) {
            $this->getSelect()->joinInner(
                array('sop' => $this->getTable('sales/order_payment')),
                'main_table.payment_id = sop.entity_id',
                $this->_addPaymentInformation
            );
        }
        if ($this->_addOrderInformation) {
            $this->getSelect()->joinInner(
                array('so' => $this->getTable('sales/order')),
                'main_table.order_id = so.entity_id',
                $this->_addOrderInformation
            );
        }
        return $this;
    }

    /**
     * Unserialize additional_information in each item
     *
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoad();
    }
}
