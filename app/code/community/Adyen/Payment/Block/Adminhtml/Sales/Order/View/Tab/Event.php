<?php

/**
 *
 * @category   Adyen
 * @package    Adyen_Payments
 * @author Mattia Zoccarato <mattia@filoblu.com>
 */
class Adyen_Payment_Block_Adminhtml_Sales_Order_View_Tab_Event extends Adyen_Payment_Block_Adminhtml_Event_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/sales_order/events', array('_current' => true));
    }

    /**
     * Retrieve grid row url
     *
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('adyen/adminhtml_event/view', array('_current' => true, 'event_id' => $item->getId()));
    }

    /**
     * Retrieve tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('adyen')->__('Adyen Events');
    }

    /**
     * Retrieve tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('adyen')->__('Events');
    }

    /**
     * Check whether can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check whether tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

}
