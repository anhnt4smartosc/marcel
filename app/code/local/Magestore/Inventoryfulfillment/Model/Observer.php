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
 * Inventoryfulfillment Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Inventoryfulfillment
 * @author      Magestore Developer
 */
class Magestore_Inventoryfulfillment_Model_Observer {

    /**
     * List of permissions in fulfillment
     * 
     * @var array
     */
    protected $_permissions = array('pick_pack_item' => 'Pick & Pack Items', 
                                    'ship_order' => 'Ship Orders'
                            );
    
    
    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Inventoryfulfillment_Model_Observer
     */
    public function controllerActionPredispatch($observer) {
        $action = $observer->getEvent()->getControllerAction();
        return $this;
    }

    /**
     * Create new shipping order by Giaohangnhanh API
     *
     * @param $observer
     */
    public function createGHNShippingOrder($observer) {
        $dataBefore = $observer->getEvent()->getDataBefore();
        if (!Mage::helper('core')->isModuleEnabled('Magestore_GHNCarrier')) {
            $dataBefore->setStatus(true);
            return;
        }
        $order = $observer->getEvent()->getOrder();
        // Get ContentNote
        $orderDescription = '';
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            $orderDescription .= $item->getDescrpition() . '. ';
        }
        $dataBefore = $observer->getEvent()->getDataBefore();

        // Get ClientOrderCode
        $orderIncrementId = $order->getIncrementId();
        $shippingAddress = $order->getShippingAddress();
        // Get RecipientName
        $recipientName = Mage::helper('inventoryfulfillment')->isNullOrEmptyString($shippingAddress->getMiddlename()) ? $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname() : $shippingAddress->getFirstname() . ' ' . $shippingAddress->getMiddlename() . ' ' . $shippingAddress->getLastname();
        $serviceClient = new Magestore_GHNCarrier_Model_GHNRest;

        //SignIn And Get SessionToken
        $sessionToken = $serviceClient->SignIn();

        //Get Pick Hubs
        $firstPickHubID = null;
        $getPickHubRequest = array("SessionToken" => $sessionToken);
        $responseGetPickHubs = $serviceClient->GetClientHubs($getPickHubRequest);
        if (empty($responseGetPickHubs['ErrorMessage'])) {
            $firstPickHubID = $responseGetPickHubs['HubInfo'][0]['PickHubID'];
        }

        //Create SO
        $shippingOrderRequest = array(
            "SessionToken" => $sessionToken,
            "RecipientName" => $recipientName,
            "DeliveryAddress" => $shippingAddress->getCity(),
            "RecipientPhone" => $shippingAddress->getTelephone(),
            "ClientOrderCode" => $orderIncrementId,
            "CODAmount" => ceil($order->getGrandTotal()),
            "ContentNote" => $orderDescription,
            "DeliveryDistrictCode" => '1A09',
            "ServiceID" => 53321,
            "PickHubID" => $firstPickHubID,
            "Weight" => $order->getWeight(),
            "Length" => 50,
            "Width" => 50,
            "Height" => 10);

