<template>
    <div
        v-if="unauthorized"
        :class="[
            preferences.darkMode && 'dark',
            'flex items-center justify-center bg-background text-sm text-foreground',
        ]"
    >
        <p>You are no longer logged in. Want to login?</p>
        <Button variant="link" as-child>
            <a :href="loginUrl">Login</a>
        </Button>
    </div>
    <div v-else-if="data" :class="[preferences.darkMode && 'dark', 'contents']">
        <Menubar class="hidden md:flex">
            <!-- Site Actions -->
            <Button variant="ghost" :class="data.site.homeAction.class" as-child>
                <a :href="data.site.homeAction.url" target="_blank">
                    <Icon icon="mdi:home" class="h-4 w-4" />
                </a>
            </Button>

            <!-- Content -->
            <MenubarMenu v-for="content in data.content" :key="content.name">
                <template v-if="content.items?.length">
                    <MenubarTrigger>
                        <Icon v-if="content.icon" :icon="content.icon" class="h-4 w-4" />
                        {{ content.name }}
                    </MenubarTrigger>
                    <MenubarContent>
                        <MenuTree :items="content.items" />
                    </MenubarContent>
                </template>

                <template v-else>
                    <Button variant="ghost" :class="content.class" as-child>
                        <a :href="content.url" target="_blank">
                            <Icon :icon="content.icon" class="h-4 w-4" />
                            {{ content.name }}
                        </a>
                    </Button>
                </template>
            </MenubarMenu>

            <div class="ml-auto flex items-center gap-2">
                <!-- Current Entry Items -->
                <template v-if="data.entry?.editAction">
                    <Button as-child variant="outline">
                        <a :href="data.entry.editAction.url" target="_blank">
                            <Icon icon="mdi:pencil" class="h-4 w-4" />
                            {{ data.entry.editAction.name }}
                        </a>
                    </Button>
                    <EntrySwitcher :locale="data.entry.short_locale" :localizations="data.entry.localizations" />
                </template>

                <template v-if="data.entry?.publishAction">
                    <div class="flex min-w-36 items-center gap-2 text-sm">
                        <Switch :checked="data.entry.status === 'published'" @update:checked="handlePublishToggle" />
                        <StatusBadge :status="data.entry.status" />
                    </div>
                </template>

                <div class="flex items-center gap-2">
                    <Badge :variant="data.environment === 'production' ? 'success' : 'warning'" title="Environment">
                        {{ data.environment }}
                    </Badge>
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
import { usePreferences } from '@/lib/preferences'
import { Icon } from '@iconify/vue'
import type { AdminBarResponse, Data } from '@types'
import axios from 'axios'
import { onMounted, ref } from 'vue'
import EntrySwitcher from './EntrySwitcher.vue'
import MenuTree from './MenuTree.vue'
import StatusBadge from './StatusBadge.vue'

const data = ref<Data | null>(null)
const unauthorized = ref(false)
const loginUrl = ref('')

const { preferences, syncPreferences } = usePreferences()

onMounted(async () => {
    try {
        const response = await axios.get<AdminBarResponse>(`/!/statamic-admin-bar`)

        if ('login' in response.data) {
            unauthorized.value = true
            loginUrl.value = response.data.login
        } else if ('disabled' in response.data && response.data.disabled) {
            localStorage.removeItem('admin-bar-user')
            document.getElementById('admin-bar')!.style.display = 'none'
        } else {
            data.value = response.data as Data
            localStorage.setItem('admin-bar-user', 'true')
            document.getElementById('admin-bar')!.style.display = 'block'
            axios.defaults.headers.common['X-CSRF-TOKEN'] = data.value.csrfToken
            syncPreferences(response.data.user.preferences)
        }
    } catch (error: any) {
        if (error.response?.status === 403 && error.response.data?.login) {
            unauthorized.value = true
            loginUrl.value = error.response.data.login
        } else {
            console.error('Failed to fetch admin bar data:', error)
        }
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
