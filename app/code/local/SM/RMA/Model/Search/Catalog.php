<?php

class SM_RMA_Model_Search_Catalog extends Mage_Adminhtml_Model_Search_Catalog {

    /**
     * Load search results
     *
     * @return Mage_Adminhtml_Model_Search_Catalog
     */
    public function load() {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }

        $collection = Mage::helper('catalogsearch')->getQuery()->getSearchCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', 'simple')
            ->addTaxPercents()
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->addSearchFilter($this->getQuery())
            ->load();


        foreach ($collection as $product) {
            if($product->isInStock() && $product->isSaleable())
            {
                $description = strip_tags($product->getDescription());
                $arr[] = array(
                    'id' => $product->getId(),
                    'type' => Mage::helper('adminhtml')->__('Product'),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'price' => $product->getPrice(),
                    'description' => Mage::helper('core/string')->substr($description, 0, 30),
                    'url' => Mage::helper('adminhtml')->getUrl('*/catalog_product/edit', array('id' => $product->getId())),
                );
            }
        }

        $this->setResults($arr);
        return $this;
    }

    public function loadAll($limit, $page) {
        $arr = array();
        $collection = //Mage::helper('catalogsearch')->getQuery()->getSearchCollection()
                Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
        ->addTaxPercents();

        $collection = $collection->setCurPage($page)
                        ->setPageSize($limit)->load();
        // not needed since we checked with JS
        $t = ceil($collection->getSize() / $limit);
        if ($page > $t)
            return null;
/*
        if ($collection->requireTaxPercent()) {

            $request = Mage::getSingleton('tax/calculation')->getRateRequest();
            foreach ($collection as $item) {
                if (null === $item->getTaxClassId()) {
                    $item->setTaxClassId($item->getMinimalTaxClassId());
                }
                if (!isset($classToRate[$item->getTaxClassId()])) {
                    $request->setProductClassId($item->getTaxClassId());
                    $classToRate[$item->getTaxClassId()] = Mage::getSingleton('tax/calculation')->getRate($request);
                }
                $item->setTaxPercent($classToRate[$item->getTaxClassId()]);
            }
        }*/
        foreach ($collection as $product) {
            $description = strip_tags($product->getDescription());
            // Get the product's tax class' ID
            $taxClassId = $product->getData("tax_class_id");
            // Get the tax rates of each tax class in an associative array
            $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
            // Extract the tax rate from the array
            $taxRate = $taxClasses["value_" . $taxClassId];
            $arr[] = array(
                'id' => $product->getId(),
                'type' => Mage::helper('adminhtml')->__('Product'),
                'name' => $product->getName(),
                'tax' => $taxRate, //$product->getTaxPercent(),
                'sku' => $product->getSku(),
                'price' => $product->getPrice(),
                'description' => Mage::helper('core/string')->substr($description, 0, 30),
                'url' => Mage::helper('adminhtml')->getUrl('*/catalog_product/edit', array('id' => $product->getId())),
            );
        }

        $this->setResults($arr);

        return $this;
    }

}
