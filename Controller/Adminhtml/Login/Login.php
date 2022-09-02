<?php
/**
 * Copyright © Scherbak Electronics.
 * See no license details.
 */
declare(strict_types=1);

namespace Shch\Lasc\Controller\Adminhtml\Login;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Login as customer action
 */
class Login extends Action implements ActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Shch_Lasc::login';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Url
     */
    private $url;


    private $scopeConfig;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param Context $context
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param Url $url
     * @param ScopeConfigInterface $scopeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        StoreCookieManagerInterface $storeCookieManager,
        CustomerRepositoryInterface $customerRepository,
        Url $url,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->url = $url;
        $this->scopeConfig = $scopeConfig;
        $this->storeCookieManager = $storeCookieManager;
    }

    /**
     * Login as customer
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        $messages = [];
        $customerId = (int)$this->_request->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int)$this->_request->getParam('entity_id');
            if (empty($customerId)) {
                $messages[] = __('Please select a Customer to login.');
                return $this->prepareJsonResult($messages);
            }
        }
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            $messages[] = __('Customer with this ID no longer exists.');
            return $this->prepareJsonResult($messages);
        }
//        $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
//        $storeId = $this->scopeConfig->getValue('dev/lasc/store');
        $storeId = $this->scopeConfig->getValue('dev/lasc/store');
//        $storeId = (int)$customer->getStoreId();
        if (empty($storeId)) {
            $messages[] = __('Please select a Store View to login in.');
            return $this->prepareJsonResult($messages);
        }
        $redirectUrl = $this->getLoginProceedRedirectUrl($customerId, $storeId);
        $res = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $res->setUrl($redirectUrl);
    }

    /**
     * Get login proceed redirect url
     *
     * @param string $customerId
     * @param int $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getLoginProceedRedirectUrl(int $customerId, string $storeId): string
    {
        $targetStore = $this->storeManager->getStore($storeId);
        $queryParameters = ['customer_id' => $customerId];
        $redirectUrl = $this->url
            ->setScope($targetStore)
            ->getUrl('loginascustomer/login/index', ['_query' => $queryParameters, '_nosid' => true]);

        $redirectUrl = $this->switch($targetStore, $redirectUrl);
        return $redirectUrl;
    }

    public function switch(StoreInterface $targetStore, string $redirectUrl): string
    {
        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        if ($defaultStoreView !== null) {
            if ($defaultStoreView->getId() === $targetStore->getId()) {
                $this->storeCookieManager->deleteStoreCookie($targetStore);
            } else {
                $this->httpContext->setValue(Store::ENTITY, $targetStore->getCode(), $defaultStoreView->getCode());
                $this->storeCookieManager->setStoreCookie($targetStore);
            }
        }

        return $redirectUrl;
    }

    /**
     * Prepare JSON result
     *
     * @param array $messages
     * @param string|null $redirectUrl
     * @return JsonResult
     */
    private function prepareJsonResult(array $messages, ?string $redirectUrl = null)
    {
        /** @var JsonResult $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $jsonResult->setData([
            'redirectUrl' => $redirectUrl,
            'messages' => $messages,
        ]);

        return $jsonResult;
    }
}
