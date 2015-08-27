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
class Adyen_Payment_Helper_Data extends Mage_Payment_Helper_Data
{
	
	/** @var Adyen_Core_Model_Factory */
    protected $factory = null;
    private $_helperLog;

    const XML_PATH_HPP_PAYMENT_METHOD_FEE   = 'payment/adyen_hpp/fee';
    /**
     * @return array
     */
    public function getCcTypes()
    {
        $_types = Mage::getConfig()->getNode('default/adyen/payment/cctypes')->asArray();
        uasort($_types, array('Mage_Payment_Model_Config', 'compareCcTypes'));
        $types = array();
        foreach ($_types as $data) {
            if (! $data['is_checkout']) {
                continue;
            }
            $types[$data['code']] = $data['name'];
        }
        return $types;
    }


    /**
     *
     */
    public function getCcTypesAltData()
    {
        $_types = Mage::getConfig()->getNode('default/adyen/payment/cctypes')->asArray();
        uasort($_types, array('Mage_Payment_Model_Config', 'compareCcTypes'));
        $types = array();
        foreach ($_types as $data) {
            $types[$data['code_alt']] = $data;
        }
        return $types;
    }


    /**
     * @return array
     */
    public function getBoletoTypes()
    {
        $_types = Mage::getConfig()->getNode('default/adyen/payment/boletotypes')->asArray();
        $types = array();
        foreach ($_types as $data) {
            $types[$data['code']] = $data['name'];
        }
        return $types;
    }


    /**
     * @return array
     */
    public function getOpenInvoiceTypes()
    {
        $_types = Mage::getConfig()->getNode('default/adyen/payment/openinvoicetypes')->asArray();
        $types = array();
        foreach ($_types as $data) {
            $types[$data['code']] = $data['name'];
        }
        return $types;
    }


    /**
     * @return array
     */
    public function getRecurringTypes()
    {
        $_types = Mage::getConfig()->getNode('default/adyen/payment/recurringtypes')->asArray();
        $types = array();
        foreach ($_types as $data) {
            $types[$data['code']] = $data['name'];
        }
        return $types;
    }


    /**
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()->getModuleConfig('Adyen_Payment')->version;
    }


    /**
     * @return bool|int
     */
    public function hasEnableScanner()
    {
        if(Mage::getStoreConfig('payment/adyen_pos/active')) {
            return (int) Mage::getStoreConfig('payment/adyen_pos/enable_scanner');
        }
        return false;
    }


    /**
     * @return int
     */
    public function hasAutoSubmitScanner()
    {
        return (int) Mage::getStoreConfig('payment/adyen_pos/auto_submit_scanner');
    }


    /**
     * @return bool|int
     */
    public function hasExpressCheckout() {
        if(Mage::getStoreConfig('payment/adyen_pos/active')) {
            // check if metmethod is available
            $methodModel = Mage::getModel('adyen/adyen_pos');
            if ($methodModel) {
                if($methodModel->isAvailable()) {
                    return (int) Mage::getStoreConfig('payment/adyen_pos/express_checkout');
                }
            }
        }
        return false;
    }

