<?php

namespace Kumar\StoreHealth\Model\Health;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as CronCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class HealthChecker
{
    protected $productCollectionFactory;
    protected $categoryCollectionFactory;
    protected $cacheManager;
    protected $indexerCollectionFactory;
    protected $cronCollectionFactory;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CacheManager $cacheManager,
        IndexerCollectionFactory $indexerCollectionFactory,
        CronCollectionFactory $cronCollectionFactory,
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->cacheManager = $cacheManager;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->cronCollectionFactory = $cronCollectionFactory;
    }

    public function run()
    {
        /*
         * ---------------------------------------------------------
         * PRODUCTS
         * ---------------------------------------------------------
         */

        $totalProducts = $this->productCollectionFactory
            ->create()
            ->getSize();

        $activeProducts = $this->productCollectionFactory
            ->create()
            ->addAttributeToFilter('status', 1)
            ->getSize();

        $disabledProducts = max(
            0,
            $totalProducts - $activeProducts
        );

        $productsWithoutPrice = $this->productCollectionFactory
            ->create()
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter(
                'price',
                ['null' => true]
            )
            ->getSize();

        $productsWithoutUrl = $this->productCollectionFactory
            ->create()
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter(
                'url_key',
                ['null' => true]
            )
            ->getSize();

        /*
         * ---------------------------------------------------------
         * CATEGORIES
         * ---------------------------------------------------------
         */

        $categories = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToSelect('entity_id')
            ->getSize();

        /*
         * ---------------------------------------------------------
         * CACHE
         * ---------------------------------------------------------
         */

        $cacheTypes = $this->cacheManager->getAvailableTypes();
        $cacheStatus = $this->cacheManager->getStatus();

        $cacheEnabled = 0;

        foreach ($cacheTypes as $type) {
            if (!empty($cacheStatus[$type])) {
                $cacheEnabled++;
            }
        }

        /*
         * ---------------------------------------------------------
         * INDEXERS
         * ---------------------------------------------------------
         */

        $indexers = $this->indexerCollectionFactory->create();

        $indexerTotal = 0;
        $indexerWorking = 0;
        $invalidIndexers = [];

        foreach ($indexers as $indexer) {
            $indexerTotal++;

            if ($indexer->getState()->getStatus() === 'valid') {
                $indexerWorking++;
            } else {
                $invalidIndexers[] = $indexer->getTitle();
            }
        }

        /*
         * ---------------------------------------------------------
         * CRON
         * ---------------------------------------------------------
         */

        $pendingCron = $this->cronCollectionFactory
            ->create()
            ->addFieldToFilter(
                'status',
                ['eq' => 'pending']
            )
            ->getSize();

        $failedCron = $this->cronCollectionFactory
            ->create()
            ->addFieldToFilter(
                'status',
                ['eq' => 'error']
            )
            ->getSize();

        /*
         * ---------------------------------------------------------
         * HEALTH CHECKS
         * ---------------------------------------------------------
         */

        $checks = [];

        $checks['products'] = [
            'label' => 'Products',
            'status' => $activeProducts > 0
                ? 'Healthy'
                : 'Critical',
            'value' => $activeProducts,
            'description' => $activeProducts
                . ' active products available'
        ];

        $checks['availability'] = [
            'label' => 'Product Availability',
            'status' => $disabledProducts > 0
                ? 'Attention'
                : 'Healthy',
            'value' => $disabledProducts,
            'description' => $disabledProducts > 0
                ? $disabledProducts . ' products are disabled'
                : 'All products are enabled'
        ];

        $checks['product_data'] = [
            'label' => 'Product Data',
            'status' => (
                $productsWithoutPrice > 0 ||
                $productsWithoutUrl > 0
            )
                ? 'Attention'
                : 'Healthy',
            'value' => $productsWithoutPrice + $productsWithoutUrl,
            'description' => $productsWithoutPrice
                . ' missing price, '
                . $productsWithoutUrl
                . ' missing URL'
        ];

        $checks['categories'] = [
            'label' => 'Categories',
            'status' => $categories > 0
                ? 'Healthy'
                : 'Attention',
            'value' => $categories,
            'description' => $categories
                . ' categories available'
        ];

        $checks['cache'] = [
            'label' => 'Store Cache',
            'status' => $cacheEnabled === count($cacheTypes)
                ? 'Healthy'
                : 'Attention',
            'value' => $cacheEnabled . '/' . count($cacheTypes),
            'description' => $cacheEnabled
                . ' of '
                . count($cacheTypes)
                . ' cache types enabled'
        ];

        $checks['indexers'] = [
            'label' => 'Store Data',
            'status' => $indexerWorking === $indexerTotal
                ? 'Healthy'
                : 'Attention',
            'value' => $indexerWorking . '/' . $indexerTotal,
            'description' => $indexerWorking
                . ' of '
                . $indexerTotal
                . ' data processes are healthy',
            'details' => $invalidIndexers
        ];

        $checks['cron'] = [
            'label' => 'Scheduled Tasks',
            'status' => $failedCron > 0
                ? 'Critical'
                : 'Healthy',
            'value' => $failedCron,
            'description' => $failedCron > 0
                ? $failedCron . ' scheduled tasks failed'
                : 'No failed scheduled tasks'
        ];

        /*
         * ---------------------------------------------------------
         * SCORE
         * ---------------------------------------------------------
         */

        $score = 100;

        if ($activeProducts === 0) {
            $score -= 20;
        }

        if ($disabledProducts > 0) {
            $score -= min(15, $disabledProducts);
        }

        if ($productsWithoutPrice > 0) {
            $score -= 10;
        }

        if ($productsWithoutUrl > 0) {
            $score -= 10;
        }

        if ($categories === 0) {
            $score -= 10;
        }

        if (
            count($cacheTypes) > 0 &&
            $cacheEnabled < count($cacheTypes)
        ) {
            $score -= 10;
        }

        if (
            $indexerTotal > 0 &&
            $indexerWorking < $indexerTotal
        ) {
            $score -= 15;
        }

        if ($failedCron > 0) {
            $score -= 10;
        }

        $score = max(0, $score);

        if ($score >= 90) {
            $status = 'Excellent';
        } elseif ($score >= 75) {
            $status = 'Good';
        } elseif ($score >= 50) {
            $status = 'Warning';
        } else {
            $status = 'Critical';
        }

        /*
         * ---------------------------------------------------------
         * HEALTH DISTRIBUTION
         * ---------------------------------------------------------
         */

        $healthyChecks = 0;
        $attentionChecks = 0;
        $criticalChecks = 0;

        foreach ($checks as $checkKey => $check) {

            $checkStatus = strtolower(
                $check['status'] ?? ''
            );

            if ($checkStatus === 'healthy') {
                $healthyChecks++;
            } elseif ($checkStatus === 'attention') {
                $attentionChecks++;
            } elseif ($checkStatus === 'critical') {
                $criticalChecks++;
            }
        }

        /*
         * ---------------------------------------------------------
         * ATTENTION
         * ---------------------------------------------------------
         */

        $attention = [];

        if ($disabledProducts > 0) {
            $attention[] = [
                'severity' => 'high',
                'title' => 'Products need attention',
                'message' => $disabledProducts
                    . ' products are currently disabled.'
            ];
        }

        if ($productsWithoutPrice > 0) {
            $attention[] = [
                'severity' => 'medium',
                'title' => 'Product information incomplete',
                'message' => $productsWithoutPrice
                    . ' active products do not have a price.'
            ];
        }

        if ($productsWithoutUrl > 0) {
            $attention[] = [
                'severity' => 'medium',
                'title' => 'Product URLs missing',
                'message' => $productsWithoutUrl
                    . ' active products do not have a URL key.'
            ];
        }

        if (
            $indexerTotal > 0 &&
            $indexerWorking < $indexerTotal
        ) {
            $attention[] = [
                'severity' => 'high',
                'title' => 'Store data needs updating',
                'message' => ($indexerTotal - $indexerWorking)
                    . ' data processes are not healthy.'
            ];
        }

        if ($failedCron > 0) {
            $attention[] = [
                'severity' => 'high',
                'title' => 'Scheduled tasks failed',
                'message' => $failedCron
                    . ' scheduled tasks have failed.'
            ];
        }

        /*
         * ---------------------------------------------------------
         * OPPORTUNITIES
         * ---------------------------------------------------------
         */

        $opportunities = [];

        if ($productsWithoutPrice > 0) {
            $opportunities[] = [
                'title' => 'Complete product information',
                'message' => 'Adding missing prices can improve product readiness.'
            ];
        }

        if ($productsWithoutUrl > 0) {
            $opportunities[] = [
                'title' => 'Improve product URLs',
                'message' => 'Complete missing product URLs for better discoverability.'
            ];
        }

        if ($disabledProducts > 0) {
            $opportunities[] = [
                'title' => 'Review unavailable products',
                'message' => 'Review disabled products and bring relevant products back online.'
            ];
        }

        /*
         * ---------------------------------------------------------
         * FINAL RESPONSE
         * ---------------------------------------------------------
         */

        return [
            'score' => $score,

            'status' => $status,

            'summary' => [
                'products' => $activeProducts,
                'categories' => $categories,
                'attention' => count($attention),
                'opportunities' => count($opportunities)
            ],

            'statistics' => [

                'health' => [
                    'healthy' => $healthyChecks,
                    'attention' => $attentionChecks,
                    'critical' => $criticalChecks
                ],

                'products' => [
                    'total' => $totalProducts,
                    'active' => $activeProducts,
                    'disabled' => $disabledProducts,
                    'missing_price' => $productsWithoutPrice,
                    'missing_url' => $productsWithoutUrl
                ],

                'store' => [
                    'categories' => $categories,
                    'cache_enabled' => $cacheEnabled,
                    'cache_total' => count($cacheTypes),
                    'indexers_healthy' => $indexerWorking,
                    'indexers_total' => $indexerTotal,
                    'pending_cron' => $pendingCron,
                    'failed_cron' => $failedCron
                ]

            ],

            'checks' => $checks,

            'attention' => $attention,

            'opportunities' => $opportunities,

            'meta' => [
                'checked_at' => date('Y-m-d H:i:s'),
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'disabled_products' => $disabledProducts,
                'failed_cron' => $failedCron,
                'pending_cron' => $pendingCron
            ]
        ];
    }
}