<?php
/**
 * Created by PhpStorm.
 * User: glenn
 * Date: 2018/8/13
 * Time: 下午 12:59
 */

namespace AstralWeb\ElectronicInvoice\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

class EInvoice extends Action
{

    /** @var string  */
    const E_INVOICE_DOMAIN_TEST = 'https://wwwtest.einvoice.nat.gov.tw/APMEMBERVAN/membercardlogin';

    /** @var string  */
    const E_INVOICE_DOMAIN = 'https://www.einvoice.nat.gov.tw/APMEMBERVAN/membercardlogin';

    /** @var string  */
    const DEFAULT_BACK_URL = 'electronic-invoice/account/einvoice';

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Redirect
     */
    protected $resultRedirect;

    /**
     * Index resultPageFactory
     * @var PageFactory
     */
    protected $resultPageFactory;


    /**
     * Index constructor.
     * @param Session $session
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Session $session,
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->session = $session;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        return parent::__construct($context);
    }

    /**
     * Function execute
     * @return Page|Redirect
     */
    public function execute()
    {
        try {
            $this->resultRedirect = $this->resultRedirectFactory->create();

            $request = $this->_request;

            if ($request->getParams()) {
                if ($request->getParam('rtn_flag')) {
                    return $this->returnResult($request->getParam('rtn_flag'));
                }

                return $this->checkBackToken($request);
            } else {
                $this->customer = $this->session->getCustomer();

                if (!$this->customer->getId()) {
                    return $this->resultRedirect->setPath('customer/account/login');
                }

                $redirectUrl = $this
                    ->setEInvoiceUrl($this->customer->getId());

                return $this->resultRedirect->setUrl($redirectUrl);
            }
        } catch (\Exception $exception) {
            $this->getResponse()->setBody($exception->getMessage());

            return $this->_response->sendResponse();
        }
    }


    /**
     * @param $customerId
     * @return string
     */
    private function setEInvoiceUrl($customerId)
    {
        /** set $eInvoiceUrl */
        if ($this->scopeConfig->getValue(
            'astralweb/e_invoice/general/sandbox',
            ScopeInterface::SCOPE_STORE
        )) {
            $eInvoiceUrl = self::E_INVOICE_DOMAIN_TEST;
        } else {
            $eInvoiceUrl = self::E_INVOICE_DOMAIN;
        }

        /** set back_url */
        if ($this->scopeConfig
            ->getValue('astralweb/e_invoice/general/back_url', ScopeInterface::SCOPE_STORE)
        ) {
            $backUrlParam = urlencode(
                $this->_url->getBaseUrl() . $this->scopeConfig
                    ->getValue('astralweb/e_invoice/general/back_url', ScopeInterface::SCOPE_STORE)
            );
        } else {
            $backUrlParam = urlencode($this->_url->getBaseUrl() . self::DEFAULT_BACK_URL);
        }

        /** set card_ban */
        $cardBanParam = $this->scopeConfig
            ->getValue('astralweb/e_invoice/general/card_ban', ScopeInterface::SCOPE_STORE);

        /** set card_no1 && card_no2 */
        $attributeCode = $this->scopeConfig
            ->getValue('astralweb/e_invoice/general/attribute', ScopeInterface::SCOPE_STORE);
        $cardNo1Param = $cardNo2Param = base64_encode($this->customer->getData($attributeCode));

        /** set card_type */
        $cardTypeParam = base64_encode($this->scopeConfig
            ->getValue('astralweb/e_invoice/general/card_type', ScopeInterface::SCOPE_STORE));


        $tokenParam = $customerId . '_' . md5($attributeCode);

        $card_ban = 'card_ban='.$cardBanParam;
        $card_no1 = 'card_no1='.$cardNo1Param;
        $card_no2 = 'card_no2='.$cardNo2Param;
        $card_type = 'card_type='.$cardTypeParam;
        $back_url = 'back_url='.$backUrlParam;
        $token = 'token='.$tokenParam;

        $url = $eInvoiceUrl.'?'.$card_ban.'&'.$card_no1.'&'.$card_no2.'&'.$card_type.'&'.$back_url.'&'.$token;

        return $url;
    }


    /**
     * @param RequestInterface $request
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function checkBackToken($request)
    {
        $backToken = $request->getParam('token');
        $userIdBase64 = $request->getParam('card_no1');

        $customerID = explode("_", $backToken)[0];
        $md5Account = explode("_", $backToken)[1];

        $customer = $this->customerRepository->getById($customerID);

        $userId = $customer->getCustomAttribute('user_id_str')
            ? $customer->getCustomAttribute('user_id_str')->getValue()
            : ''
        $accountStr =  $customer->getCustomAttribute('account_str')
            ? $customer->getCustomAttribute('account_str')->getValue()
            : ''

        if ($accountStr) {
            if ($md5Account == md5($accountStr) && $userId == base64_decode($userIdBase64)) {
                $this->getResponse()->setBody('Y');
            } else {
                $this->getResponse()->setBody('N');
            }
        } else {
            $this->getResponse()->setBody('N');
        }

        return $this->_response->sendResponse();
    }


    /**
     * @param $returnFlag
     * @return Redirect
     */
    private function returnResult($returnFlag)
    {
        if ($returnFlag == 'Y') {
            $this->messageManager->addSuccessMessage('歸戶成功');
        } else {
            $this->messageManager->addSuccessMessage('歸戶失敗');
        }

        return $this->resultRedirectFactory->create()->setPath('customer/account/edit');
    }
}