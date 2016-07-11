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

    public function getAllTypeOptions()
    {
        return array(
            self::ORDER_TYPE => 'Order',
            self::BONUS_TYPE => 'Bonus',
            self::PENALTY_TYPE => 'Penalty'
        );
    }

    public function createBy($order, $type = self::ORDER_TYPE, $comment = '')
    {
        if(!$order->getXposUserId()) {
            return false;
        }

        //get current commit of xpos-user
        $commit = Mage::getModel('alexsales/commit')->getByUser($order->getXposUserId());

        $this->setCommitId($commit->getId());
        $this->setType($type);
        $this->setXposUserId($order->getXposUserId());
        $this->setOrderId($order->getId());
        $this->setComment($comment);

        /** @var Mage_Sales_Model_Order $order */
        $paymentMethod = $order->getPayment()->getMethod();
        $points = $this->getPointsByPayment($paymentMethod);
        $this->setPoints( ($points * $order->getGrandTotal()) / 100 );
        $this->setComment( "A bonus transaction for Order {$order->getIncrementId()}");
        $this->setCreateTime(date(now()));
        $this->save();

        $commit->setBalance($commit->getBalance() + $this->getPoints())->save();
    }


    public function applyToCommit() {
        //get current commit of xpos-user
        $type = $this->getType();

        if($type == self::PENALTY_TYPE) {
            $points = (-1) * $this->getPoints();
        } else {
            $points = $this->getPoints();
        }

        $commit = Mage::getModel('alexsales/commit')->getByUser($this->getXposUserId());
        $commit->setBalance($commit->getBalance() + $points)->save();

        $this->setCommitId($commit->getId())->save();
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