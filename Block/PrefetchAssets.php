<?php

namespace MageSuite\AssetsPrefetch\Block;

/**
 * This block only purpose is to add proper tag to identities so full page cache will be cleared when new assets
 * are added for prefetching
 */
class PrefetchAssets extends \Magento\Framework\View\Element\AbstractBlock implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [\MageSuite\AssetsPrefetch\Model\Assets\PrefetchedAssetsCache::CACHE_TAG];
    }

    public function toHtml()
    {
        return '';
    }
}