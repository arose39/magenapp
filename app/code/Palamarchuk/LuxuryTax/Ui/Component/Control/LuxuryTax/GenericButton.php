<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Ui\Component\Control\LuxuryTax;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;

abstract class GenericButton
{
    public function __construct(
        private UrlInterface            $urlBuilder,
        private RequestInterface        $request,
        private LuxuryTaxRepository $luxuryTaxRepository
    ) {
    }

    public function getUrl($route = '', $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    public function getLuxuryTaxId(): mixed
    {
        $luxuryTaxId = (int)$this->request->getParam('id');
        if ($luxuryTaxId == null) {
            return 0;
        }
        $luxuryTax = $this->luxuryTaxRepository->get($luxuryTaxId);

        return $luxuryTax->getId() ?: null;
    }
}
