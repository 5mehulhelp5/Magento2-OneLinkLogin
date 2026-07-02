<?php declare(strict_types=1);

namespace HyTales\OneLinkLogin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config
{
    private const string XML_PATH_ENABLED = 'admin/onelinklogin/enabled';
    private const string XML_PATH_ACCOUNTS = 'admin/onelinklogin/accounts';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Json $json
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * @return array<int, array{label: string, email: string, role_id: int}>
     */
    public function getAccounts(): array
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_ACCOUNTS);
        if (!$value) {
            return [];
        }

        $accounts = $this->json->unserialize($value);

        return array_values(array_filter((array)$accounts, static fn ($account) => !empty($account['email'])));
    }

    /**
     * @param string $email
     *
     * @return array|null
     */
    public function getAccountByEmail(string $email): ?array
    {
        foreach ($this->getAccounts() as $account) {
            if ($account['email'] === $email) {
                return $account;
            }
        }

        return null;
    }
}
