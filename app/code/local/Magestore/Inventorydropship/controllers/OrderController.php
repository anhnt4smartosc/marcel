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
 * @package     Magestore_Inventorydropship
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventorydropship Index Controller
 *
 * @category    Magestore
 * @package     Magestore_Inventorydropship
 * @author      Magestore Developer
 */

class Magestore_Inventorydropship_OrderController extends Mage_Sales_Controller_Abstract {

    /**
     * Drop Shipments page
     */
    public function dropshipAction()
    {
        $this->_viewAction();
    }
}