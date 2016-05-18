<?php
/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @category   SM
 * @package    SM_Barcode
 * @version    2.0
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_Barcode_Adminhtml_Barcode_ProductController extends SM_Barcode_Controller_Adminhtml_Action {
//    protected function _isAllowed()
//    {
//        return Mage::getSingleton('admin/session')->isAllowed('barcode/index');
//    }

	protected function _initAction() {
		$this->loadLayout();
		return $this;
	}
	
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

    public function printAction() {
        $this->_initAction()
            ->renderLayout();
    }
    
    public function returnAction(){
        $this->_initAction()
            ->_setActiveMenu('barcode/return_products')
			->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();

    }
} 
