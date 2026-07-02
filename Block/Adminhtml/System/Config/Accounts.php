<?php declare(strict_types=1);

namespace HyTales\OneLinkLogin\Block\Adminhtml\System\Config;

use HyTales\OneLinkLogin\Block\Adminhtml\System\Config\Field\RoleSelect;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Accounts extends AbstractFieldArray
{
    private ?RoleSelect $roleRenderer = null;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('label', ['label' => __('Label')]);
        $this->addColumn('email', ['label' => __('Email')]);
        $this->addColumn('role_id', ['label' => __('Role'), 'renderer' => $this->getRoleRenderer()]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Account');
    }

    /**
     * @param DataObject $row
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $roleId = $row->getData('role_id');

        if ($roleId !== null) {
            $options['option_' . $this->getRoleRenderer()->calcOptionHash($roleId)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return RoleSelect
     * @throws LocalizedException
     */
    private function getRoleRenderer(): RoleSelect
    {
        if ($this->roleRenderer === null) {
            $this->roleRenderer = $this->getLayout()->createBlock(
                RoleSelect::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->roleRenderer;
    }
}
