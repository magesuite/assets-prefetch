<?php

namespace MageSuite\AssetsPrefetch\Plugin\Framework\View\Page\Config\Renderer;

class AddPrefetchedAssets
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Asset\MergeService
     */
    protected $mergeService;

    /**
     * @var \MageSuite\AssetsPrefetch\Model\Assets\PrefetchedAssetsCache
     */
    protected $prefetchedAssetsCache;

    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Asset\MergeService $mergeService,
        \MageSuite\AssetsPrefetch\Model\Assets\PrefetchedAssetsCache $prefetchedAssetsCache
    )
    {
        $this->pageConfig = $pageConfig;
        $this->mergeService = $mergeService;
        $this->prefetchedAssetsCache = $prefetchedAssetsCache;
    }

    public function afterRenderHeadContent(\Magento\Framework\View\Page\Config\Renderer $subject, $result)
    {
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
                $this->prefetchedAssetsCache->addAsset($asset->getUrl());
            }
        }

        if($this->prefetchedAssetsCache->hasNewAssets()) {
            $this->prefetchedAssetsCache->updateAssetsCache();
        }

        $renderedPrefetchAssets = $this->renderPrefetchAssets();

        return $result . $renderedPrefetchAssets;
    }

    /**
     * @return string
     */
    protected function renderPrefetchAssets()
    {
        $output = PHP_EOL;

        foreach ($this->prefetchedAssetsCache->getExistingAssets() as $assetUrl) {
            $output .= sprintf('<link rel="prefetch" as="style" href="%s">', $assetUrl) . PHP_EOL;
        }

        return $output;
    }

}