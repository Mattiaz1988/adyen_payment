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
class Adyen_Payment_Helper_Process extends Mage_Payment_Helper_Data
{
	
    /**
     * @param $order
     * @return bool
     */
    public function _isAutoCapture($order,$paymentMethod = null)
    {
        $captureMode = trim($this->_getConfigData('capture_mode', 'adyen_abstract', $order->getStoreId()));
        $sepaFlow = trim($this->_getConfigData('flow', 'adyen_sepa', $order->getStoreId()));
        $_paymentCode = $this->_paymentMethodCode($order);
        $captureModeOpenInvoice = $this->_getConfigData('auto_capture_openinvoice', 'adyen_abstract', $order->getStoreId());
        $captureModePayPal = trim($this->_getConfigData('paypal_capture_mode', 'adyen_abstract', $order->getStoreId()));
        $paymentMethod = $paymentMethod?$paymentMethod:$order->getPayment()->getCcType();
        
        //check if it is a banktransfer. Banktransfer only a Authorize notification is send.
        $isBankTransfer = $this->_isBankTransfer($paymentMethod);

        // if you are using authcap the payment method is manual. There will be a capture send to indicate if payment is succesfull
        if($_paymentCode == "adyen_sepa" && $sepaFlow == "authcap") {
            return false;
        }

        // payment method ideal, cash adyen_boleto or adyen_pos has direct capture
        if (strcmp($paymentMethod, 'ideal') === 0 || strcmp($paymentMethod, 'c_cash' ) === 0 || $_paymentCode == "adyen_pos" || $isBankTransfer == true || ($_paymentCode == "adyen_sepa" && $sepaFlow != "authcap") || $_paymentCode == "adyen_boleto") {
            return true;
        }
        // if auto capture mode for openinvoice is turned on then use auto capture
        if ($captureModeOpenInvoice == true && (strcmp($paymentMethodd, 'openinvoice') === 0 || strcmp($paymentMethod, 'afterpay_default') === 0 || strcmp($paymentMethod, 'klarna') === 0)) {
            return true;
        }
        // if PayPal capture modues is different from the default use this one
        if(strcmp($paymentMethod, 'paypal' ) === 0 && $captureModePayPal != "") {
            if(strcmp($captureModePayPal, 'auto') === 0 ) {
                return true;
            } elseif(strcmp($captureModePayPal, 'manual') === 0 ) {
                return false;
            }
        }
        if (strcmp($captureMode, 'manual') === 0) {
            return false;
        }
        //online capture after delivery, use Magento backend to online invoice (if the option auto capture mode for openinvoice is not set)
        if (strcmp($paymentMethod, 'openinvoice') === 0 || strcmp($paymentMethod, 'afterpay_default') === 0 || strcmp($paymentMethod, 'klarna') === 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $order
     * @return mixed
     */
    public function _paymentMethodCode($order)
    {
        return $order->getPayment()->getMethod();
    }

    public function _getPaymentMethodType($order) {
        return $order->getPayment()->getPaymentMethodType();
    }

    /**
     * @param $paymentMethod
     * @return bool
     */
    public function _isBankTransfer($paymentMethod) {
        if(strlen($paymentMethod) >= 12 &&  substr($paymentMethod, 0, 12) == "bankTransfer") {
            $isBankTransfer = true;
        } else {
            $isBankTransfer = false;
        }
        return $isBankTransfer;
    }
    
    
    /**
     * @param $code
     * @param null $paymentMethodCode
     * @param null $storeId
     * @return mixed
     */
    public function _getConfigData($code, $paymentMethodCode = null, $storeId = null)
    {
        return Mage::helper('adyen')->getConfigData($code, $paymentMethodCode, $storeId);
    }

    /**
     * @return mixed
     */
    public function _getRequest()
    {
        return Mage::app()->getRequest();
    }
    
}
