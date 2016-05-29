<?php

require_once Mage::getModuleDir('controllers', 'SM_Barcode') . DS. 'Adminhtml' . DS . 'Barcode' . DS .'AjaxController.php';

class Alex_Barcode_Adminhtml_Barcode_AjaxController extends SM_Barcode_Adminhtml_Barcode_AjaxController
{
    public function ajaxsaveconfigAction()
    {
        // Get all params -> Save to config
        //Not care time
        set_time_limit(0);

        if (isset($_GET['enable'])) Mage::getModel('core/config')->saveConfig('barcode/general/enable', $_GET['enable']);
        if (isset($_GET['key'])) Mage::getModel('core/config')->saveConfig('barcode/general/key', $_GET['key']);
        if (isset($_GET['symbology'])) Mage::getModel('core/config')->saveConfig('barcode/general/symbology', $_GET['symbology']);
        if (isset($_GET['unit'])) Mage::getModel('core/config')->saveConfig('barcode/general/input_size_unit', $_GET['unit']);
        if (isset($_GET['conversion'])) Mage::getModel('core/config')->saveConfig('barcode/product/conversion', $_GET['conversion']);
        if (isset($_GET['value'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_value', $_GET['value']);
        if (isset($_GET['field'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_field', $_GET['field']);
        if (isset($_GET['source'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_source', $_GET['source']);
        if (isset($_GET['orientation'])) Mage::getModel('core/config')->saveConfig('barcode/product/orientation', $_GET['orientation']);
        if (isset($_GET['width']) && $_GET['width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/width', $_GET['width']);
        if (isset($_GET['height']) && $_GET['height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/height', $_GET['height']);

        if (isset($_GET['barcode_width']) && $_GET['barcode_width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_width', $_GET['barcode_width']);
        if (isset($_GET['barcode_height']) && $_GET['barcode_height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_height', $_GET['barcode_height']);
        if (isset($_GET['barcode_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/barcode_settings', $_GET['barcode_settings']);

        if (isset($_GET['columns_display'])) Mage::getModel('core/config')->saveConfig('barcode/product/columns_display', $_GET['columns_display']);
        if (isset($_GET['rows_display'])) Mage::getModel('core/config')->saveConfig('barcode/product/rows_display', $_GET['rows_display']);

        if (isset($_GET['margin_top']) && $_GET['margin_top'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/page_margin_top', $_GET['margin_top']);
        if (isset($_GET['margin_left']) && $_GET['margin_left'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/page_margin_left', $_GET['margin_left']);

        if (isset($_GET['label_margin_top']) && $_GET['label_margin_top'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_margin_top', $_GET['label_margin_top']);
        if (isset($_GET['label_margin_left']) && $_GET['label_margin_left'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_margin_left', $_GET['label_margin_left']);
        if (isset($_GET['padding_top']) && $_GET['padding_top'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_top', $_GET['padding_top']);
        if (isset($_GET['padding_bottom']) && $_GET['padding_bottom'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_bottom', $_GET['padding_bottom']);
        if (isset($_GET['padding_left']) && $_GET['padding_left'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_left', $_GET['padding_left']);
        if (isset($_GET['padding_right']) && $_GET['padding_right'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/product/label_padding_right', $_GET['padding_right']);

        if (isset($_GET['include_logo'])) Mage::getModel('core/config')->saveConfig('barcode/product/include_logo', $_GET['include_logo']);
        if (isset($_GET['logo_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/logo_settings', $_GET['logo_settings']);
        if (isset($_GET['logo_width']) && $_GET['logo_width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/logo_width', $_GET['logo_width']);
        if (isset($_GET['logo_height']) && $_GET['logo_height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/logo_height', $_GET['logo_height']);

        if (isset($_GET['name_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/name_visible', $_GET['name_visible']);
        if (isset($_GET['product_name_leng']) && $_GET['product_name_leng'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/product_name_leng', $_GET['product_name_leng']);
        if (isset($_GET['product_name_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/product_name_settings', $_GET['product_name_settings']);

        if (isset($_GET['price_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/price_visible', $_GET['price_visible']);
        if (isset($_GET['price_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/price_settings', $_GET['price_settings']);

        if (isset($_GET['slot1_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot1_visible', $_GET['slot1_visible']);
        if (isset($_GET['slot1'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot1', $_GET['slot1']);
        if (isset($_GET['slot1_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot1_settings', $_GET['slot1_settings']);

        if (isset($_GET['slot2_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot2_visible', $_GET['slot2_visible']);
        if (isset($_GET['slot2'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot2', $_GET['slot1']);
        if (isset($_GET['slot2_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot2_settings', $_GET['slot2_settings']);

        if (isset($_GET['slot3_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot3_visible', $_GET['slot3_visible']);
        if (isset($_GET['slot3'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot3', $_GET['slot3']);
        if (isset($_GET['slot3_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot3_settings', $_GET['slot3_settings']);

        if (isset($_GET['slot4_visible'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot4_visible', $_GET['slot4_visible']);
        if (isset($_GET['slot4'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot4', $_GET['slot4']);
        if (isset($_GET['slot4_settings'])) Mage::getModel('core/config')->saveConfig('barcode/product/slot4_settings', $_GET['slot4_settings']);

        if (isset($_GET['font_for_text'])) Mage::getModel('core/config')->saveConfig('barcode/product/use_font_for_text', $_GET['font_for_text']);
        if (isset($_GET['font_size']) && $_GET['font_size'] > 0) Mage::getModel('core/config')->saveConfig('barcode/product/font_size', $_GET['font_size']);

        if (isset($_GET['barcode_order_include_logo'])) Mage::getModel('core/config')->saveConfig('barcode/order/barcode_order_include_logo', $_GET['barcode_order_include_logo']);
        if (isset($_GET['invoice_enabled'])) Mage::getModel('core/config')->saveConfig('barcode/order/invoice_enabled', $_GET['invoice_enabled']);
        if (isset($_GET['invoice_position'])) Mage::getModel('core/config')->saveConfig('barcode/order/invoice_position', $_GET['invoice_position']);
        if (isset($_GET['packingslip_enabled'])) Mage::getModel('core/config')->saveConfig('barcode/order/packingslip_enabled', $_GET['packingslip_enabled']);
        if (isset($_GET['packingslip_position'])) Mage::getModel('core/config')->saveConfig('barcode/order/packingslip_position', $_GET['packingslip_position']);
        if (isset($_GET['order_padding_top']) && $_GET['order_padding_top'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/padding_top', $_GET['order_padding_top']);
        if (isset($_GET['order_padding_left']) && $_GET['order_padding_left'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/padding_left', $_GET['order_padding_left']);
        if (isset($_GET['order_barcode_width']) && $_GET['order_barcode_width'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/barcode_width', $_GET['order_barcode_width']);
        if (isset($_GET['order_barcode_height']) && $_GET['order_barcode_height'] > 0) Mage::getModel('core/config')->saveConfig('barcode/order/barcode_height', $_GET['order_barcode_height']);
        if (isset($_GET['rma_valid_duration']) && $_GET['rma_valid_duration'] >= 0) Mage::getModel('core/config')->saveConfig('barcode/order/rma_valid_duration', $_GET['rma_valid_duration']);
        if (isset($_GET['stock_update'])) Mage::getModel('core/config')->saveConfig('barcode/order/stock_update', $_GET['stock_update']);

        if (isset($_GET['debug_isEnabled'])) Mage::getModel('core/config')->saveConfig('barcode/debug/isEnabled', $_GET['debug_isEnabled']);

        // Optimized save products > 10k
        $conversion = $_GET['conversion'];
        $barcode_field = $_GET['field'];
        $symbology = $_GET['symbology'];
        $barcode_source = $_GET['source'];

        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('catalog/product');
        $readConnection = $resource->getConnection('core_read');
        $generatePerRequest = isset($_GET['generate_per_request']) ? $_GET['generate_per_request'] : 0;
        if ($generatePerRequest > 0) {
            if (isset($_GET['first_click']) && $_GET['first_click']) {
                $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_lastPage_data');
                $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_totalRow_data');
            }
            // Pagination data
            //if $generatePerRequest = 0 then set it = 100 default
            if( $generatePerRequest == 0 ) $generatePerRequest = 100;

            $num_rec_per_page = $generatePerRequest;
            $lastPage = $this->_getSession()->getData('barcode_ajaxsaveconfigAction_lastPage_data');
            if (!$lastPage) {
                $lastPage = 0;
            }
            $start_from = $lastPage * $num_rec_per_page;
            $this->_getSession()->setData('barcode_ajaxsaveconfigAction_lastPage_data', $lastPage + 1);

            // Count if have not count data
            $totalRow = $this->_getSession()->getData('barcode_ajaxsaveconfigAction_totalRow_data');
            if (!$totalRow) {
                //            $query = SELECT COUNT(*) FROM `catalog_product_entity` WHERE  `type_id` =  'simple'
                $query = "SELECT COUNT(*) FROM `$tableName`";
                $totalRow = $readConnection->fetchOne($query);
                $this->_getSession()->setData('barcode_ajaxsaveconfigAction_totalRow_data', (int)$totalRow);
            }

            //            $query = "SELECT `entity_id` ,  `sku`  FROM $tableName ORDER BY `entity_id` WHERE  `type_id` =  'simple' LIMIT $start_from, $num_rec_per_page";

            $query = "SELECT `entity_id` ,  `sku`  FROM $tableName ORDER BY `entity_id` LIMIT $start_from, $num_rec_per_page";
        } else {
            // Count if have not count data
            $totalRow = $this->_getSession()->getData('barcode_ajaxsaveconfigAction_totalRow_data');

            if (!$totalRow) {
                //            $query = SELECT COUNT(*) FROM `catalog_product_entity` WHERE  `type_id` =  'simple'
                $query = "SELECT COUNT(*) FROM `$tableName`";
                $totalRow = $readConnection->fetchOne($query);
                $this->_getSession()->setData('barcode_ajaxsaveconfigAction_totalRow_data', (int)$totalRow);
            }
            $query = "SELECT `entity_id` ,  `sku`  FROM $tableName ORDER BY `entity_id`";
        }
        $results = $readConnection->fetchAll($query);

        foreach ($results as $_product) {
            $product_id = $_product['entity_id'];
            $product_sku = $_product['sku'];
            $field = '';
            if (intval($conversion == 1)) {
                switch (intval($barcode_field)) {
                    case 0: //Product ID
                        $field = str_pad($product_id, 12, "0", STR_PAD_LEFT);
                        break;
                    case 1: //SKU
                        if (!empty($product_sku)) {
                            $field = substr(number_format(hexdec(substr(md5($product_sku), 0, 16)), 0, "", ""), 0, 12);
                        }

                        break;
                    case 2: //custom field
                        $product = Mage::getModel('catalog/product')->load($product_id);
                        $attr_id = $barcode_source;
                        $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                        // $attr_val = $product->getResource()->getAttribute($attr)->getFrontend()->getValue($product);
                        $store_id = Mage::app()->getStore()->getStoreId();
                        $attr_val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, $attr, $store_id);
                        if (!empty($attr_val)) $field = substr(number_format(hexdec(substr(md5($attr_val), 0, 16)), 0, "", ""), 0, 12);
                        break;

                    /* BEGIN CUSTOM */
                    /**
                     * Add new format of barcodes, generate base on Expired Date and SKU
                     */
                    case 3 :
                        $product = Mage::getModel('catalog/product')->load($product_id);
                        $expired_date = $product->getExpiredDate();
                        $date = date_create($expired_date);
                        $formatStr = 'Ym';
                        $formatDate = date_format($date, $formatStr);
                        /* Format YYYY MM XXXXX */
                        $field = $formatDate . $product_sku;
                        Mage::log($field);
                        break;

                    /* END CUSTOM */
                }
            } else // Conversion: OFF
            {
                $attr_id = Mage::getStoreConfig('barcode/product/barcode_value');
                $attr = Mage::getModel('eav/entity_attribute')->load($attr_id)->getAttributeCode();
                $store_id = Mage::app()->getStore()->getStoreId();
                $attr_val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, $attr, $store_id);
                $field = $attr_val;
            }
            $field = trim($field);

            //EAN13, Conversion OFF
            if ($symbology == 0 && $conversion == 0) {
                //Check $field leng and is number
                if (strlen($field) < 12 && floatval($field) != 0) {
                    //Add prefix 0
                    $field = str_pad($field, 12, "0", STR_PAD_LEFT);
                }
            }

            //EAN13 add 1 digit
            if ($symbology == 0) {
                $helper = Mage::helper('barcode/barcode');
                $helper->addLastDigitForEan13($field);
            }
            //UPC add 1 digit
            if ($symbology == 7) {
                $helper = Mage::helper('barcode/barcode');
                $helper->addLastDigitForEan13($field);
            }
            //Update barcode value
            Mage::getSingleton('catalog/product_action')->updateAttributes(array($product_id), array('sm_barcode' => $field), 0);
        }
        //end foreach
        $numberUpdated = count($results);

        $numberUpdated += $lastPage * $num_rec_per_page;
        if($numberUpdated>$totalRow){
            $numberUpdated=$totalRow;
        }
        $resData = array();
        if ($generatePerRequest > 0 && $numberUpdated !=$totalRow) {
            $resData['message'] = 'Updated ' . $numberUpdated . "/$totalRow";
        } else {
            $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_lastPage_data');
            $this->_getSession()->unsetData('barcode_ajaxsaveconfigAction_totalRow_data');
            $resData['success'] = 'Updated ' . $numberUpdated . "/$totalRow";
            $resData['message'] = 'Done';
        }
        $this->getResponse()->setBody(Mage::helper('barcode')->jsonEncode($resData));

    }

//    }

}
