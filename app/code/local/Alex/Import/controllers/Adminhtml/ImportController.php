<?php

require_once 'app/code/core/Mage/ImportExport/controllers/Adminhtml/ImportController.php';

class Alex_Import_Adminhtml_ImportController extends Mage_ImportExport_Adminhtml_ImportController
{
    public function startPreviewAction()
    {
        $this->loadLayout('popup');
        $this->renderLayout();
    }

    /**
     * Validate uploaded files action.
     *
     * @return void
     */
    public function validateAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->loadLayout(false);
            /** @var $resultBlock Mage_ImportExport_Block_Adminhtml_Import_Frame_Result */
            $resultBlock = $this->getLayout()->getBlock('import.frame.result');
            // common actions
            $resultBlock->addAction('show', 'import_validation_container')
                ->addAction('clear', array(
                        Mage_ImportExport_Model_Import::FIELD_NAME_SOURCE_FILE,
                        Mage_ImportExport_Model_Import::FIELD_NAME_IMG_ARCHIVE_FILE)
                );

            try {
                /** @var $import Mage_ImportExport_Model_Import */
                $import = Mage::getModel('importexport/import');

                $sourceFile = $import->setData($data)->uploadSource();

                /** Set sourceFile to session to do the preview */
                Mage::getSingleton('adminhtml/session')->setData('import_uploaded_file', $sourceFile);

                $validationResult = $import->validateSource($sourceFile);

                if (!$import->getProcessedRowsCount()) {
                    $resultBlock->addError($this->__('File does not contain data. Please upload another one'));
                } else {
                    if (!$validationResult) {
                        if ($import->getProcessedRowsCount() == $import->getInvalidRowsCount()) {
                            $resultBlock->addNotice(
                                $this->__('File is totally invalid. Please fix errors and re-upload file')
                            );
                        } elseif ($import->getErrorsCount() >= $import->getErrorsLimit()) {
                            $resultBlock->addNotice(
                                $this->__('Errors limit (%d) reached. Please fix errors and re-upload file', $import->getErrorsLimit())
                            );
                        } else {
                            if ($import->isImportAllowed()) {
                                $resultBlock->addNotice(
                                    $this->__('Please fix errors and re-upload file or simply press "Import" button to skip rows with errors'),
                                    true
                                );
                            } else {
                                $resultBlock->addNotice(
                                    $this->__('File is partially valid, but import is not possible'), false
                                );
                            }
                        }
                        // errors info
                        foreach ($import->getErrors() as $errorCode => $rows) {
                            $error = $errorCode . ' ' . $this->__('in rows:') . ' ' . implode(', ', $rows);
                            $resultBlock->addError($error);
                        }
                    } else {
                        if ($import->isImportAllowed()) {
                            $resultBlock->addSuccess(
                                $this->__('File is valid ( columns only ) ! Please click "Preview" button to see the summary and process. '), true
                            );
                        } else {
                            $resultBlock->addError(
                                $this->__('File is valid, but import is not possible'), false
                            );
                        }
                    }
                    $resultBlock->addNotice($import->getNotices());
                    $resultBlock->addNotice($this->__('Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d', $import->getProcessedRowsCount(), $import->getProcessedEntitiesCount(), $import->getInvalidRowsCount(), $import->getErrorsCount()));
                }
            } catch (Exception $e) {
                $resultBlock->addNotice($this->__('Please fix errors and re-upload file'))
                    ->addError($e->getMessage());
            }
            $this->renderLayout();
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $this->loadLayout(false);
            $resultBlock = $this->getLayout()->getBlock('import.frame.result');
            $resultBlock->addError($this->__('File was not uploaded'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Data is invalid or file is not uploaded'));
            $this->_redirect('*/*/index');
        }
    }
}
