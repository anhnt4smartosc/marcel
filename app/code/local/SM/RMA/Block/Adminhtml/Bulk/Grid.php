<?php
class SM_RMA_Block_Adminhtml_Bulk_Grid extends Mage_Adminhtml_Block_Widget_Grid{
    public function __construct(){
        parent::__construct();
        $this->setId('rmaReturnProductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);


    }

    protected function _prepareCollection()
    {
        if($this->getRequest()->getParam('order_id')){
            $order_id = $this->getRequest()->getParam('order_id');
        }
        else{
            $session = Mage::getSingleton("adminhtml/session");
           // $session->unsetData("lstId");
           // $session->unsetData("lstNewId");
            $order_id = 0;
        }
        $session = Mage::getSingleton("adminhtml/session");

        if($order_id!=0){

            $listID = $session->getData("lstId");
            $listNewId = $session->getData("lstNewId");
            if($listID[0]==0){
                if($order_id!=0){
                    $newlist = array();
                    $newlist[] = $order_id;
                    $session->setData("lstId",$newlist);
                    $listID = $session->getData("lstId");
                    $listID[] = $order_id;

                    $listNewId = $session->getData("lstNewId");

                    foreach($listID as $id){
                        $order = Mage::getModel('sales/order')->loadByIncrementID($id);
                        $listNewId[] = $order->getId();
                    }

                    $session->setData("lstNewId",$listNewId);
                }

            }else{
                Mage::log('b');
                if($listID[0]==1){
                    $newlist = array();
                    $newlist[] = $order_id;
                    $session->setData("lstId",$newlist);
                }
                $listID = $session->getData("lstId");
                $listID[] = $order_id;

                $listNewId = $session->getData("lstNewId");

                foreach($listID as $id){
                    $order = Mage::getModel('sales/order')->loadByIncrementID($id);
                    $listNewId[] = $order->getId();
                }
                $session->setData("lstNewId",$listNewId);
            }
        }else{
            $listNewId = $session->getData("lstNewId");

        }


           // $order = Mage::getModel('sales/order')->loadByIncrementID($bulk_order_id);
            $collection = Mage::getResourceModel('sales/order_grid_collection')
                ->addFieldToFilter('entity_id', $listNewId)
                //->addFieldToFilter('main_table.product_type', 'simple')
                //->addFieldToFilter('qty_shipped',array('gt' => 0))
            ;


        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('functionalities',
            array(
                'header' => Mage::helper('sales')->__('Action'),
                'width'     => '100px',
                'renderer'  => 'rma/adminhtml_bulk_grid_renderer_functionalities',
                'type'      => 'action',
                'filter'    => false,
                'sortable'  => false
            )
        );

        $store = $this->_getStore();


        return parent::_prepareColumns();
    }

    protected function _addColumnFilterToCollection($column) {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
            // Set filter for renderer column (Scanned Quantity column)
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
                return parent::_addColumnFilterToCollection($column);
            } else {
                $cond = $column->getFilter()->getCondition();
                $select = $this->getCollection()->getSelect();
                if ($column->getId() == 'scanned_qty') {
                    if (!empty($cond['from']) && !empty($cond['to'])) {
                        $select->where('countinventory.scanned_qty >= '.$cond['from'].' AND countinventory.scanned_qty <= '.$cond['to'].'');
                    } elseif(!empty($cond['from']) && empty($cond['to'])) {
                        $select->where('countinventory.scanned_qty >= '.$cond['from'].'');
                    } elseif(empty($cond['from']) && !empty($cond['to'])) {
                        $select->where('countinventory.scanned_qty <= '.$cond['to'].'');
                    }
                    return $this;
                }
                else {
                    return parent::_addColumnFilterToCollection($column);
                }
            }
        }
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                'label'=> Mage::helper('sales')->__('Cancel'),
                'url'  => $this->getUrl('*/rma_bulk/massCancel'),
                'complete' => 'rmaReturnProductsGridJsObject.reload()'
            ));
        }

        $this->getMassactionBlock()->addItem('invoices_order', array(
            'label'=> Mage::helper('sales')->__('Invoices'),
            'url'  => $this->getUrl('*/rma_bulk/voidPayment'),
            'complete' => 'rmaReturnProductsGridJsObject.reload()'
        ));

        $this->getMassactionBlock()->addItem('shipments_order', array(
            'label'=> Mage::helper('sales')->__('Ship Orders'),
            'url'  => $this->getUrl('*/rma_bulk/massShipOrder'),
            'complete' => '(function(){rmaReturnProductsGridJsObject.reload(); rmaReturnProductsGrid_massactionJsObject.unselectAll();})()'
        ));

        $this->getMassactionBlock()->addItem('refund_order', array(
            'label'=> Mage::helper('sales')->__('Refund Orders'),
            'url'  => $this->getUrl('*/rma_bulk/massRefundOrderNotReturn'),
            'complete' => '(function(){rmaReturnProductsGridJsObject.reload(); rmaReturnProductsGrid_massactionJsObject.unselectAll();})()'
        ));
        $this->getMassactionBlock()->addItem('refund_order_return', array(
            'label'=> Mage::helper('sales')->__('Refund Orders(Items return to stock)'),
            'url'  => $this->getUrl('*/rma_bulk/massRefundOrderReturn'),
            'complete' => '(function(){rmaReturnProductsGridJsObject.reload(); rmaReturnProductsGrid_massactionJsObject.unselectAll();})()'
        ));

        return $this;
    }


    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }


    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
} 
