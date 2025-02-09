<template>
    <div
        v-if="unauthorized"
        id="admin-bar__unauthorized"
        :class="[
            preferences.dark_mode && 'dark',
            'flex items-center justify-center bg-background text-sm text-foreground',
        ]"
    >
        <p>You are no longer logged in. Want to login?</p>
        <Button variant="link" as-child>
            <a :href="loginUrl">Login</a>
        </Button>
    </div>
    <div v-else-if="data" id="admin-bar__container" :class="[preferences.dark_mode && 'dark', 'contents']">
        <Menubar id="admin-bar__menubar" class="hidden contain-layout @container sm:flex">
            <!-- Site Actions -->
            <Button id="admin-bar__home" variant="ghost" :class="data.site.home_action.class" as-child>
                <a :href="data.site.home_action.url" target="_blank">
                    <Icon icon="mdi:home" class="h-4 w-4" />
                </a>
            </Button>

            <!-- Content -->
            <MenubarMenu v-for="content in data.content" :key="content.name">
                <template v-if="content.items?.length">
                    <MenubarTrigger>
                        <Icon v-if="content.icon" :icon="content.icon" class="h-4 w-4" />
                        <span class="hidden @4xl:inline">{{ content.name }}</span>
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

            <div id="admin-bar__actions" class="ml-auto flex items-center gap-2">
                <!-- Current Entry Items -->
                <template v-if="data.entry?.edit_action">
                    <Button id="admin-bar__edit" as-child variant="outline">
                        <a :href="data.entry.edit_action.url" target="_blank">
                            <Icon icon="mdi:file-edit" class="h-4 w-4" />
                            <span class="hidden @4xl:inline">{{ data.entry.edit_action.name }}</span>
                        </a>
                    </Button>
                    <EntrySwitcher
                        :label="data.entry.switch_site"
                        :locale="data.entry.short_locale"
                        :localizations="data.entry.localizations"
                    />
                </template>

                <template v-if="data.entry?.publish_action">
                    <div id="admin-bar__publish" class="flex min-w-36 items-center gap-2 text-sm">
                        <Switch :checked="data.entry.status === 'published'" @update:checked="handlePublishToggle" />
                        <StatusBadge :status="data.entry.status" />
                    </div>
                </template>

                <div id="admin-bar__meta" class="flex items-center gap-2">
                    <Badge
                        id="admin-bar__environment"
                        :variant="data.environment === 'production' ? 'success' : 'warning'"
                        title="Environment"
                    >
                        {{ data.environment }}
                    </Badge>
                    <!-- User Menu -->
                    <MenubarMenu id="admin-bar__user">
                        <MenubarTrigger>
                            <Icon :icon="data.user.icon" class="h-4 w-4" />
                            <span class="hidden @4xl:inline">{{ data.user.name }}</span>
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

const props = defineProps<{
    onStateChange?: (state: string) => void
}>()

onMounted(async () => {
    try {
        const response = await axios.get<AdminBarResponse>(`/!/statamic-admin-bar`)

        if ('login' in response.data) {
            unauthorized.value = true
            loginUrl.value = response.data.login
            props.onStateChange?.('unauthorized')
        } else if ('disabled' in response.data && response.data.disabled) {
            localStorage.removeItem('admin-bar-user')
            document.getElementById('admin-bar')!.style.display = 'none'
            props.onStateChange?.('disabled')
        } else {
            data.value = response.data as Data
            localStorage.setItem('admin-bar-user', 'true')
            document.getElementById('admin-bar')!.style.display = 'block'
            axios.defaults.headers.common['X-CSRF-TOKEN'] = data.value.csrf_token
            syncPreferences(response.data.user.preferences)
            props.onStateChange?.('loaded')
        }
    } catch (error: any) {
        props.onStateChange?.('error')
    }
})

const handlePublishToggle = async () => {
    if (!data.value?.entry?.publish_action) return

    try {
        const response = await axios.put(data.value.entry.publish_action.url, {
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
