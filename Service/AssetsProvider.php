<?php

namespace MageSuite\AssetsPrefetch\Service;

class AssetsProvider
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Asset\MergeService
     */
    protected $mergeService;

    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Asset\MergeService $mergeService
    )
    {
        $this->pageConfig = $pageConfig;
        $this->mergeService = $mergeService;
    }

    public function getCurrentPageAssetsUrls()
    {
        $assets = [];

        foreach ($this->pageConfig->getAssetCollection()->getGroups() as $group) {
            $groupAssets = $group->getAll();

            if (!$group->getProperty(\Magento\Framework\View\Asset\GroupedCollection::PROPERTY_CAN_MERGE)) {
                continue;
            }

            if ($group->getProperty(\Magento\Framework\View\Asset\GroupedCollection::PROPERTY_CONTENT_TYPE) !== 'css') {
                continue;
            }

            $mergedAssets = $this->mergeService->getMergedAssets(
                $groupAssets,
                $group->getProperty(\Magento\Framework\View\Asset\GroupedCollection::PROPERTY_CONTENT_TYPE)
            );

            foreach ($mergedAssets as $asset) {
                $assets[] = $asset->getUrl();
            }
        }

        return $assets;
    }
}