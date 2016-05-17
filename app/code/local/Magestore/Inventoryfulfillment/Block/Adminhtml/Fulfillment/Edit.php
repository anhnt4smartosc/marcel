<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryfulfillment Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'inventoryfulfillment';
        $this->_controller = 'adminhtml_fulfillment';
        
        $this->_updateButton('save', 'label', Mage::helper('inventoryfulfillment')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('inventoryfulfillment')->__('Delete Item'));
        
        /*
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);
        */
        
        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('add');
        $this->_removeButton('reset');
        $this->_removeButton('back');
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('inventoryfulfillment_data')
            && Mage::registry('inventoryfulfillment_data')->getId()
        ) {
            return Mage::helper('inventoryfulfillment')->__("Edit Item '%s'",
                                                $this->htmlEscape(Mage::registry('inventoryfulfillment_data')->getTitle())
            );
        }
        return Mage::helper('inventoryfulfillment')->__('Fulfillment Management');
    }
}