<?php

namespace DCKAP\AkeneoSync\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\AbstractModel;


class Log extends AbstractDb
{
    /**
     * This variable contains a DateTime
     *
     * @var DateTime $date
     */
    protected $date;

    /**
     * Construct
     *
     * @param Context $context
     * @param DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        Context $context,
        DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);

        $this->date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dckap_akeneo_connector_import_log', 'log_id');
    }
    /**
     * Process post data before saving
     *
     * @param AbstractModel $object
     *
     * @return \DCKAP\AkeneoSync\Model\ResourceModel\Log
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->gmtDate());
        }

        return parent::_beforeSave($object);
    }

    /**
     * Add step to log
     *
     * @param array $data
     */
    public function addStep(array $data)
    {
        $this->getConnection()->insert($this->getTable('dckap_akeneo_connector_import_log_step'), $data);
    }

    /**
     * Retrieve steps
     *
     * @param int $logId
     *
     * @return array
     */
    public function getSteps($logId)
    {
        /** @var AdapterInterface $connection */
        $connection = $this->getConnection();

        return $connection->fetchAll(
            $connection->select()
                ->from($this->getTable('dckap_akeneo_connector_import_log_step'))
                ->where('log_id = ?', $logId)
                ->order('step_id ASC')
        );
    }

}
