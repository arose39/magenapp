<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Controller\Adminhtml\Luxurytax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Palamarchuk\LuxuryTax\Model\LuxuryTax;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxFactory;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;


class Save extends Action implements HttpPostActionInterface
{
    private LuxuryTaxRepository $luxuryTaxRepository;
    private LuxuryTaxFactory $luxuryTaxFactory;

    public function __construct(
        Context                 $context,
        LuxuryTaxRepository $luxuryTaxRepository,
        LuxuryTaxFactory $luxuryTaxFactory
    )
    {
        parent::__construct($context);
        $this->luxuryTaxRepository = $luxuryTaxRepository;
        $this->luxuryTaxFactory = $luxuryTaxFactory;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $requestData = $request->getPostValue();
//        $luxuryTaxData = $requestData['general'];
//        $luxuryTaxData['status'] = (bool) $luxuryTaxData['status'];
//        $luxuryTaxData['luxury_tax_amount'] = (int) $luxuryTaxData['luxury_tax_amount'];
//        $luxuryTaxData['tax_rate'] = (int) $luxuryTaxData['tax_rate'];

        if (!$request->isPost() || empty($requestData['general'])) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $resultRedirect->setPath('*/*/new');
            return $resultRedirect;
        }
        try {
            $id = (int)$requestData['general'][LuxuryTax::ID];
            $luxuryTax = $this->luxuryTaxRepository->get($id);
        } catch (\Exception $e) {
            $luxuryTax = $this->luxuryTaxFactory->create();
        }
        $luxuryTax->setName($requestData['general']['name']);
        $luxuryTax->setDescription($requestData['general']['description']);
        $luxuryTax->setStatus($requestData['general']['status']);
        $luxuryTax->setLuxuryTaxAmount($requestData['general']['luxury_tax_amount']);
        $luxuryTax->setTaxRate($requestData['general']['tax_rate']);


//        $luxuryTax->setData($request->getPostValue());
//        $luxuryTax = $this->luxuryTaxRepository->save($luxuryTax);
        try {
            $luxuryTax = $this->luxuryTaxRepository->save($luxuryTax);
            $this->processRedirectAfterSuccessSave($resultRedirect, $luxuryTax->getId());
            $this->messageManager->addSuccessMessage(__('Store location and it work schedul were saved.'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $resultRedirect->setPath('*/*/new');
        }

        return $resultRedirect;
    }

    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, string $id): void
    {
        if ($this->getRequest()->getParam('back')) {
            $resultRedirect->setPath(
                '*/*/edit',
                [
                    'id' => $id,
                    '_current' => true,
                ]
            );
        } elseif ($this->getRequest()->getParam('redirect_to_new')) {
            $resultRedirect->setPath(
                '*/*/new',
                [
                    '_current' => true,
                ]
            );
        } else {
            $resultRedirect->setPath('*/*/');
        }
    }
}
