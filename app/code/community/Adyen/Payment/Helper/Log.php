<?php

/**
 * @category Adyen
 * @package Adyen_Core_Helper
 * @author Mattia Zoccarato <mattia@filoblu.com>
 * @link http://www.filoblu.com
 */
class Adyen_Payment_Helper_Log extends Mage_Payment_Helper_Data {

    public function logException($message) {
        Mage::log($message, Zend_Log::ERR, 'adyen_exception.log');
    }

    public function log($message, $type) {
        Mage::log($message, Zend_Log::DEBUG, "adyen_".strtolower($type).".log", true);
    }

}
