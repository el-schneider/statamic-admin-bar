<template>
    <DropdownMenu v-if="cache" id="admin-bar__cache">
        <DropdownMenuTrigger as-child class="@4xl:w-auto @4xl:px-2" @mouseenter="fetchStats">
            <Button variant="outline" size="icon" class="h-7 w-7">
                <Icon :icon="cache.icon" />
                <span class="hidden @4xl:inline">{{ cache.name }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="center">
            <DropdownMenuItem v-for="item in cache.items" :key="item.name">
                <div class="flex items-center gap-1">
                    <Icon v-if="item.icon" :icon="item.icon" class="mr-1 h-4 w-4" />
                    <div>
                        <div>{{ item.name }}</div>
                        <div v-if="stats" class="text-xs text-muted-foreground">
                            <template v-if="item.url.includes('stache')">
                                {{ stats.stache }}
                            </template>
                            <template v-else-if="item.url.includes('static')">
                                {{ stats.static }}
                            </template>
                            <template v-else-if="item.url.includes('image')">
                                {{ stats.images }}
                            </template>
                            <template v-else-if="item.url.includes('application')">
                                {{ stats.cache }}
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
                    <Button size="icon" variant="destructiveOutline" @click="handleCacheAction(item)">
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
import type { Cache, CacheItem, FormattedCacheStats } from '@/types/cache'
import { Icon } from '@iconify/vue'
import axios from 'axios'
import { ref } from 'vue'

const { addDeferredToast } = useDeferredToast()

const props = defineProps<{
    cache?: Cache
}>()

const stats = ref<FormattedCacheStats | null>(null)
const hasFetchedStats = ref(false)

const fetchStats = async () => {
    if (!props.cache?.urls.stats || hasFetchedStats.value) return

    try {
        const { data } = await axios.get<FormattedCacheStats>(props.cache.urls.stats)
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
