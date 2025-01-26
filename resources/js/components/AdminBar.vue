<template>
    <template v-if="data">
        <Menubar class="container fixed left-0 right-0 top-0 z-50 text-xs font-medium shadow-md">
            <!-- Site Actions -->
            <Button variant="ghost" :class="data.site.homeAction.class" as-child>
                <a :href="data.site.homeAction.url" target="_blank">
                    {{ data.site.homeAction.name }}
                </a>
            </Button>

            <!-- Collections -->
            <MenubarMenu>
                <MenubarTrigger>{{ data.collections.name }}</MenubarTrigger>
                <MenubarContent>
                    <MenuTree :items="data.collections.items" />
                </MenubarContent>
            </MenubarMenu>

            <!-- Taxonomies -->
            <MenubarMenu>
                <MenubarTrigger>{{ data.taxonomies.name }}</MenubarTrigger>
                <MenubarContent>
                    <MenuTree :items="data.taxonomies.items" />
                </MenubarContent>
            </MenubarMenu>

            <div class="ml-auto flex">
                <!-- Current Entry Items -->
                <template v-if="data?.entry">
                    <Button as-child variant="ghost" style="--accent: 120, 100%, 75%; --primary-foreground: 0, 0%, 0%">
                        <a :href="data.entry.editAction.url" target="_blank"> Edit </a>
                    </Button>
                </template>

                <!-- User Menu -->
                <MenubarMenu>
                    <MenubarTrigger>{{ data.user.name }}</MenubarTrigger>
                    <MenubarContent align="end">
                        <MenuTree :items="data.user.items" />
                    </MenubarContent>
                </MenubarMenu>
            </div>
        </Menubar>
    </template>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Menubar, MenubarContent, MenubarMenu, MenubarTrigger } from '@/components/ui/menubar'
import { onMounted, ref } from 'vue'
import type { ItemsData } from '../types/data'
import MenuTree from './MenuTree.vue'

const data = ref<ItemsData | null>(null)

onMounted(async () => {
    try {
        const response = await fetch(`/!/statamic-admin-bar?uri=${window.location.pathname}`)
        if (!response.ok) throw new Error('Network response was not ok')
        data.value = await response.json()
    } catch (error) {
        console.error('Failed to fetch admin bar data:', error)
    }
})
</script>

<style scoped></style>
