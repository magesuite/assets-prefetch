<?php

namespace MageSuite\AssetsPrefetch\Plugin\Framework\View\Page\Config\Renderer;

class AddPrefetchedAssets
{
    /**
     * @var \MageSuite\AssetsPrefetch\Service\AssetsProvider
     */
    protected $assetsProvider;

    /**
     * @var \MageSuite\AssetsPrefetch\Model\Assets\PrefetchedAssetsCache
     */
    protected $prefetchedAssetsCache;

    public function __construct(
        \MageSuite\AssetsPrefetch\Service\AssetsProvider $assetsProvider,
        \MageSuite\AssetsPrefetch\Model\Assets\PrefetchedAssetsCache $prefetchedAssetsCache
    )
    {
        $this->prefetchedAssetsCache = $prefetchedAssetsCache;
        $this->assetsProvider = $assetsProvider;
    }

    public function afterRenderHeadContent(\Magento\Framework\View\Page\Config\Renderer $subject, $result)
    {
        $assets = $this->assetsProvider->getCurrentPageAssetsUrls();

        if(empty($assets)) {
            return $result;
        }

        foreach($assets as $assetUrl) {
            $this->prefetchedAssetsCache->addAsset($assetUrl);
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
