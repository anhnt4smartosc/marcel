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
class Magestore_Inventoryfulfillment_Model_Ghnorder
{
    const CRON_STRING_PATH = 'crontab/jobs/magestore_inventoryfulfillment/schedule/cron_expr';
    const CRON_STRING_PATH_RUN = 'crontab/jobs/magestore_inventoryfulfillment/run/model';

    protected function _getOrdersCollection()
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('ghn_order_code', array('notnull' => true));

        return $orders;
    }

    public function getOrderStatus()
    {
        $currentHour = Mage::getModel('core/date')->date('H');
        if ($currentHour > 21 || $currentHour < 6) {
            return;
        }

        $serviceClient = new Magestore_GHNCarrier_Model_GHNRest;
        //SignIn And Get SessionToken
        $sessionToken = $serviceClient->SignIn();

        $ordersCollection = $this->_getOrdersCollection();
        $result = array(
            'ReadyToPick' => 0,
            'Picking' => 0,
            'Storing' => 0,
           'Delivering' => 0,
            'Delivered' => 0,
            'WaitingToFinish' => 0,
            'Finish' => 0,
            'Return' => 0,
            'Cancel' => 0
        );
        foreach ($ordersCollection as $order) {
            $getOrderInfoRequest = array(
                "SessionToken" => $sessionToken,
                'OrderCode' => $order->getGhnOrderCode()
            );
            $getOrderInfoResponse = $serviceClient->GetOrderInfo($getOrderInfoRequest);
            if (!empty($getOrderInfoResponse['ErrorMessage'])) {

            } else {
                if (!empty($getOrderInfoResponse['CurrentStatus'])) {
                    $result[$getOrderInfoResponse['CurrentStatus']]++;
                }
            }
        }


    }
}