<?php

class Adyen_Payment_Adminhtml_EventController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/adyen')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }
    
    /**
     * Initialize adyen event model
     *
     * @return Adyen_Payment_Model_Event | bool
     */
    protected function _initEvent()
    {
        $event = Mage::getModel('adyen/event')->load(
            $this->getRequest()->getParam('event_id')
        );

        if (!$event->getId()) {
            $this->_getSession()->addError($this->__('Wrong event ID specified.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $event->setOrderUrl(
                $this->getUrl('*/sales_order/view', array('order_id' => $orderId))
            );
        }

        Mage::register('current_event', $event);
        return $event;
    }
    
    /**
     * View Transaction Details action
     */
    public function viewAction()
    {
        $event = $this->_initEvent();
        if (!$event) {
            return;
        }
        $this->_title($this->__('Sales'))
            ->_title($this->__('Events'))
            ->_title(sprintf("#%s", $event->getEventId()));

        $this->loadLayout()
            ->_setActiveMenu('sales/adyen_event')
            ->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'adyen.csv';
        $content = $this->getLayout()->createBlock('adyen/adminhtml_event_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'adyen.xml';
        $content = $this->getLayout()->createBlock('adyen/adminhtml_event_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

}

?>