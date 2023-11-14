<?php

namespace MageSuite\AssetsPrefetch\Model\Assets;

class PrefetchedAssetsCache
{
    const CACHE_IDENTIFIER = 'prefetched_assets';

    const CACHE_TAG = 'prefetched_assets_tag';

    /**
     * @var string[]|null
     */
    protected $existingAssets = null;

    /**
     * @var string[]
     */
    protected $newAssets = [];

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $cacheContext;


    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Indexer\CacheContext $cacheContext
    )
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->eventManager = $eventManager;
        $this->cacheContext = $cacheContext;
    }

    public function getExistingAssets() {
        if($this->existingAssets === null) {
            $cachedAssets = $this->cache->load(self::CACHE_IDENTIFIER);
            $this->existingAssets = $cachedAssets !== false ? $this->serializer->unserialize($cachedAssets) : [];
        }

        return $this->existingAssets;
    }

    public function addAsset($assetUrl) {
        if(!$this->isMergedAsset($assetUrl)) {
            return;
        }

        if(!in_array($assetUrl, $this->getExistingAssets())) {
            $this->newAssets[] = $assetUrl;
            $this->existingAssets[] = $assetUrl;
        }
    }

    public function updateAssetsCache() {
        $this->cache->save(
            $this->serializer->serialize($this->getExistingAssets()),
            self::CACHE_IDENTIFIER
        );

        $tags = [self::CACHE_TAG];

        $this->cache->clean($tags);
        $this->cacheContext->registerTags($tags);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }

    public function hasNewAssets() {
        return !empty($this->newAssets);
    }

    /**
     * @param $assetUrl
     */
    protected function isMergedAsset($assetUrl)
    {
        return strpos($assetUrl, '_cache/merged') !== false;
    }
}
