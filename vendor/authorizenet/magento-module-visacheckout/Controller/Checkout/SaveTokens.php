<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Controller\Checkout;

class SaveTokens extends AbstractCheckout
{
    /**
     * Main action method.
     *
     * Save VC token and quote id and return to response success data.
     *
     * @return \Magento\Framework\Controller\ResultInterface $response
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid form key'));
            }

            $request = $this->getRequest();
            $this->checkout->saveVcTokens(
                $request->getParam('callId'),
                $request->getParam('encKey'),
                $request->getParam('encData')
            );

            if ($this->getQuote()->getId()) {
                $this->getCheckoutSession()->setQuoteId($this->getQuote()->getId());
            }
            
            return $response->setData(['success' => true]);
        } catch (\Magento\Payment\Gateway\Command\CommandException $e) {
            $this->handleError($response, $e, $e->getMessage());
        } catch (\Exception $e) {
            $this->handleError($response, $e, __('Unable to process Visa Checkout tokens. Try again later.'));
        }
        
        return $response;
    }

    /**
     * Handle Error
     *
     * Add exeption error message and set to the response.
     *
     * @param  \Magento\Framework\Controller\Result\Json $response
     * @param  \Exception $e
     * @param  string $message
     */
    protected function handleError(\Magento\Framework\Controller\Result\Json $response, $e, $message)
    {
        $this->messageManager->addExceptionMessage($e, $message);
        $response->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
        $response->setData([
            'message' => $message,
        ]);
    }
}
