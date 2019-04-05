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


    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    )
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
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

        $this->cache->clean([self::CACHE_TAG]);
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