<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Model\Status;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \AuthorizeNet\Webhooks\Model\Webhooks
     */
    protected $webhooks;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\Api\Search\ReportingInterface $reporting
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \AuthorizeNet\Webhooks\Model\Webhooks $webhooks
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Api\Search\ReportingInterface $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \AuthorizeNet\Webhooks\Model\Webhooks $webhooks,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $meta = [],
        array $data = []
    ) {
    
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
        $this->webhooks = $webhooks;
        $this->messageManager = $messageManager;
    }

    /**
     * Get the list of webhooks
     *
     * @return array
     */
    public function getData()
    {
        $result = [];
        $list = $this->webhooks->getWebhooksList();
        if($list == false){
            $this->messageManager->addNoticeMessage(
                __('Unable to load registered webhooks list. Please check your Login ID and Transaction Key.')
            );
            return ['items' => $result];
        }
        foreach ($list as $item) {
            $types = '';
            foreach ($item->eventTypes as $type) {
                $types .= $type . PHP_EOL;
            }
            $result[] = [
                'id_field_name' => 'webhook_id',
                'webhook_id' => $item->webhookId,
                'name' => $item->name ?? '',
                'url' => $item->url ?? '',
                'types' => $types,
                'status' => $item->status ?? ''
            ];
        }
        return ['items' => $result];
    }
}
