<template>
    <!-- Edit Button -->
    <Button
        v-if="current?.edit_action"
        id="admin-bar__edit"
        as-child
        variant="outline"
        size="icon"
        class="@4xl:w-auto @4xl:px-2"
    >
        <a :href="current?.edit_action?.url" target="_blank">
            <Icon icon="mdi:file-edit" />
            <span class="hidden @4xl:inline">{{ current?.edit_action?.name }}</span>
        </a>
    </Button>

    <!-- Entry Switcher -->
    <EntrySwitcher v-if="entry?.localizations?.length" :localizations="entry.localizations">
        <Button id="admin-bar__switcher" variant="outline" class="h-7 gap-1 px-2" :aria-label="entry.switch_site_label">
            <Icon icon="mdi:circle-arrows" />
            <template v-if="entry.short_site_label">
                {{ entry.short_site_label }}
            </template>
            <Badge v-else size="sm" variant="outline" class="uppercase text-muted-foreground">
                {{ entry.short_locale }}
            </Badge>
        </Button>
    </EntrySwitcher>

    <!-- Publish Toggle -->
    <div v-if="current?.update_action" id="admin-bar__publish" class="flex min-w-32 items-center gap-2 text-sm">
        <Switch :checked="current?.published" @update:checked="handlePublishToggle" />
        <StatusBadge :status="current?.status" :label="current?.localized_status" />
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import type { Entry } from '@/types'
import { Icon } from '@iconify/vue'
import axios from 'axios'
import { computed, onMounted } from 'vue'
import EntrySwitcher from './EntrySwitcher.vue'
import StatusBadge from './StatusBadge.vue'

interface Props {
    entry: Entry
}

const props = defineProps<Props>()

const current = computed(() => {
    return props.entry.localizations?.find((localization) => localization.is_current)
})

onMounted(() => {
    console.log(current.value?.status)
})

const handlePublishToggle = async () => {
    if (!current.value?.update_action) return

    try {
        await axios.put(current.value?.update_action.url, {
            published: !current.value?.published,
        })

        if (current.value) {
            window.location.reload()
        }
    } catch (error) {
        console.error('Failed to toggle publish state:', error)
    }
}
</script>
