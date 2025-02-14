interface Cache {
    name: string
    icon: string
    items: Array<CacheItem>
    urls: {
        stats: string
    }
}

interface CacheItem {
    name: string
    icon: string
    url: string
    method: string
    current?: boolean
}

interface FormattedCacheStats {
    stache: string
    static: string
    images: string
    cache: string
}

export type { Cache, CacheItem, FormattedCacheStats }
