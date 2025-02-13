<template>
    <DropdownMenu v-if="cache" id="admin-bar__cache">
        <DropdownMenuTrigger as-child @mouseenter="fetchStats">
            <Button variant="outline" size="icon" class="h-7 w-7">
                <Icon :icon="cache.icon" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="center">
            <DropdownMenuItem
                v-for="item in cache.items"
                :key="item.name"
                class="flex items-center justify-start gap-4"
            >
                <div class="flex items-center">
                    <Icon v-if="item.icon" :icon="item.icon" class="mr-2 h-4 w-4" />
                    <div>
                        <div>{{ item.name }}</div>
                        <div v-if="stats" class="text-xs text-muted-foreground">
                            <template v-if="item.url.includes('stache')">
                                {{ stats.stache.records }} records, {{ stats.stache.size }}
                            </template>
                            <template v-else-if="item.url.includes('static')">
                                {{ stats.static.count }} pages cached
                            </template>
                            <template v-else-if="item.url.includes('image')">
                                {{ stats.images.count }} files, {{ stats.images.size }}
                            </template>
                            <template v-else-if="item.url.includes('application')">
                                Driver: {{ stats.cache.driver }}
                            </template>
                        </div>
                    </div>
                </div>
                <div class="ml-auto flex gap-1">
                    <Button
                        v-if="item.url.includes('static')"
                        size="icon"
                        variant="outline"
                        @click="handleCacheAction({ ...item, current: true })"
                    >
                        <Icon icon="mdi:restore-from-trash" />
                    </Button>
                    <Button size="icon" variant="outline" @click="handleCacheAction(item)">
                        <Icon icon="mdi:trash" />
                    </Button>
                </div>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { useDeferredToast } from '@/composables/useDeferredToast'
import { Icon } from '@iconify/vue'
import axios from 'axios'
import { ref } from 'vue'

const { addDeferredToast } = useDeferredToast()

interface CacheStats {
    stache: {
        records: number
        size: string
        time: string
        rebuilt: string
    }
    cache: {
        driver: string
    }
    static: {
        enabled: boolean
        strategy: string
        count: number
    }
    images: {
        count: number
        size: string
    }
}

interface CacheItem {
    name: string
    icon: string
    url: string
    method: string
    current?: boolean
}

interface CacheData {
    name: string
    icon: string
    urls: {
        stats: string
    }
    items: CacheItem[]
}

const props = defineProps<{
    cache?: CacheData
}>()

const stats = ref<CacheStats | null>(null)
const hasFetchedStats = ref(false)

const fetchStats = async () => {
    if (!props.cache?.urls.stats || hasFetchedStats.value) return

    try {
        const { data } = await axios.get<CacheStats>(props.cache.urls.stats)
        stats.value = data
        hasFetchedStats.value = true
    } catch (error) {
        console.error('Failed to fetch cache stats:', error)
    }
}

const handleCacheAction = async (item: CacheItem) => {
    try {
        const { data } = await axios({
            method: item.method.toLowerCase(),
            url: item.url,
            params: item.current ? { url: window.location.pathname } : undefined,
        })

        addDeferredToast({
            description: data.message,
            variant: 'success',
        })

        window.location.reload()
    } catch (error) {
        console.error('Failed to clear cache:', error)
    }
}
</script>
