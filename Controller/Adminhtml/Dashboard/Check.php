<?php

namespace Kumar\StoreHealth\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Kumar\StoreHealth\Model\Health\HealthChecker;

class Check extends Action
{
    public const ADMIN_RESOURCE = 'Kumar_StoreHealth::storehealth';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var HealthChecker
     */
    protected $healthChecker;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        HealthChecker $healthChecker
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->healthChecker = $healthChecker;
    }

    /**
     * Run health check
     *
     * @return Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            return $result->setData([
                'success' => true,
                'data' => $this->healthChecker->run()
            ]);
        } catch (\Throwable $e) {
            return $result->setHttpResponseCode(500)->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
