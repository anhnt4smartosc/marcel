<?php

class Alex_Import_Block_Adminhtml_Import_Catalog_Preview_Grid extends Mage_Core_Block_Template
{
    public function getAllowedPreviewColumns()
    {
        return array(
            'sku',
            'name',
            '_attribute_set',
            '_type',
            'description',
            'price',
            'cost',
            'expired_date'
        );
    }

    /**
     * Get file that passed the validate
     * @return mixed
     */
    protected function _getCsvFile()
    {
        return Mage::getSingleton('adminhtml/session')->getData('import_uploaded_file');
    }

    /**
     * Bring back the validated ( columns only, not value ) to the preview data
     * @return array
     */
    public function getProductsFromCsv()
    {
        $file = $this->_getCsvFile();
        $format = $this->getAllowedPreviewColumns();
        $result = array();

        if (($handle = fopen("$file", "r")) !== FALSE) {
            $header = fgetcsv($handle, 2000, ",");
            $keys = array_flip($header);

            while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                $productData = array();
                foreach ($format as $column) {
                    $productData[$column] = $data[ $keys[$column] ];
                }

                $result [] = $productData;
            }
            fclose($handle);
        }

        return $result;
    }

    /**
     * Import start action URL.
     * Bring from result block
     *
     * @return string
     */
    public function getImportStartUrl()
    {
        return $this->getUrl('*/*/start');
    }
}