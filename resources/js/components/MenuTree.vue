<template>
    <div>
        <template v-for="item in items" :key="'item' in item ? item.name : 'divider'">
            <MenubarSeparator v-if="item.type === 'divider'" />

            <template v-else-if="'items' in item && item.items?.length">
                <MenubarSub>
                    <MenubarSubTrigger>
                        <Icon v-if="item.icon" :icon="item.icon" class="mr-2 h-4 w-4" />
                        {{ item.name }}
                    </MenubarSubTrigger>
                    <MenubarSubContent :side-offset="8">
                        <MenuTree :items="item.items" />
                    </MenubarSubContent>
                </MenubarSub>
            </template>

            <template v-else>
                <MenubarItem>
                    <a
                        :href="item.url"
                        :class="[
                            'flex w-full items-center text-sm outline-none',
                            'hover:bg-accent hover:text-accent-foreground',
                            item.class,
                        ]"
                        target="_blank"
                        :data-method="item.type"
                    >
                        <Icon v-if="item.icon" :icon="item.icon" class="mr-2 h-4 w-4" />
                        <span>{{ item.name }}</span>
                    </a>
                </MenubarItem>
            </template>
        </template>
    </div>
</template>

<script setup lang="ts">
import {
    MenubarItem,
    MenubarSeparator,
    MenubarSub,
    MenubarSubContent,
    MenubarSubTrigger,
} from '@/components/ui/menubar'
import { Icon } from '@iconify/vue'
import type { Item } from '@types'

defineProps<{
    items: Item[]
}>()
</script>
