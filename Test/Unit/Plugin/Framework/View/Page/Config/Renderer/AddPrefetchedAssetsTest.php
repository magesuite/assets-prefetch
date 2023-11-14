<?php

namespace MageSuite\AssetsPrefetch\Test\Unit\Plugin\Framework\View\Page\Config\Renderer;

class AddPrefetchedAssetsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\MageSuite\AssetsPrefetch\Model\Assets\PrefetchedAssetsCache
     */
    protected $prefetchedAssetsCache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\MageSuite\AssetsPrefetch\Service\AssetsProvider
     */
    protected $assetsProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\MageSuite\AssetsPrefetch\Plugin\Framework\View\Page\Config\Renderer\AddPrefetchedAssets
     */
    protected $addPrefetchedAssets;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Page\Config\Renderer
     */
    protected $pageConfigRenderer;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->assetsProvider = $this->getMockBuilder(\MageSuite\AssetsPrefetch\Service\AssetsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->prefetchedAssetsCache = $this->getMockBuilder(\MageSuite\AssetsPrefetch\Model\Assets\PrefetchedAssetsCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addPrefetchedAssets = $this->objectManager->create(
            \MageSuite\AssetsPrefetch\Plugin\Framework\View\Page\Config\Renderer\AddPrefetchedAssets::class,
            ['assetsProvider' => $this->assetsProvider, 'prefetchedAssetsCache' => $this->prefetchedAssetsCache]
        );

        $this->pageConfigRenderer = $this->getMockBuilder(\Magento\Framework\View\Page\Config\Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache = $this->objectManager->create(\Magento\Framework\App\CacheInterface::class);
        $cache->clean();
    }

    public function testItDoesNotAddAssetsWhenAssetProviderReturnedEmptyArray() {
        $this->assetsProvider->method('getCurrentPageAssetsUrls')
            ->willReturn([]);

        $this->prefetchedAssetsCache->method('getExistingAssets')
            ->willReturn([]);

        $result = $this->addPrefetchedAssets->afterRenderHeadContent($this->pageConfigRenderer, '<link rel="stylesheet" href="https://example.com">');

        $this->assertEquals('<link rel="stylesheet" href="https://example.com">', $result);
    }

    public function testItAddsMultipleAssetsCorrectly() {
        $assets = [
            'http://localhost/static/_cache/merged/asset.js',
            'http://localhost/static/_cache/merged/second_asset.js',
        ];

        $this->assetsProvider->method('getCurrentPageAssetsUrls')
            ->willReturn($assets);
        $this->prefetchedAssetsCache->method('getExistingAssets')
            ->willReturn($assets);

        $result = $this->addPrefetchedAssets->afterRenderHeadContent($this->pageConfigRenderer, '<link rel="stylesheet" href="https://example.com">');

        $this->assertEquals('<link rel="stylesheet" href="https://example.com">
<link rel="prefetch" as="style" href="http://localhost/static/_cache/merged/asset.js">
<link rel="prefetch" as="style" href="http://localhost/static/_cache/merged/second_asset.js">
', $result);
    }
}
