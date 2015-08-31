<?php
/**
 * Adminhtml event details grid
 *
 * @category   Adyen
 * @package    Adyen_Payment
 * @author Mattia Zoccarato <mattia@filoblu.com>
 */
class Adyen_Payment_Block_Adminhtml_Event_Detail_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    
    protected $_typeCode;
    
    /**
     * Initialize default sorting and html ID
     */
    protected function _construct()
    {
        $this->setId('eventDetailsGrid');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        
        if($this->getData("type_code") == 'request'){
            $this->buildKeyEntry($this->getEventRequest(),$collection);
        }else if($this->getData("type_code") == 'response'){
            $this->buildKeyEntry($this->getEventResponse(),$collection);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function buildKeyEntry(array $array,&$collection,$preKey = null){
        foreach ($array as $key => $value) {
            if(is_array($value)){
                $this->buildKeyEntry($value,$collection,$key);
            }
            else{
                $data = new Varien_Object(array('key' => $preKey?$preKey." > ".$key:$key, 'value' => $value));
                $collection->addItem($data);
            }
            
        }
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('key', array(
            'header'    => Mage::helper('sales')->__('Key'),
            'index'     => 'key',
            'sortable'  => false,
            'type'      => 'text',
            'width'     => '50%'
        ));

        $this->addColumn('value', array(
            'header'    => Mage::helper('sales')->__('Value'),
            'index'     => 'value',
            'sortable'  => false,
            'type'      => 'text',
            'escape'    => true
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve Transaction addtitional info
     *
     * @return array
     */
    public function getEventRequest()
    {
        $info = json_decode(Mage::registry('current_event')->getAdyenEventRequest(),true);
        return (is_array($info)) ? $info : array();
    }
    
    /**
     * Retrieve Transaction addtitional info
     *
     * @return array
     */
    public function getEventResponse()
    {
        $info = json_decode(Mage::registry('current_event')->getAdyenEventResponse(),true);
        return (is_array($info)) ? $info : array();
    }

}
