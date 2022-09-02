<?php
/**
 * Copyright © Scherbak Electronics.
 * See no license details.
 */
declare(strict_types=1);

namespace Shch\Lasc\Controller\Login;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Login as Customer storefront login action
 */
class Index implements ActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $customerSession;
    
    /**
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param Session|null $customerSession
     */
    public function __construct(
        ResultFactory $resultFactory,
        RequestInterface $request,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        Session $customerSession = null
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->customerSession = $customerSession ?? ObjectManager::getInstance()->get(Session::class);
    }

    /**
     * Login as Customer storefront login
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $customerId = $this->request->getParam('customer_id');
        try {
            $result = $this->customerSession->loginById($customerId);
            if (false === $result) {
                throw new LocalizedException(__('Login was not successful.'));
            }
//            $customer = $this->customerSession->getCustomer();
//            $this->messageManager->addSuccessMessage(
//                __('You are logged in as customer: %1', $customer->getFirstname() . ' ' . $customer->getLastname())
//            );
//            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
//            $resultPage->getConfig()->getTitle()->set(__('You are logged in'));
            return $resultRedirect->setPath('customer/account/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('/');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('Cannot login to account.'));
            $resultRedirect->setPath('/');
        }
        return $resultRedirect;
    }
}
