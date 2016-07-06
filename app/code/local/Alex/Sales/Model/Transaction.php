<?php

class Alex_Sales_Model_Transaction extends Mage_Core_Model_Abstract
{
    CONST ORDER_TYPE = 1;
    CONST BONUS_TYPE = 2;
    CONST PENALTY_TYPE = 3;

    protected function _construct()
    {
        $this->_init('alexsales/transaction');
    }


    public function createBy($order, $type = self::ORDER_TYPE, $comment = '')
    {
        //get current commit of xpos-user
        $commit = Mage::getModel('alexsales/commit')->getCollection()
            ->addFieldToFilter('time', array('eq' => date('Y-m-d', strtotime(Mage::getSingleton('core/date')->date('Y-m'))) ))
            ->addFieldToFilter('xpos_user_id', $order->getXposUserId())
            ->getFirstItem();

        if(!$commit->getId()) {
            Mage::throwException("Not found needed commit for order {$order->getIncrementId()}");
        }

        $this->setCommitId($commit->getId());
        $this->setType($type);
        $this->setXposUserId($order->getXposUserId());
        $this->setOrderId($order->getId());
        $this->setComment($comment);

        /** @var Mage_Sales_Model_Order $order */
        $paymentMethod = $order->getPayment()->getMethod();
        $points = $this->getPointsByPayment($paymentMethod);
        $this->setPoints($points);

        $this->setCreateTime(Mage::getSingleton('core/date')->date());
        $this->save();

        $commit->setBalance($commit->getBalance() + $this->getPoints())->save();
    }

    public function getPointsByPayment($paymentMethod)
    {
        $config = Mage::getStoreConfig('alexsales_commits/commit_points/payment_points');
        $data = unserialize($config);

        if(sizeof($data)) {
            foreach ($data as $paymentConfig) {
                if($paymentConfig['method_id'] == $paymentMethod) {
                    return $paymentConfig['points'];
                }
            }
        }

        Mage::log("can't find config for payment $paymentMethod ");
        return 0;
    }
}