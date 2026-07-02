<?php declare(strict_types=1);

namespace HyTales\OneLinkLogin\Controller\Adminhtml\Index;

use HyTales\OneLinkLogin\Model\Config;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NotFoundException;
use Magento\TwoFactorAuth\Api\TfaSessionInterface;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Random\RandomException;

class Index implements ActionInterface, HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly AdminSession $adminSession,
        private readonly BackendUrl $backendUrl,
        private readonly UserFactory $userFactory,
        private readonly UserResource $userResource,
        private readonly UserCollectionFactory $userCollectionFactory,
        private readonly TfaSessionInterface $tfaSession,
        private readonly State $state,
        private readonly Config $config,
        private readonly ResultFactory $resultFactory
    ) {
    }

    /**
     * @return Redirect
     * @throws AlreadyExistsException
     * @throws NotFoundException
     * @throws RandomException
     */
    public function execute(): Redirect
    {
        if ($this->state->getMode() !== State::MODE_DEVELOPER || !$this->config->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $account = $this->config->getAccountByEmail((string)$this->request->getParam('email'));

        if ($account === null) {
            throw new NotFoundException(__('Page not found.'));
        }

        $user = $this->getExistingUser($account['email']);

        if ($user !== null) {
            $this->syncRole($user, $account);
        } else {
            $user = $this->createUser($account);
        }

        $this->adminSession->setUser($user);
        $this->adminSession->processLogin();
        $this->tfaSession->grantAccess();

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->backendUrl->getStartupPageUrl());

        return $resultRedirect;
    }

    /**
     * @param string $email
     *
     * @return DataObject|null
     */
    private function getExistingUser(string $email): ?DataObject
    {
        $user = $this->userCollectionFactory->create()
            ->addFieldToFilter('email', $email)
            ->setPageSize(1)
            ->getFirstItem();

        return $user->getId() ? $user : null;
    }

    /**
     * @param DataObject $user
     * @param array $account
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function syncRole(DataObject $user, array $account): void
    {
        $currentRoleId = (int)$user->getRole()->getId();
        $configuredRoleId = (int)$account['role_id'];

        if ($currentRoleId !== $configuredRoleId) {
            $user->setRoleId($configuredRoleId);
            $this->userResource->save($user);
        }
    }

    /**
     * @param array{label: string, email: string, role_id: int} $account
     *
     * @throws RandomException|AlreadyExistsException
     */
    private function createUser(array $account): User
    {
        $user = $this->userFactory->create();

        $user->setFirstname($account['label'] ?: 'One-Link')
            ->setLastname('Login')
            ->setUsername($account['email'])
            ->setEmail($account['email'])
            ->setPassword(bin2hex(random_bytes(16)))
            ->setIsActive(1)
            ->setRoleId((int)$account['role_id']);

        $this->userResource->save($user);
        $user->isObjectNew(false);

        return $user;
    }
}
