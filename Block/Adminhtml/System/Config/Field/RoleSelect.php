<?php declare(strict_types=1);

namespace HyTales\OneLinkLogin\Block\Adminhtml\System\Config\Field;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class RoleSelect extends Select
{
    /**
     * @param Context $context
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly RoleCollectionFactory $roleCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setInputName(string $value): self
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $roles = $this->roleCollectionFactory->create()
                ->addFieldToFilter('role_type', Group::ROLE_TYPE);

            foreach ($roles as $role) {
                $this->addOption($role->getId(), $role->getRoleName());
            }
        }

        return parent::_toHtml();
    }
}
