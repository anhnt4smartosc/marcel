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
 * @package     Magestore_Inventorywebpos
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventoryxpos Observer Model
 *
 * @category    Magestore
 * @package     Magestore_Inventoryxpos
 * @author      Magestore Developer
 */
class Magestore_Inventoryxpos_Model_Observer {

    /**
     * Add tab XPos user in warehouse
     *
     * @param $observer
     * @return int
     */
    public function addXPosUserTabInWarehouse($observer) {
        if (Mage::helper('inventoryplus/xpos')->isXposActive()) {
            $warehouseId = $observer->getEvent()->getWarehouseId();
            if (!$warehouseId)
                return 0;
            $tab = $observer->getEvent()->getTab();
            /*
            $tab->addTab('xpos_user_section', array(
                'label' => Mage::helper('inventoryxpos')->__('XPos Users'),
                'title' => Mage::helper('inventoryxpos')->__('XPos Users'),
                'content' => $tab->getLayout()
                    ->createBlock('inventoryxpos/adminhtml_warehouse_edit_tab_xpos_user', 'adminhtml_warehouse_edit_tab_xpos_user')
                    ->toHtml()
            ));
            
             */
        }
        return 1;
    }

    /**
     * Save XPos users in warehouse
     *
     * @param $observer
     */
    public function saveXposUserInWarehouse($observer)
    {
        $data = $observer->getEvent()->getXposData();
        $warehouseId = $observer->getEvent()->getWarehouseId();
        if ($data['xpos_user'] && is_array($data['xpos_user'])) {
            $existingXposUserIds = array();
            $uncheckedXposUserIds = array();
            $existingXposUsersInWarehouse = Mage::getModel('inventoryxpos/xposuser')->getCollection()
                ->addFieldToFilter('warehouse_id', array('eq' => $warehouseId));
            foreach ($existingXposUsersInWarehouse as $user) {
                array_push($existingXposUserIds, $user->getXposUserId());
            }
            $checkedXposUserIds = $data['xpos_user'];

            $allXposUsers = Mage::getModel('xpos/user')->getCollection();
            foreach ($allXposUsers as $xposUser) {
                if (!in_array($xposUser->getXposUserId(), $data['xpos_user'])) {
                    array_push($uncheckedXposUserIds, $xposUser->getXposUserId());
                }
            }

            foreach ($uncheckedXposUserIds as $uncheckedXposUserId) {
                $existingRecord = Mage::getModel('inventoryxpos/xposuser')->getCollection()
                    ->addFieldToFilter('xpos_user_id', $uncheckedXposUserId)
                    ->addFieldToFilter('warehouse_id', $warehouseId)
                    ->getFirstItem();
                if ($existingRecord->getId()) {
                    try {
                        $existingRecord->delete();
                    } catch (Exception $ex) {

                    }
                }
            }

            foreach ($checkedXposUserIds as $checkedXposUserId) {
                $xposUserWarehouse = Mage::getModel('inventoryxpos/xposuser')->load($checkedXposUserId, 'xpos_user_id');
                if ($xposUserWarehouse->getId()) {
                    $xposUserWarehouse->setWarehouseId($warehouseId);
                } else {
                    $xposUserWarehouse = Mage::getModel('inventoryxpos/xposuser');
                    $xposUserWarehouse->setWarehouseId($warehouseId);
                    $xposUserWarehouse->setXposUserId($checkedXposUserId);
                }

                try {
                    $xposUserWarehouse->save();
                } catch (Exception $ex) {

                }
            }
        }
    }

    public function inp_prepare_post_creditmemo_data($observer) {
        $dataObject = $observer->getEvent()->getCreditmemoData();
        if (!$dataObject->getQty() || strpos($dataObject->getQty(), '$refund$') < 0) {
            return;
        }
        $qtyEx = explode('$refund$', $dataObject->getQty());
        $stockEx = explode('$refund$', $dataObject->getStock());
        $tempData = array();
        $creditmemoData = array();
        for ($i = 0; $i < count($qtyEx); $i++) {
            $anItemQty = explode('/', $qtyEx[$i]);
            $anItemStock = explode('/', $stockEx[$i]);
            if (isset($anItemQty[1]))
                $tempData[$i]['qty'] = $anItemQty[1];
            if (isset($anItemQty[0]) && $anItemQty[0] != '')
                $tempData[$i]['order_item_id'] = $anItemQty[0];
            if (isset($anItemStock[1]))
                $tempData[$i]['back_to_stock'] = ($anItemStock[1] == 'true' ? 1 : 0);
        }

        if (count($tempData)) {
            $creditmemoData['items'] = array();
            foreach ($tempData as $itemData) {
                $creditmemoData['items'][$itemData['order_item_id']] = array('back_to_stock' => $itemData['back_to_stock'],
                    'qty' => $itemData['qty']);
            }
        }
        $creditmemoData['select-warehouse-supplier'] = 1;
        $creditmemoData['warehouse-select'] = Mage::helper('inventoryxpos/warehouse')->getCurrentWarehouseId();
        $dataObject->setCreditmemo($creditmemoData);
    }
}