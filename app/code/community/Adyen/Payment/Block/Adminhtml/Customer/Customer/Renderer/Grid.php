<?php

class Adyen_Payment_Block_Adminhtml_Customer_Customer_Renderer_Grid extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function __construct() {
        return parent::_construct();
    }

    public function render(Varien_Object $row) {
        return $this->_getValue($row);
    }

    protected function _getValue(Varien_Object $row) {

        if ($getter = $this->getColumn()->getGetter()) {
            if (is_string($getter)) {
                return $row->$getter();
            } elseif (is_callable($getter)) {
                return call_user_func($getter, $row);
            }
            return '';
        }

        $val = $row->getData("customer_id");

        if ($val==0){
            return $row->getData("customer");
        }
                
        $imageUrl = Mage::helper("adminhtml")->getUrl("adminhtml/customer/edit/",array("id"=>$val,));

        $html = '<a href="' . $imageUrl . '" target="_blank"/>'.$row->getData("customer").'</a>';

        return $html;
    }

}

?>