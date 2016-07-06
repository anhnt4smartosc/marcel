<?php
class Alex_Sales_Block_Adminhtml_Config_PaymentPoints extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_itemRenderer;

    public function _prepareToRender()
    {
        $this->addColumn('method_id', array(
            'label' => Mage::helper('alexsales')->__('Method'),
            'renderer' => $this->_getRenderer(),
        ));
        $this->addColumn('points', array(
            'label' => Mage::helper('alexsales')->__('Points'),
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('alexsales')->__('Add');
    }

    protected function  _getRenderer()
    {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->getLayout()->createBlock(
                'alexsales/adminhtml_config_form_field_payment', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()
                ->calcOptionHash($row->getData('method_id')),
            'selected="selected"'
        );
    }
}