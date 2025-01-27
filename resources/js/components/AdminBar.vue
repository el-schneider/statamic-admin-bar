<template>
    <div :class="{'dark': preferences.darkMode}" class="contents">
        <Menubar>
            <!-- Site Switcher -->
            <SiteSwitcher v-if="sites.length > 1" :sites="sites" />

            <!-- Site Actions -->
            <Button variant="ghost" :class="data.site.homeAction.class" as-child>
                <a :href="data.site.homeAction.url" target="_blank">
                    <Icon icon="mdi-light:home" class="h-4 w-4" />
                    {{ data.site.homeAction.name }}
                </a>
            </Button>

            <!-- Rest of the existing template... -->

            <!-- Dark Mode Toggle -->
            <Toggle
                    :pressed="preferences.darkMode"
                    @update:pressed="toggleDarkMode"
                    variant="outline"
                    size="sm"
                >
                    <Icon :icon="preferences.darkMode ? 'mdi-light:weather-night' : 'mdi-light:weather-sunny'" class="h-4 w-4" />
                </Toggle>

            <div class="ml-auto flex items-center gap-2">
                <!-- Current Entry Items -->
                <template v-if="data.entry?.editAction">
                    <Button as-child variant="ghost" style="--accent: 120, 100%, 75%; --primary-foreground: 0, 0%, 0%">
                        <a :href="data.entry.editAction.url" target="_blank">
                            <Icon icon="mdi-light:pencil" class="h-4 w-4" />
                            {{ data.entry.editAction.name }}
                        </a>
                    </Button>
                </template>

                <template v-if="data.entry?.publishAction">
                    <div class="flex min-w-36 items-center gap-2 text-sm">
                        <Switch
                            style="--primary: 120, 100%, 75%; --primary-foreground: 0, 0%, 0%"
                            :checked="data.entry.published"
                            @update:checked="handlePublishToggle"
                        >
                        </Switch>
                        {{ data.entry.published ? 'Published' : 'Unpublished' }}
                    </div>
                </template>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Badge
                        variant="outline"
                        title="Environment"
                        :class="[
                            data.environment === 'production' ? 'bg-green-500 text-white' : 'bg-yellow-300 text-black',
                        ]"
                        >{{ data.environment }}</Badge
                    >
                    <Badge variant="outline" title="Language">{{ data.site.lang }}</Badge>
                    <!-- User Menu -->
                    <MenubarMenu>
                        <MenubarTrigger>
                            <Icon :icon="data.user.icon" class="h-4 w-4" />
                            {{ data.user.name }}
                        </MenubarTrigger>
                        <MenubarContent align="end">
                            <MenuTree :items="data.user.items" />
                        </MenubarContent>
                    </MenubarMenu>
                </div>
            </div>
        </Menubar>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Menubar, MenubarContent, MenubarMenu, MenubarTrigger } from '@/components/ui/menubar'
import { Switch } from '@/components/ui/switch'
import { Icon } from '@iconify/vue'
import type { Data } from '@types'
import axios from 'axios'
import { onMounted, ref, computed } from 'vue'
import MenuTree from './MenuTree.vue'
import { usePreferences } from '@/lib/preferences'
import SiteSwitcher from './SiteSwitcher.vue'
import { Toggle } from '@/components/ui/toggle'

const { preferences, toggleDarkMode } = usePreferences()
const sites = computed(() => data.value?.sites || [])

const data = ref<Data | null>(null)

onMounted(async () => {
    try {
        const response = await axios.get(`/!/statamic-admin-bar?uri=${window.location.pathname}`)
        data.value = response.data
        if (import.meta.env.DEV) console.log(data.value)
        axios.defaults.headers.common['X-CSRF-TOKEN'] = data.value?.csrfToken ?? ''
    } catch (error) {
        console.error('Failed to fetch admin bar data:', error)
    }
})

const handlePublishToggle = async () => {
    if (!data.value?.entry?.publishAction) return

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
