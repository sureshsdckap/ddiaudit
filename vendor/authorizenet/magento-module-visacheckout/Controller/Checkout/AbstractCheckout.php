<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Controller\Checkout;

abstract class AbstractCheckout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \AuthorizeNet\VisaCheckout\Model\Checkout
     */
    protected $checkout;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * AbstractCheckout Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \AuthorizeNet\VisaCheckout\Model\Checkout $checkout
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidato
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \AuthorizeNet\VisaCheckout\Model\Checkout $checkout,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        
        $this->checkoutSession = $checkoutSession;
        $this->checkout = $checkout;
        $this->formKeyValidator = $formKeyValidator;
        
        $this->checkout
            ->setQuote($this->getQuote())
            ->setCustomerSession($customerSession);
        
        parent::__construct($context);
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->getCheckoutSession()->getQuote();
        }
        return $this->quote;
    }

    /**
     * Get checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * Generate initial checkout data
     *
     * Initialize quote state to be valid for one page checkout
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initCheckout()
    {
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize Visa Checkout review.'));
        }
    }
}
