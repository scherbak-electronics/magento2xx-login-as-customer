<?php
/**
 * Copyright © Scherbak Electronics.
 * See no license details.
 */
declare(strict_types=1);

namespace Shch\Lasc\Ui\Customer\Component\Control;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Login as Customer button UI component.
 */
class LoginAsCustomerButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context, $registry);
    }

    /**
     * @inheritdoc
     */
    public function getButtonData(): array
    {
        $customerId = (int)$this->getCustomerId();
        $data = [];
        if ($customerId) {
            $href = sprintf("location.href = '%s';", $this->getLoginUrl($customerId));
            $data = [
                'label' => __('Login as Customer'),
                'class' => 'login login-button',
                'on_click' => $href,
                'sort_order' => 60,
            ];
        }
        return $data;
    }

    private function getLoginUrl(int $customerId): string
    {
        return $this->urlBuilder->getUrl('loginascustomer/login/login', ['customer_id' => $customerId]);
    }
}
