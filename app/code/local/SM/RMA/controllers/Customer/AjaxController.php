<?php
class SM_RMA_Customer_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function getItemsAction(){
        $result = array();
        $order_id = $this->getRequest()->getPost('order');
        $order = Mage::getModel('sales/order')->load($order_id);
        /*
         * $items = Mage::getModel('sales/order_item')->getCollection()
                                    ->addFieldToSelect('product_id')
                                    ->addFieldToFilter('order_id', $order->getId())
                                    ->addFieldToFilter('product_type', 'simple');
                                    */
        $items = $order->getItemsCollection();
        $filteredItems = array();
        $product_ids = array();
        foreach($items as $key=>$item){
            if (!$item->getParentItem()){
                $filteredItems[] = $item;
                $product_ids[] = (int)$item->getProductId();
            }
        }
        
        $result['table'] = $this->getLayout()->createBlock('rma/customer_new_items')
                                                    ->setItems($filteredItems)
                                                    ->renderView();
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    /**
     * Product Search Action
     *
     */
    public function productSearchAction() {

        $items = array();

        $start = $this->getRequest()->getParam('start', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $query = $this->getRequest()->getParam('query', '');

        $searchInstance = new SM_RMA_Model_Search_Catalog;

        $results = $searchInstance->setStart($start)
                ->setLimit($limit)
                ->setQuery($query)
                ->load()
                ->getResults();

        $items = array_merge_recursive($items, $results);

        $totalCount = sizeof($items);

        $block = $this->getLayout()//->createBlock('adminhtml/template')
                ->createBlock('core/template')
                ->setTemplate('rma/autocomplete.phtml')
                ->assign('items', $items);
        $this->getResponse()->setBody($block->toHtml());
    }    
} 
