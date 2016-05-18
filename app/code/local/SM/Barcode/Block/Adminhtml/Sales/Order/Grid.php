<?php

class SM_Barcode_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {
    
	protected function _prepareMassaction()
    {
		parent::_prepareMassaction();
		if(Mage::helper('barcode')->isEnable()){

            $this->getMassactionBlock()->addItem('pdfinvoices_order_sm', array(
                'label'=> Mage::helper('sales')->__('Print Invoices w/Barcodes'),
                'url'  => $this->getUrl('*/barcode_order/pdfinvoices'),
            ));

            $this->getMassactionBlock()->addItem('pdfpackingslips_sm', array(
                'label'=> Mage::helper('sales')->__('Print Packingslips w/Barcodes'),
                'url'  => $this->getUrl('*/barcode_order/pdfshipments'),
            ));

            $this->getMassactionBlock()->addItem('pdforderpickinglist_sm', array(
                'label'=> Mage::helper('sales')->__('Print Order Picking List w/Barcodes'),
                'url'  => $this->getUrl('*/barcode_order/pdforderpickinglist'),
            ));

            $this->getMassactionBlock()->addItem('pdfitempickinglist_sm', array(
                'label'=> Mage::helper('sales')->__('Print Item Picking List w/Barcodes'),
                'url'  => $this->getUrl('*/barcode_order/pdfitempickinglist'),
            ));

//            $this->getMassactionBlock()->addItem('pdfcreditmemos_sm', array(
//                'label'=> Mage::helper('sales')->__('Print X-BAR\'s Creditmemos'),
//                'url'  => $this->getUrl('*/barcode_order/pdfcreditmemos'),
//            ));
        }
		return $this;
	}
}