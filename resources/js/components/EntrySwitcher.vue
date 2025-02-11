<template>
    <DropdownMenu v-if="localizations?.length">
        <DropdownMenuTrigger as-child>
            <Button id="admin-bar__switcher" variant="outline" class="h-7 gap-1 px-2" :aria-label="label">
                <Icon icon="mdi:circle-arrows" />
                <Badge size="sm" variant="outline" class="uppercase text-muted-foreground">
                    {{ locale }}
                </Badge>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="center">
            <div class="flex flex-col gap-1">
                <template v-for="localization in localizations" :key="localization.site_name">
                    <DropdownMenuItem class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <div>{{ localization.site_name }}</div>
                            <Badge size="sm" variant="outline" class="uppercase text-muted-foreground">
                                {{ localization.short_locale }}
                            </Badge>
                            <StatusBadge :status="localization.status ?? 'missing'" />
                        </div>

                        <div class="ml-auto flex gap-1">
                            <Button
                                v-if="localization.url"
                                variant="outline"
                                size="icon"
                                :as="localization.url ? 'a' : 'div'"
                                :href="localization.url"
                                class="h-7 w-7"
                                :class="!localization.url && 'opacity-50'"
                            >
                                <Icon icon="mdi:circle-arrows" class="h-3 w-3" />
                            </Button>

                            <Button variant="outline" size="icon" :href="localization.edit_url" target="_blank" as="a">
                                <Icon :icon="!!localization.status ? 'mdi:pencil' : 'mdi:plus-circle'" />
                            </Button>
                        </div>
                    </DropdownMenuItem>
                </template>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Icon } from '@iconify/vue'
import type { Entry } from '@types'
import StatusBadge from './StatusBadge.vue'
defineProps<{
    label: Entry['switch_site']
    locale: Entry['short_locale']
    localizations: Entry['localizations']
}>()
</script>
