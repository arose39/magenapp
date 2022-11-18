<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Controller\Adminhtml\Luxurytax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;


class Delete extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    private LuxuryTaxRepository $luxuryTaxRepository;

    public function __construct(
        Context $context,
        LuxuryTaxRepository $luxuryTaxRepository,
    ) {
        parent::__construct($context);
        $this->luxuryTaxRepository = $luxuryTaxRepository;
    }

    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $luxuryTaxId = (int)$this->getRequest()->getParam('id');

        if (!$luxuryTaxId) {
            $this->messageManager->addErrorMessage(__('Error.'));

            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $luxuryTax = $this->luxuryTaxRepository->get($luxuryTaxId);
            $this->luxuryTaxRepository->delete($luxuryTax);
            $this->messageManager->addSuccessMessage(__('You deleted the luxury tax'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Cannot delete luxury tax'));
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
