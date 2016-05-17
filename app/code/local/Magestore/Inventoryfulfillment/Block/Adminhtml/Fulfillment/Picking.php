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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Picking extends Magestore_Inventoryfulfillment_Block_Adminhtml_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('order_picking_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setTag('picking');
    }

    /**
     * Add filters to collection
     *
     * @return \Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Picking
     */
    public function _filterCollection()
    {
        $pickOrderIds = $this->helper('inventoryfulfillment')->getPickingOrderIds();
        $this->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $pickOrderIds))
                //->addFieldToFilter('order.fulfillment_status', Magestore_Inventoryfulfillment_Model_Order_Fulfillmentstatus::STATUS_VERIFIED)
                ;
        return $this->getCollection();
    }    

    public function getRowDetails($row)
    {
        $url = $this->getUrl('adminhtml/inf_orderpicking/orderdetails');
        return array('url' => $url, 'order_id' => $row->getId());
    }
}