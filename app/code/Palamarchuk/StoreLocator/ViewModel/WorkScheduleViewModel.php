<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Palamarchuk\StoreLocator\Model\WorkSchedule;
use Palamarchuk\StoreLocator\Model\WorkScheduleRepository;

class WorkScheduleViewModel implements ArgumentInterface
{
    public function __construct(
        private WorkScheduleRepository $workScheduleRepository,
        private Http                   $request
    ) {
    }

    public function getStoreLocationWorkSchedule(): ?WorkSchedule
    {
        $storeLocationId = (int)$this->request->get('id');
        try {
            $workSchedule = $this->workScheduleRepository->getByStoreLocationId($storeLocationId);
        } catch (\Exception $exception) {
            return null;
        }

        return $workSchedule;
    }
}
