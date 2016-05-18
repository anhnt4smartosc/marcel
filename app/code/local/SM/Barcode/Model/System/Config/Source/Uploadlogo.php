<?php

/*
* Author: truongnq@smartosc.com
* Created date: 27/02/2013
*/

class SM_Barcode_Model_System_Config_Source_Uploadlogo extends Mage_Adminhtml_Model_System_Config_Backend_Image
{
 
 protected function _getAllowedExtensions()
    {
        return array(
            "jpg",
            'png',
            'gif',
        );
    }

}
