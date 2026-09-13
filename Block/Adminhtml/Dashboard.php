<?php

namespace Kumar\StoreHealth\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Kumar\StoreHealth\Model\Health\HealthChecker;

class Dashboard extends Template
{
    protected $healthChecker;

    public function __construct(
        Context $context,
        HealthChecker $healthChecker,
        array $data = []
    ) {
        $this->healthChecker = $healthChecker;
        parent::__construct($context, $data);
    }

    public function getCheckUrl()
    {
        return $this->getUrl('storehealth/dashboard/check');
    }
}
