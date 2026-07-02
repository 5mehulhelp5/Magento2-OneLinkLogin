<?php declare(strict_types=1);

namespace HyTales\OneLinkLogin\Block\Adminhtml\Login;

use HyTales\OneLinkLogin\Model\Config;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\State;

class Link extends Template
{
    public function __construct(
        Context $context,
        private readonly State $state,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _toHtml(): string
    {
        if ($this->state->getMode() !== State::MODE_DEVELOPER || !$this->config->isEnabled() || !$this->getAccounts()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return array<int, array{label: string, email: string, role_id: int}>
     */
    public function getAccounts(): array
    {
        return $this->config->getAccounts();
    }

    public function getAccountUrl(array $account): string
    {
        return $this->getUrl('onelinklogin/index/index', ['email' => $account['email']]);
    }
}
