<template>
    <DropdownMenu v-if="localizations?.length">
        <DropdownMenuTrigger as-child>
            <Button variant="outline" class="h-7 gap-1 px-2" aria-label="Switch site">
                <Icon icon="mdi:circle-arrows" class="h-4 w-4" />
                <Badge size="sm" variant="outline" class="uppercase text-muted-foreground">
                    {{ locale }}
                </Badge>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="center">
            <div class="flex flex-col gap-2">
                <template v-for="localization in localizations" :key="localization.site_name">
                    <div class="flex items-center justify-between space-x-2">
                        <Button
                            :as="localization.url ? 'a' : 'div'"
                            :href="localization.url"
                            variant="ghost"
                            :class="[
                                'flex flex-1 items-center justify-start px-4 py-6',
                                !localization.url && 'hover:bg-transparent',
                            ]"
                        >
                            <span class="text-xs">
                                {{ localization.site_name }}
                            </span>
                            <Badge size="sm" variant="outline" class="uppercase text-muted-foreground">
                                {{ localization.short_locale }}
                            </Badge>
                            <StatusBadge :status="localization.status ?? 'missing'" />
                        </Button>

                        <Button
                            variant="ghost"
                            class="ml-auto inline-flex h-10 w-10"
                            :href="localization.edit_url"
                            target="_blank"
                            as="a"
                        >
                            <Icon :icon="!!localization.status ? 'mdi:pencil' : 'mdi:plus-circle'" class="h-3 w-3" />
                        </Button>
                    </div>
                </template>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Icon } from '@iconify/vue'
import type { Entry } from '@types'
import StatusBadge from './StatusBadge.vue'
defineProps<{
    locale: Entry['short_locale']
    localizations: Entry['localizations']
}>()
</script>
