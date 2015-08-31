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
class Adyen_Payment_Model_Event extends Mage_Core_Model_Abstract {

    const ADYEN_EVENT_AUTHORISATION = 'AUTHORISATION';
    const ADYEN_EVENT_PENDING = 'PENDING';
    const ADYEN_EVENT_AUTHORISED = 'AUTHORISED';
    const ADYEN_EVENT_CANCELLED = 'CANCELLED';
    const ADYEN_EVENT_REFUSED = 'REFUSED';
    const ADYEN_EVENT_ERROR = 'ERROR';
    const ADYEN_EVENT_REFUND = 'REFUND';
    const ADYEN_EVENT_REFUND_FAILED = 'REFUND_FAILED';
    const ADYEN_EVENT_CANCEL_OR_REFUND  = 'CANCEL_OR_REFUND';
    const ADYEN_EVENT_CAPTURE = 'CAPTURE';
    const ADYEN_EVENT_CAPTURE_FAILED = 'CAPTURE_FAILED';
    const ADYEN_EVENT_CANCELLATION = 'CANCELLATION';
    const ADYEN_EVENT_POSAPPROVED = 'POS_APPROVED';
    const ADYEN_EVENT_HANDLED_EXTERNALLY  = 'HANDLED_EXTERNALLY';
    const ADYEN_EVENT_MANUAL_REVIEW_ACCEPT = 'MANUAL_REVIEW_ACCEPT';
    const ADYEN_EVENT_MANUAL_REVIEW_REJECT = 'MANUAL_REVIEW_REJECT ';
    const ADYEN_EVENT_RECURRING_CONTRACT = "RECURRING_CONTRACT";
    const ADYEN_EVENT_REPORT_AVAILABLE = "REPORT_AVAILABLE";
    const ADYEN_EVENT_ORDER_CLOSED = "ORDER_CLOSED";
    const ADYEN_EVENT_NOTIFICATION_OF_FRAUD = 'NOTIFICATION_OF_FRAUD';
    const ADYEN_EVENT_NOTIFICATION_OF_CHARGEBACK = 'NOTIFICATION_OF_CHARGEBACK';
    const ADYEN_EVENT_CHARGEBACK = 'CHARGEBACK';
    
    protected $_order;

    /**
     * Initialize resources
     */
    protected function _construct() {
        $this->_init('adyen/adyen_event');
    }

    /**
     * Check if the Adyen Notification is already stored in the system
     * @param type $dbPspReference
     * @param type $dbEventCode
     * @return boolean true if the event is a duplicate
     */
    public function isDuplicate($pspReference, $event, $success) {
        $success = (trim($success) == "true") ? true : false;
        $result = $this->getResource()->getEvent(trim($pspReference), trim($event), $success);
        return (empty($result)) ? false : true;
    }

    public function getEvent($pspReference, $event) {
        return $this->getResource()->getEvent($pspReference, $event);
    }

    public function saveData() {
        $this->getResource()->saveData($this);
    }

    public function getOriginalPspReference($incrementId) {
        $originalReference = $this->getResource()->getOriginalPspReference($incrementId);
        return (!empty($originalReference)) ? $originalReference['psp_reference'] : false;
    }
    
    /**
     * Retrieve order instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            $this->setOrder();
        }

        return $this->_order;
    }

    /**
     * Set order instance for transaction depends on transaction behavior
     * If $order equals to true, method isn't loading new order instance.
     *
     * @param Mage_Sales_Model_Order|null|boolean $order
     * @return Adyen_Payment_Model_Event
     */
    public function setOrder($order = null)
    {
        if (null === $order || $order === true) {
            if ($this->getOrderId() && $order === null) {
                $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
            } else {
                $this->_order = false;
            }
        } elseif (!$this->getId() || ($this->getOrderId() == $order->getId())) {
            $this->_order = $order;
        } else {
            Mage::throwException(Mage::helper('sales')->__('Set order for existing transactions not allowed'));
        }

        return $this;
    }
    
    public function loadByOrderId($orderId) {
        $this->load($orderId, 'order_id');
        return $this;
    }

    public function loadByQuoteId($orderId) {
        $this->load($orderId, 'quote_id');
        return $this;
    }

    public function getFraudData($orderId) {

        $event = $this->loadByOrderId($orderId);
        $fraudDataObj = json_decode(utf8_decode($event['adyen_event_response']));
        $fraudData = @$fraudDataObj->fraudResult;
        return $fraudData;
    }

}