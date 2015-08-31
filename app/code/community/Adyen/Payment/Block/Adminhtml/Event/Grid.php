<?php
/**
 * Added Event Grid
 *
 * @category   Adyen
 * @package    Adyen_Payment
 * @author Mattia Zoccarato <mattia@filoblu.com>
 */
class Adyen_Payment_Block_Adminhtml_Event_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $currencies;
    protected $ccTypes;
    protected $methods;
    protected $adyenEventCodes;
    protected $adyenEventResults;
    protected $refusalReasons;

    public function __construct() {
        parent::__construct();
        $this->setId('adyenGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('event_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('adyen/event')->getCollection();
        $order = Mage::registry('current_order');
        if ($order) {
            $collection->addOrderIdFilter($order->getId());
        }
        $collection->addOrderInformation(array('increment_id'));
        //$collection->getSelect()->order('date desc');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {


        $this->addColumn('event_id', array(
            'header' => Mage::helper('adyen')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'event_id',
        ));

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('adyen')->__('Order #'),
            'align' => 'left',
            'index' => 'increment_id',
        ));


        $this->addColumn('created_at', array(
            'header' => Mage::helper('adyen')->__('Order Date'),
            'align' => 'left',
            'type' => 'datetime',
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_FULL),
            'gmtoffset' => true,
            'index' => 'created_at',
        ));
        
     
            $this->addColumn('customer', array(
                'header' => Mage::helper('adyen')->__('Customer'),
                'align' => 'left',
                'type' => 'action',
                'renderer'  => 'Adyen_Payment_Block_Adminhtml_Customer_Customer_Renderer_Grid',
                'filter' => false,
                'sortable' => false,
                'index' => 'customer',
                'is_system' => true,
                'width' => 150,
            ));




        $this->addColumn('amount', array(
            'header' => Mage::helper('adyen')->__('Total'),
            'index' => 'amount',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

                
        $this->addColumn('psp_reference', array(
            'header' => Mage::helper('adyen')->__('Shop Transaction Id'),
            'align' => 'left',
            'index' => 'psp_reference',
        ));
        
        $this->addColumn('adyen_transaction_type', array(
            'header' => Mage::helper('adyen')->__('Transaction Type'),
            'align' => 'center',
            'index' => 'adyen_transaction_type',
        ));
        
        
        $this->addColumn('adyen_event_code', array(
            'header' => Mage::helper('adyen')->__('Event Code'),
            'align' => 'center',
            'index' => 'adyen_event_code',
            'type' => 'options',
            'options' => $this->getAdyenEventCode(),
        ));

        $this->addColumn('adyen_event_result', array(
            'header' => Mage::helper('adyen')->__('Result'),
            'align' => 'center',
            'index' => 'adyen_event_result',
            'type' => 'options',
            'options' => $this->getAdyenEventResult(),
        ));
        
         $this->addColumn('refusal_reason', array(
            'header' => Mage::helper('adyen')->__('Refusal Reason'),
            'align' => 'center',
            'index' => 'refusal_reason',
             'type' => 'options',
            'options' => $this->getAdyenRefusalReason(),
        ));

        $this->addColumn('auth_code', array(
            'header' => Mage::helper('adyen')->__('Authorization Code'),
            'align' => 'left',
            'index' => 'auth_code',
        ));


        $this->addColumn('currency', array(
            'header' => Mage::helper('adyen')->__('Currency'),
            'align' => 'center',
            'index' => 'currency',
            'type' => 'options',
            'options' => $this->getCurrencyOptions(),
        ));


         $this->addColumn('payment_method', array(
            'header' => Mage::helper('adyen')->__('Payment Method'),
            'align' => 'center',
            'index' => 'payment_method',
            'type' => 'options',
            'options' => $this->getActivePaymentMethods(),
        ));
         

        return parent::_prepareColumns();
    }
    
    public function getActivePaymentMethods() {
        
        if (!$this->methods) {
            $payments = Mage::getSingleton('adyen/event')->getCollection()->addFieldToFilter("payment_method", array("notnull" => true));
            $this->methods = array();

            foreach ($payments as $payment) {
                $method = $payment->getData('payment_method');
                if (!in_array($method, $this->methods)) {
                    $this->methods[$method] = $method;
                }
            }
        }

        return $this->methods;
    }
    
    public function getActivePaymentCcTypes() {
        
        if (!$this->ccTypes) {
            $payments = Mage::getSingleton('adyen/event')->getCollection()->addFieldToFilter("payment_method", array("notnull" => true));
            $this->ccTypes = array();

            foreach ($payments as $payment) {
                $ccType = $payment->getData('payment_method');
                if (!in_array($ccType, $this->ccTypes)) {
                    $this->ccTypes[$ccType] = $ccType;
                }
            }
        }

        return $this->ccTypes;
    }
    
    public function getAdyenEventCode() {
        
        if (!$this->adyenEventCodes) {
            $payments = Mage::getSingleton('adyen/event')->getCollection()->addFieldToFilter("adyen_event_code", array("notnull" => true)); //->getAllMethods();//
            $this->adyenEventCodes = array();

            foreach ($payments as $payment) {
                $code = $payment->getData('adyen_event_code');
                if (!in_array($code, $this->adyenEventCodes)) {
                    $this->adyenEventCodes[$code] = $code;
                }
            }
        }

        return $this->adyenEventCodes;
    }
    
    public function getAdyenEventResult() {
        
        if (!$this->adyenEventResults) {
            $payments = Mage::getSingleton('adyen/event')->getCollection()->addFieldToFilter("adyen_event_result", array("notnull" => true)); //->getAllMethods();//
            $this->adyenEventResults = array();

            foreach ($payments as $payment) {
                $result = $payment->getData('adyen_event_result');
                if (!in_array($result, $this->adyenEventResults)) {
                    $this->adyenEventResults[$result] = $result;
                }
            }
        }

        return $this->adyenEventResults;
    }
    
    public function getAdyenRefusalReason() {
        
        if (!$this->refusalReasons) {
            $payments = Mage::getSingleton('adyen/event')->getCollection()->addFieldToFilter("refusal_reason", array("notnull" => true)); //->getAllMethods();//
            $this->refusalReasons = array();

            foreach ($payments as $payment) {
                $refusalReason = $payment->getData('refusal_reason');
                if (!in_array($refusalReason, $this->refusalReasons)) {
                    $this->refusalReasons[$refusalReason] = $refusalReason;
                }
            }
        }

        return $this->refusalReasons;
    }
    
    
    

    protected function getCurrencyOptions() {
        $orders = Mage::getSingleton('adyen/event')->getCollection()->addFieldToFilter("currency", array("notnull" => true)); //->getAllMethods();
        $this->currencies = array();

        foreach ($orders as $order) {
            $currency = $order->getData('currency');
            if (!in_array($currency, $this->currencies)) {
                $this->currencies[$currency] = $currency;
            }
        }

        return $this->currencies;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('adyen_id');
        $this->getMassactionBlock()->setFormFieldName('adyen');


        return $this;
    }

    public function getRowUrl($row) {
        if($row->getOrderId()){
            return $this->getUrl('*/*/view', array('event_id' => $row->getId()));
        }
        else{
            return false;
        }
    }
    
    
    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }


}

?>