<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Controller\Adminhtml\Luxurytax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;


class Edit extends Action
{
    private $luxuryTaxRepository;

    public function __construct(
        Context                 $context,
        LuxuryTaxRepository $luxuryTaxRepository
    )
    {
        parent::__construct($context);
        $this->luxuryTaxRepository = $luxuryTaxRepository;
    }

    public function execute(): ResultInterface
    {
        $id = (int) $this->getRequest()->getParam('id');
        try {
            $luxuryTax = $this->luxuryTaxRepository->get($id);

            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->setActiveMenu('Palamarchuk_LuxuryTax::module');
            $resultPage->getConfig()
                ->getTitle()
                ->prepend(__('Edit luxury tax: %tax', ['tax' => $luxuryTax->getName()]));
        } catch (NoSuchEntityException $e) {
            /** @var Redirect $result */
            $result = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(
                __('luxuryTax with id "%value" does not exist.', ['value' => $id])
            );
            $result->setPath('*/*');
        }

        return $resultPage;
    }
}
