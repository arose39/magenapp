<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Palamarchuk\TgOrderNotificationBot\Model\ModuleConfig;


class ModuleConfigViewModel implements ArgumentInterface
{
    public function __construct(
        private ModuleConfig $moduleConfig
    ) {
    }

    public function moduleIsEnabled(): bool
    {
        return $this->moduleConfig->moduleIsEnabled();
    }

    public function checkHookIsSet(): bool
    {
        return (bool)$this->moduleConfig->checkHookIsSet();
    }

    public function getHook(): ?string
    {
        return $this->moduleConfig->getHook();
    }

    public function getBotName(): ?string
    {
        return $this->moduleConfig->getBotName();
    }

    public function getAccessToken(): ?string
    {
        return $this->moduleConfig->getAccessToken();
    }
}
