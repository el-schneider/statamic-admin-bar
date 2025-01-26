<template>
    <template v-if="data">
        <Menubar class="container fixed left-0 right-0 top-0 z-50 text-xs font-medium shadow-md">
            <!-- Site Actions -->
            <Button variant="ghost" :class="data.site.homeAction.class" as-child>
                <a :href="data.site.homeAction.url" target="_blank">
                    {{ data.site.homeAction.name }}
                </a>
            </Button>

            <!-- Content -->
            <MenubarMenu v-for="content in data.content" :key="content.name">
                <MenubarTrigger>{{ content.name }}</MenubarTrigger>
                <MenubarContent>
                    <MenuTree :items="content.items" />
                </MenubarContent>
            </MenubarMenu>

            <div class="ml-auto flex items-center gap-2">
                <!-- Current Entry Items -->
                <template v-if="data?.entry">
                    <Button as-child variant="ghost" style="--accent: 120, 100%, 75%; --primary-foreground: 0, 0%, 0%">
                        <a :href="data.entry.editAction.url" target="_blank"> Edit </a>
                    </Button>

                    <Switch
                        style="--primary: 120, 100%, 75%; --primary-foreground: 0, 0%, 0%"
                        :checked="data.entry.published"
                        @update:checked="handlePublishToggle"
                    >
                        Published
                    </Switch>
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
import { Switch } from '@/components/ui/switch'
import type { Data } from '@types'
import axios from 'axios'
import { onMounted, ref } from 'vue'
import MenuTree from './MenuTree.vue'

const data = ref<Data | null>(null)

onMounted(async () => {
    try {
        const response = await axios.get(`/!/statamic-admin-bar?uri=${window.location.pathname}`)
        data.value = response.data
        axios.defaults.headers.common['X-CSRF-TOKEN'] = data.value?.csrfToken ?? ''
    } catch (error) {
        console.error('Failed to fetch admin bar data:', error)
    }
})

const handlePublishToggle = async () => {
    if (!data.value?.entry) return

    try {
        const response = await axios.put(data.value.entry.publishAction.url, {
            published: !data.value.entry.published,
        })

        if (data.value?.entry) {
            data.value.entry.published = response.data.data.published
            window.location.reload()
        }
    } catch (error) {
        console.error('Failed to toggle publish state:', error)
    }
}
</script>
