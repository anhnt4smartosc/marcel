<?php

class Alex_Sales_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('alexsales/adminhtml_transaction'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $model = Mage::getModel('alexsales/transaction');
        $id = $this->getRequest()->getParam('id');

        try{
            if($id) {
                $model->load($id);
                if(!$model->getId()) {
                    Mage::throwException($this->__('transaction is not exist.'));
                }
            }
            Mage::register('current_transaction', $model);

            $this->loadLayout();
            $title = $id ? 'Edit transaction' : 'New transaction';
            $this->_title($this->__($title));
            $this->_addContent($this->getLayout()->createBlock('alexsales/adminhtml_transaction_edit'))
                ->_addLeft($this->getLayout()->createBlock('alexsales/adminhtml_transaction_edit_tabs'));

            $this->renderLayout();
        } catch (Exception $ex) {
            Mage::logException($ex);
            Mage::getSingleton('adminhtml/session')->addError($this->__("Something broken! %s", $ex->getMessage()));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        $params = $this->getRequest()->getParams();

        try {
            /** @var Alex_Sales_Model_Transaction $model */
            $model = Mage::getModel('alexsales/transaction');
            if(isset($params['id']) && $id = $params['id']) {
                $model->load($id);
                if(!$model->getId()) {
                    Mage::throwException($this->__('Commit is not existed.'));
                }
            }

            $model->setXposUserId($params['xpos_user_id']);
            $model->setType($params['type']);
            $model->setComment($params['comment']);
            $model->setPoints($params['points']);

            //Commit, date time, order :
            $model->setPoints($params['points']);
            $model->setCreatedTime(date(now()));

            $model->save();

            //Apply to commit
            $model->applyToCommit();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Commit is saved successfully!'));
        }
        catch (Exception $ex) {
            Mage::logException($ex);
            Mage::getSingleton('adminhtml/session')->addError($this->__("Something broken! %s", $ex->getMessage()));
        }
        $this->_redirect("*/*/index");
    }
}