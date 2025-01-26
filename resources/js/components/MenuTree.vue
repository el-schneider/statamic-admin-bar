<template>
    <div>
        <template v-for="item in items" :key="'item' in item ? item.name : 'divider'">
            <MenubarSeparator v-if="item.type === 'divider'" />

            <template v-else-if="'items' in item && item.items?.length">
                <MenubarSub>
                    <MenubarSubTrigger>{{ item.name }}</MenubarSubTrigger>
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
                            'flex w-full items-center gap-2 px-2 py-1.5 text-sm outline-none',
                            'hover:bg-accent hover:text-accent-foreground',
                            item.class,
                        ]"
                        target="_blank"
                        :data-method="item.type"
                    >
                        {{ item.name }}
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
import type { Item } from '../types/item'

defineProps<{
    items: Item[]
}>()
</script>