        $responseCreateShippingOrder = $serviceClient->CreateShippingOrder($shippingOrderRequest);
        if (!empty($responseCreateShippingOrder['ErrorMessage'])) {
            $dataBefore->setStatus(false);
            $dataBefore->setError($responseCreateShippingOrder['ErrorMessage']);
        } else {
            if (!empty($responseCreateShippingOrder['OrderCode'])) {
                $order->setData('ghn_order_code', $responseCreateShippingOrder['OrderCode']);
                try {
                    $order->save();
                    $dataBefore->setStatus(true);
                } catch (Exception $e) {
                    $dataBefore->setStatus(false);
                    $dataBefore->setError($e->getMessage());
                }
            } else {
                $dataBefore->setStatus(false);
                $dataBefore->setError(Mage::helper('inventoryfulfillment')->__('Can not push the order to Giaohangnhanh.'));
            }
        }
    }

    public function inventoryfulfillmentOrderPickAfter($observer) {
        Mage::helper('inventoryfulfillment')->createFulfillmentLog($observer->getEvent()->getOrder());
        if ($observer->getEvent()->getIsNotifyEmail()) {
            $order = $observer->getEvent()->getOrder();
            Mage::helper('inventoryfulfillment')->sendMailToNotifyCustomer($order);
        }
    }

    public function inventoryfulfillmentOrderPackAfter($observer) {
        Mage::helper('inventoryfulfillment')->createFulfillmentLog($observer->getEvent()->getOrder());
        if ($observer->getEvent()->getIsNotifyEmail()) {
            $order = $observer->getEvent()->getOrder();
            Mage::helper('inventoryfulfillment')->sendMailToNotifyCustomer($order);
        }
    }

    public function inventoryfulfillmentOrderReadyAfter($observer) {
        Mage::helper('inventoryfulfillment')->createFulfillmentLog($observer->getEvent()->getOrder());
        if ($observer->getEvent()->getIsNotifyEmail()) {
            $order = $observer->getEvent()->getOrder();
            Mage::helper('inventoryfulfillment')->sendMailToNotifyCustomer($order);
        }
    }

    public function inventoryfulfillmentPackagesShipAfter($observer) {
        $packages = $observer->getEvent()->getPackages();
        foreach ($packages as $package) {
            Mage::helper('inventoryfulfillment')->createFulfillmentLog($package);
        }
    }
    
    public function add_warehouse_permission_grid($observer) {
        $grid = $observer->getEvent()->getGrid();
        $disabledvalue = $observer->getEvent()->getDisabled();
        $grid->addColumn('can_pick_pack_fulfillment', array(
            'header' => Mage::helper('inventoryfulfillment')->__('Pick & Pack <br/> (Fulfillment)'),
            'sortable' => false,
            'filter' => false,
            'width' => '60px',
            'type' => 'checkbox',
            'index' => 'user_id',
            'align' => 'center',
            'disabled_values' => $disabledvalue,
            'field_name' => 'pick_pack_item[]',
            'values' => $this->_getSelectedCanDoAction('pick_pack_item', $grid)
        ));
        
        $grid->addColumn('can_ship_fulfillment', array(
            'header' => Mage::helper('inventoryfulfillment')->__('Ship Order <br/> (Fulfillment)'),
            'sortable' => false,
            'filter' => false,
            'width' => '60px',
            'type' => 'checkbox',
            'index' => 'user_id',
            'align' => 'center',
            'disabled_values' => $disabledvalue,
            'field_name' => 'ship_order[]',
            'values' => $this->_getSelectedCanDoAction('ship_order', $grid)
        ));        
    }  

    
    /**
     * process inventory_adminhtml_add_more_permission event
     *
     * @return Magestore_Inventoryphysicalstocktaking_Model_Observer
     */
    public function inventory_adminhtml_add_more_permission($observer) {
        $event = $observer->getEvent();
        $assignment = $event->getPermission();
        $datas = $event->getData();
        $data = $datas['data'];
        $adminId = $event->getAdminId();
        $changePermissions = $observer->getEvent()->getChangePermssions();
        foreach($this->_permissions as $permission => $permisionLabel) {
            $permissionData = array();
            if (isset($data[$permission]) && is_array($data[$permission])) {
                $permissionData = $data[$permission];
            }
            if ($assignment->getId()) {
                $oldPermission = $assignment->getCanPhysical();
            }

            if (in_array($adminId, $permissionData)) {
                if ($assignment->getId()) {
                    if ($oldPermission != 1) {
                        $changePermissions[$adminId]['old_'.$permission] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot '. $permisionLabel);
                        $changePermissions[$adminId]['new_'.$permission] = Mage::helper('inventoryphysicalstocktaking')->__('Can '. $permisionLabel);
                    }
                } else {
                    $changePermissions[$adminId]['old_'.$permission] = '';
                    $changePermissions[$adminId]['new_'.$permission] = Mage::helper('inventoryphysicalstocktaking')->__('Can '. $permisionLabel);
                }
                $assignment->setData('can_'.$permission, 1);
            } else {
                if ($assignment->getId()) {
                    if ($oldPermission != 0) {
                        $changePermissions[$adminId]['old_'.$permission] = Mage::helper('inventoryphysicalstocktaking')->__('Can '. $permisionLabel);
                        $changePermissions[$adminId]['new_'.$permission] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot '. $permisionLabel);
                    }
                } else {
                    $changePermissions[$adminId]['old_'.$permission] = '';
                    $changePermissions[$adminId]['new_'.$permission] = Mage::helper('inventoryphysicalstocktaking')->__('Cannot '. $permisionLabel);
                }
                $assignment->setData('can_'.$permission, 0);
            }
        }
    }

    protected function _getSelectedCanDoAction($action, $grid) {
        $warehouse = $grid->getWarehouse();
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        $array = array();
        if ($warehouse->getId()) {
            $permittedAdmins = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
                    ->addFieldToFilter('warehouse_id', $warehouse->getId())
                    ->addFieldToFilter('can_'. $action, 1);
            foreach ($permittedAdmins as $permittedAdmin) {
                $array[] = $permittedAdmin->getAdminId();
            }
        } else {
            $array = array($adminId);
        }

        return $array;
    }
    

}
