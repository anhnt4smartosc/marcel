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
class SM_Barcode_Block_Adminhtml_License extends Mage_Adminhtml_Block_Template{
    public function __construct(){
        $this->_blockGroup = 'barcode';
		$this->_controller = 'adminhtml_license';

		parent::__construct();
        
        $this->setTemplate('sm/barcode/license.phtml');
    }
    
}
 
