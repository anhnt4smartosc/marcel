<?php

class Alex_Sales_Adminhtml_CommitController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('alexsales/adminhtml_commit'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $model = Mage::getModel('alexsales/commit');
        $id = $this->getRequest()->getParam('id');

        try{
            if($id) {
                $model->load($id);
                if(!$model->getId()) {
                    Mage::throwException($this->__('Commit is not exist.'));
                }
            }
            Mage::register('current_commit', $model);

            $this->loadLayout();
            $title = $id ? 'Edit Commit' : 'New Commit';
            $this->_title($this->__($title));
            $this->_addContent($this->getLayout()->createBlock('alexsales/adminhtml_commit_edit'))
                ->_addLeft($this->getLayout()->createBlock('alexsales/adminhtml_commit_edit_tabs'));

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
            $model = Mage::getModel('alexsales/commit');
            if(isset($params['id']) && $id = $params['id']) {
                $model->load($id);
                if(!$model->getId()) {
                    Mage::throwException($this->__('Commit is not existed.'));
                }
            }

            $model->setxpos_user_id($params['xpos_user_id']);
            $model->setBalance($params['balance']);
            $model->setPoints($params['points']);
            $model->setTime($params['time']);

            $model->save();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Commit is saved successfully!'));

            $this->_redirect('*/*/edit', array(
                '_current'=> true,
                'id' => $model->getId()
            ));
        }
        catch (Exception $ex) {
            Mage::logException($ex);
            Mage::getSingleton('adminhtml/session')->addError($this->__("Something broken! %s", $ex->getMessage()));
            $action = isset($params['id']) ? 'edit' : 'new';
            $this->_redirect("*/*/$action", array(
                '_current'=>true
            ));
        }
    }

    public function massDeleteAction()
    {
        $params = $this->getRequest()->getParams();

        try {
            $model = Mage::getModel('alexsales/commit');

            if(isset($params['ids']) && $ids = $params['ids']) {
                foreach ($ids as $id) {
                    $model->setId($id)->delete();
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Deleted successfully!'));
        }
        catch (Exception $ex) {
            Mage::logException($ex);
            Mage::getSingleton('adminhtml/session')->addError($this->__("Something broken! %s", $ex->getMessage()));
        }

        $this->_redirect("*/*/index");
    }

    public function exportCsvAction()
    {
        $fileName   = 'commit.csv';
        $content    = $this->getLayout()->createBlock('alexsales/adminhtml_commit_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {

        $fileName   = 'commit.xml';
        $content    = $this->getLayout()->createBlock('alexsales/adminhtml_commit_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }
}