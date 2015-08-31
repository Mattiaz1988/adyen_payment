<?php

/**
 * Adminhtml event detail
 *
 * @category   Adyen
 * @package    Adyen_Payment
 * @author Mattia Zoccarato <mattia@filoblu.com>
*/
class Adyen_Payment_Block_Adminhtml_Event_Detail extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Transaction model
     *
     * @var Mage_Sales_Model_Order_Payment_Transaction
     */
    protected $_txn;

    /**
     * Add control buttons
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->_event = Mage::registry('current_event');

        $backUrl = ($this->_event->getOrderUrl()) ? $this->_event->getOrderUrl() : $this->getUrl('*/*/');
        $this->_addButton('back', array(
            'label'   => Mage::helper('sales')->__('Back'),
            'onclick' => "setLocation('{$backUrl}')",
            'class'   => 'back'
        ));

    }

    /**
     * Retrieve header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('adyen')->__("Adyen Event # %s | %s", $this->_event->getEventId(), $this->formatDate($this->_event->getCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true));
    }

    protected function _toHtml()
    {
        $this->setEventIdHtml($this->escapeHtml($this->_event->getEventId()));

        $this->setOrderIncrementIdHtml($this->escapeHtml($this->_event->getOrder()->getIncrementId()));
        
        $this->setOrderIdUrlHtml(
            $this->escapeHtml($this->getUrl('adminhtml/sales_order/view', array('order_id' => $this->_event->getOrderId())))
        );
        
        $this->setEventCodeHtml($this->escapeHtml($this->_event->getAdyenEventCode()));
        $this->setEventResultHtml($this->escapeHtml($this->_event->getAdyenEventResult()));
        $this->setEventTypeHtml($this->escapeHtml($this->_event->getAdyenTransactionType()));
        $this->setEventPspReferenceHtml($this->escapeHtml($this->_event->getPspReference()));

        $createdAt = (strtotime($this->_event->getCreatedAt()))
            ? $this->formatDate($this->_event->getCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true)
            : $this->__('N/A');
        $this->setCreatedAtHtml($this->escapeHtml($createdAt));

        return parent::_toHtml();
    }
    
    public function getOrder(){
        return $this->_event->getOrder();
    }
}
