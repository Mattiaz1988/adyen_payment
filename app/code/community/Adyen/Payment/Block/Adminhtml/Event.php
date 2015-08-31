<?php

 
class Adyen_Payment_Block_Adminhtml_Event extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_event';
    $this->_blockGroup = 'adyen';
    $this->_headerText = Mage::helper('adyen')->__('Adyen Events');
    $this->_addButtonLabel = Mage::helper('adyen')->__('Add Item');

    parent::__construct();

        $this->removeButton("add");
  }

}
?>