    /**
     * @return bool|int
     */
    public function hasCashExpressCheckout() {
        if(Mage::getStoreConfig('payment/adyen_cash/active')) {

            // check if metmethod is available
            $methodModel = Mage::getModel('adyen/adyen_cash');
            if ($methodModel) {
                if($methodModel->isAvailable()) {
                    return (int) Mage::getStoreConfig('payment/adyen_cash/cash_express_checkout');
                }
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getOrderStatus()
    {
        return Mage::getStoreConfig('payment/adyen_abstract/order_status');
    }


    /**
     * @param Mage_Sales_Model_Quote | Mage_Sales_Model_Order $object
     * @return bool
     */
    public function isPaymentFeeEnabled($object)
    {
        $paymentMethod = $object->getPayment()->getMethod() ;

        if($paymentMethod == 'adyen_openinvoice')
        {
            $fee = Mage::getStoreConfig('payment/adyen_openinvoice/fee');
            if($fee > 0) {
                return true;
            }
        } elseif($paymentMethod == 'adyen_ideal') {
            $fee = Mage::getStoreConfig('payment/adyen_ideal/fee');
            if($fee > 0) {
                return true;
            }
        } elseif(substr($paymentMethod,0, 10)  == 'adyen_hpp_') {

            $fee = $this->getHppPaymentMethodFee($paymentMethod);
            if($fee) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param Mage_Sales_Model_Quote | Mage_Sales_Model_Order $object
     * @return float
     */
    public function getPaymentFeeAmount($object)
    {
        $paymentMethod = $object->getPayment()->getMethod() ;
        if ($paymentMethod == 'adyen_openinvoice') {
            return Mage::getStoreConfig('payment/adyen_openinvoice/fee');
        } elseif($paymentMethod == 'adyen_ideal') {
            return Mage::getStoreConfig('payment/adyen_ideal/fee');
        } elseif(substr($paymentMethod,0, 10)  == 'adyen_hpp_') {
            return $this->getHppPaymentMethodFee($paymentMethod);
        }
        return 0;
    }

    /**
     * @return array
     */
    public function getHppPaymentMethodFees()
    {
        $config = Mage::getStoreConfig(self::XML_PATH_HPP_PAYMENT_METHOD_FEE);

        return $config ? unserialize($config) : array();
    }

    public function getHppPaymentMethodFee($paymentMethod)
    {
        $paymentMethod = str_replace('adyen_hpp_', '', $paymentMethod);

        $paymentFees = $this->getHppPaymentMethodFees();

        if($paymentFees && is_array($paymentFees) && !empty($paymentFees)) {

            foreach($paymentFees as $paymentFee) {
                if(isset($paymentFee['code']) && $paymentFee['code'] == $paymentMethod) {
                    if(isset($paymentFee['amount']) && $paymentFee['amount'] > 0) {
                        return $paymentFee['amount'];
                    }
                }
            }
        }
        return null;
    }



    /**
     * Return the formatted currency. Adyen accepts the currency in multiple formats.
     * @param $amount
     * @param $currency
     *
     * @return string
     */
    public function formatAmount($amount, $currency)
    {
        switch($currency) {
            case "JPY":
            case "IDR":
            case "KRW":
            case "BYR":
            case "VND":
            case "CVE":
            case "DJF":
            case "GNF":
            case "PYG":
            case "RWF":
            case "UGX":
            case "VUV":
            case "XAF":
            case "XOF":
            case "XPF":
            case "GHC":
            case "KMF":
                $format = 0;
                break;
            case "MRO":
                $format = 1;
                break;
            case "BHD":
            case "JOD":
            case "KWD":
            case "OMR":
            case "LYD":
            case "TND":
                $format = 3;
                break;
            default:
                $format = 2;
                break;
        }

        return number_format($amount, $format, '', '');
    }

    public function originalAmount($amount, $currency) {
        // check the format
        switch($currency) {
            case "JPY":
            case "IDR":
            case "KRW":
            case "BYR":
            case "VND":
            case "CVE":
            case "DJF":
            case "GNF":
            case "PYG":
            case "RWF":
            case "UGX":
            case "VUV":
            case "XAF":
            case "XOF":
            case "XPF":
            case "GHC":
            case "KMF":
                $format = 1;
                break;
            case "MRO":
                $format = 10;
                break;
            case "BHD":
            case "JOD":
            case "KWD":
            case "OMR":
            case "LYD":
            case "TND":
                $format = 1000;
                break;
            default:
                $format = 100;
                break;
        }

        return ($amount / $format);
    }

    /**
     * Creditcard type that is selected is different from creditcard type that we get back from the request this
     * function get the magento creditcard type this is needed for getting settings like installments
     * @param $ccType
     * @return mixed
     */
    public function getMagentoCreditCartType($ccType) {

        $ccTypesMapper = Mage::helper('adyen')->getCcTypesAltData();

        if(isset($ccTypesMapper[$ccType])) {
            $ccType = $ccTypesMapper[$ccType]['code'];
        }

        return $ccType;
    }

	public function getAdditionalInformationField($field,$transactionAdditionalInfo){
        $found = 0;
        $existsAdditionalDataEntry = 0;
        foreach($transactionAdditionalInfo['raw_details_info'] as $index => $value){
            if(strstr($index,'additionalData_entry') || $existsAdditionalDataEntry){
                if($found == 1){
                    return $value;
                }
                else if(strstr($index,'_key') && $value == $field){
                    $found = 1;
                    continue;
                }
                $existsAdditionalDataEntry = 1;
            }
        }
        return null;
    }

    /**
     * Used via Payment method.Notice via configuration ofcourse Y or N
     * @return boolean true on demo, else false
     */
    public function getConfigDataDemoMode($storeId = null)
    {
        if ($this->getConfigData('demoMode', null, $storeId) == 'Y') {
            return true;
        }
        return false;
    }


    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigDataWsUserName($storeId = null)
    {
        if ($this->getConfigDataDemoMode($storeId)) {
            return $this->getConfigData('ws_username_test', null, $storeId);
        }
        return $this->getConfigData('ws_username_live', null, $storeId);
    }


    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getConfigDataWsPassword($storeId = null)
    {
        if ($this->getConfigDataDemoMode($storeId)) {
            return Mage::helper('core')->decrypt($this->getConfigData('ws_password_test', null, $storeId));
        }
        return Mage::helper('core')->decrypt($this->getConfigData('ws_password_live', null, $storeId));
    }


    /**
     * @param      $code
     * @param null $paymentMethodCode
     * @param null $storeId
     * @deprecated please use getConfigData
     * @return mixed
     */
    public function _getConfigData($code, $paymentMethodCode = null, $storeId = null)
    {
        return $this->getConfigData($code, $paymentMethodCode, $storeId);
    }


    /**
     * @desc    Give Default settings
     * @example $this->_getConfigData('demoMode','adyen_abstract')
     * @since   0.0.2
     *
     * @param string $code
     *
     * @return mixed
     */
    public function getConfigData($code, $paymentMethodCode = null, $storeId = null) {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }
        if (empty($paymentMethodCode)) {
            return trim(Mage::getStoreConfig("payment/adyen_abstract/$code", $storeId));
        }
        return trim(Mage::getStoreConfig("payment/$paymentMethodCode/$code", $storeId));
    }


    /**
     * Get the client ip address
     * @return string
     */
    public function getClientIp() {
        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        }elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif(isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif(isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = '';
        }

        return $ipaddress;
    }


    /**
     * Is th IP in the given range
     * @param $ip
     * @param $from
     * @param $to
     *
     * @return bool
     */
    public function ipInRange($ip, $from, $to) {
        $ip = ip2long($ip);
        $lowIp = ip2long($from);
        $highIp = ip2long($to);

        if ($ip <= $highIp && $lowIp <= $ip) {
            return true;
        }
        return false;
    }


    /**
     * normalize adyen response
     * @param array $array
     * @param array $origin
     * @param string $keyToAdd
     * @return array
     */
    public function getResponseArray(array $array, &$origin = null, $keyToAdd = null) {

        if (!$origin) {
            $origin = array();
        }
        if (!is_null($keyToAdd)) {
            $keyToAdd .= "_";
        }

        foreach ($array as $key => $value) {

            if (is_array($value)) {
                $origin[$keyToAdd . (string) $key] = '';
                $this->getResponseArray($value, $origin, $key);
            } else {

                $origin[$keyToAdd . (string) $key] = (string) $value;
            }
        }

        return $origin;
    }

/**
     * Street format
     * @param type $address
     * @return Varien_Object
     */
    public function getStreet($address) {
        if (empty($address)) return false;
        $street = self::formatStreet($address->getStreet());
        $streetName = $street['0'];
        unset($street['0']);
//        $streetNr = implode('',$street);
        $streetNr = implode(' ',$street);

        return new Varien_Object(array('name' => $streetName, 'house_number' => $streetNr));
    }

    /**
     * Fix this one string street + number
     * @example street + number
     * @param type $street
     * @return type $street
     */
    static public function formatStreet($street) {
        if (count($street) != 1) {
            return $street;
        }
        preg_match('/((\s\d{0,10})|(\s\d{0,10}\w{1,3}))$/i', $street['0'], $houseNumber, PREG_OFFSET_CAPTURE);
        if(!empty($houseNumber['0'])) {
            $_houseNumber = trim($houseNumber['0']['0']);
            $position = $houseNumber['0']['1'];
            $streeName = trim(substr($street['0'], 0, $position));
            $street = array($streeName,$_houseNumber);
        }
        return $street;
    }

    
    /**
     * @return Adyen_Core_Helper_Config
     */
    protected function helperConfig() {
        return $this->getFactory()->helperConfig();
    }
    
    /**
     *
     * @return Adyen_Core_Model_Factory
     */
    public function getFactory() {
        if ($this->factory === null) {
            $this->factory = new Adyen_Core_Model_Factory();
        }
        return $this->factory;
    }

    /**
     *
     * @param Adyen_Core_Model_Factory $factory
     */
    public function setFactory(Adyen_Core_Model_Factory $factory) {
        $this->factory = $factory;
    }
    
}
