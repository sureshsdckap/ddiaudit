<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Model;

class Webhooks
{
    const NAME_PREFIX = 'm2_';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Client
     */
    protected $client;

    /**
     * Webhooks constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Client $client
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \AuthorizeNet\Webhooks\Model\Client $client
    ) {
    
        $this->storeManager = $storeManager;
        $this->client = $client;
    }

    /**
     * Get webhooks list
     *
     * @return mixed
     */
    public function getWebhooksList()
    {
        $response = $this->client->get();
        if ($response['status'] == 200) {
            return $response['data'];
        }
        return false;
    }

    /**
     * Delete webhooks
     *
     * @return array $result
     */
    public function deleteWebhooks()
    {
        $response = $this->client->get();

        $result = [];

        if ($response['status'] == 200) {
            foreach ($response['data'] as $item) {
                $name = $item->name ?? '';

                if (!$this->isNameMatchingPattern($name)) {
                    continue;
                }

                $result[$item->webhookId] = $this->client->delete($item->webhookId);
            }
        }
        return $result;
    }

    /**
     * Create webhooks
     *
     * @return array
     */
    public function createWebhooks()
    {
        $webhooks = [
            'priorAuthCapture_created' => [
                'endpoint' => 'priorauthcapture',
                'event' => 'priorAuthCapture.created'
            ],
            'refund_created' => [
                'endpoint' => 'refund',
                'event' => 'refund.created'
            ],
            'void_created' => [
                'endpoint' => 'void',
                'event' => 'void.created'
            ],
            'fraud_approved' => [
                'endpoint' => 'fraudapproved',
                'event' => 'fraud.approved'
            ],
            'fraud_declined' => [
                'endpoint' => 'frauddeclined',
                'event' => 'fraud.declined'
            ],
        ];
        $result = [];
        foreach ($webhooks as $name => $data) {
            $content = [
                'name' => $this->generateName($name),
                'url' => $this->storeManager->getStore()->getBaseUrl() . 'rest/V1/anet-webhook/' . $data['endpoint'],
                'eventTypes' => [
                    'net.authorize.payment.' . $data['event']
                ],
                'status' => 'active'
            ];
            $result[$name] = $this->client->post(json_encode($content, JSON_UNESCAPED_SLASHES));
        }
        return $result;
    }

    /**
     * Generate Webhooks name
     *
     * @param $name
     * @return string
     */
    private function generateName($name)
    {
        return self::NAME_PREFIX . $name;
    }

    /**
     * Validate the Webhooks name
     *
     * @param $name
     * @return bool
     */
    private function isNameMatchingPattern($name)
    {
        return (bool)preg_match('/^' . self::NAME_PREFIX . '/', $name);
    }
}
