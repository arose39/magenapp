<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ModuleConfig
{
    const CONFIG_MODULE_PATH = 'tg_order_notification_bot';
    const MODULE_ENABLED = 'module_enabled';
    const HOOK_IS_SET = 'hook_is_set';
    const HOOK = 'hook';
    const ACCESS_TOKEN = 'access_token';
    const BOT_NAME = "bot_name";


    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function moduleIsEnabled(): bool
    {
        return (bool)$this->getConfig('general/' . self::MODULE_ENABLED);
    }

    public function checkHookIsSet(): bool
    {
        return (bool)$this->getConfig('general/' . self::HOOK_IS_SET);
    }

    public function getBotName(): ?string
    {
        return $this->getConfig('general/' . self::BOT_NAME);
    }

    public function getHook(): ?string
    {
        return $this->getConfig('general/' . self::HOOK);
    }

    public function getAccessToken(): ?string
    {
        return $this->getConfig('general/' . self::ACCESS_TOKEN);
    }

    private function getConfig(string $config_path): null|string|int
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_MODULE_PATH . '/' . $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
