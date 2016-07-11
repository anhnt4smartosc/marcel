<?php

class Alex_Sales_Block_Adminhtml_Transaction_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $form = new Varien_Data_Form();

        $currentCommit = Mage::registry('current_transaction');

        $fieldSet = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('alexsales')->__('Transaction Information')));

        if(!$currentCommit || !$currentCommit->getId()) {
            $currentCommit = Mage::getModel('alexsales/transaction');
            $fieldSet->addField('xpos_user_id', 'select',
                array(
                    'name'  => 'xpos_user_id',
                    'values' => $this->_getCashiers(),
                    'label' => Mage::helper('alexsales')->__('Balance'),
                    'title' => Mage::helper('alexsales')->__('Balance'),
                    'required' => true,
                )
            );
        }

        $fieldSet->addField('type', 'select',
            array (
                'name'  => 'type',
                'values' => Mage::getModel('alexsales/transaction')->getAllTypeOptions(),
                'label' => Mage::helper('alexsales')->__('Type'),
                'title' => Mage::helper('alexsales')->__('Type'),
                'required' => true,
            )
        );

        $fieldSet->addField('points', 'text',
            array(
                'name'  => 'points',
                'label' => Mage::helper('alexsales')->__('Points'),
                'title' => Mage::helper('alexsales')->__('Points'),
                'required' => true,
            )
        );



        $fieldSet->addField('comment', 'textarea',
            array(
                'name'  => 'comment',
                'label' => Mage::helper('alexsales')->__('Comment'),
                'title' => Mage::helper('alexsales')->__('Comment'),
                'required' => true,
            )
        );

        if( Mage::getSingleton('adminhtml/session')->getCurrentCommitData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getCurrentCommitData());
            Mage::getSingleton('adminhtml/session')->setCurrentCommitData(null);
        } else {
            $form->addValues($currentCommit->getData());
        }

        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/save'));
        $this->setForm($form);
    }

    protected function _getCashiers()
    {
        $cashiers = Mage::getModel('xpos/user')->getCollection();

        $result = array();
        foreach($cashiers as $cashier) {
            $result[$cashier->getId()] = $cashier->getName();
        }
        return $result;
    }
}
