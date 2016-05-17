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
class Magestore_Inventoryfulfillment_Block_Adminhtml_Fulfillment_Dashboard_Template extends Mage_Adminhtml_Block_Template
{
    const STATUS_VERIFIED = 1;
    const STATUS_PICKED = 2;
    const STATUS_PACKED = 3;
    const STATUS_READYTOSHIP = 4;

    public function __construct()
    {
        parent::__construct();
        $this->setId('fulfillment_dashboard_template');
        $this->setTemplate('inventoryfulfillment/dashboard/template.phtml');
    }

    public function getChartData($collection) {
        if ($collection) {
            $orderData = $collection->getData();
            $orderByDay = array();

            foreach ($orderData as $order) {
                $orderByDay[$order['date_without_hour']] = $order;
            }

            $return = '';
            for ($i = 30; $i >= 0; $i--) {
                $d = Mage::getModel('core/date')->gmtDate('Y-m-d', strtotime('-' . $i . ' days'));
                if ($i != 30) {
                    $return .= ', ';
                }
                if (isset($orderByDay[$d])) {
                    $return .= $orderByDay[$d]['order_by_day'];
                } else {
                    $return .= '0';
                }
            }
            return $return;
        }
    }
}