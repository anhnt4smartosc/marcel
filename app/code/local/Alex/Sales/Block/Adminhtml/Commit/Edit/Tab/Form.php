<?php

class Alex_Sales_Block_Adminhtml_Commit_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $form = new Varien_Data_Form();

        $currentCommit = Mage::registry('current_commit');

        $fieldSet = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('alexsales')->__('Commit Information')));

        if(!$currentCommit || !$currentCommit->getId()) {
            $currentCommit = Mage::getModel('alexsales/commit');
            $fieldSet->addField('xpos_user_id', 'select',
                array(
                    'name'  => 'xpos_user_id',
                    'values' => $this->_getCashiers(),
                    'label' => Mage::helper('alexsales')->__('Balance'),
                    'title' => Mage::helper('alexsales')->__('Balance'),
                    'required' => true,
                )
            );
        } else {
            $cashiers = $this->_getCashiers();
            $fieldSet->addField('xpos_user', 'text',
                array(
                    'name'  => 'xpos_user',
                    'value' => $cashiers[$currentCommit->getXposUserId()],
                    'label' => Mage::helper('alexsales')->__('User Name'),
                    'title' => Mage::helper('alexsales')->__('User Name'),
                    'readonly' => true,
                )
            );
        }


        $fieldSet->addField('points', 'text',
            array(
                'name'  => 'points',
                'label' => Mage::helper('alexsales')->__('Points'),
                'title' => Mage::helper('alexsales')->__('Points'),
                'required' => true,
                'readonly' => true,
            )
        );

        $fieldSet->addField('balance', 'text',
            array(
                'name'  => 'balance',
                'label' => Mage::helper('alexsales')->__('Balance'),
                'title' => Mage::helper('alexsales')->__('Balance'),
                'required' => true,
                'readonly' => true,
            )
        );

        $fieldSet->addField('time', 'date', array(
            'name'               => 'time',
            'label'              => Mage::helper('alexsales')->__('From Date'),
            'after_element_html' => '<small>Comments</small>',
            'tabindex'           => 1,
            'image'              => $this->getSkinUrl('images/grid-cal.gif'),
            'format'             => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
            'value'              => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                strtotime('next weekday') )
        ));

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
