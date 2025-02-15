<template>
    <Toaster />
    <div
        v-if="unauthorized"
        id="admin-bar__unauthorized"
        class="flex h-admin-bar items-center justify-center bg-background text-sm text-foreground"
    >
        <p>
            You are no longer logged in. Want to
            <Button variant="link" as-child class="px-0"><a :href="loginUrl">login</a> </Button>?
        </p>
    </div>
    <div v-else-if="data" id="admin-bar__container" class="contents">
        <Menubar id="admin-bar__menubar" class="hidden h-admin-bar contain-layout @container sm:flex">
            <!-- Site Actions -->
            <Button id="admin-bar__home" variant="ghost" :class="data.site.home_action.class" as-child>
                <a :href="data.site.home_action.url" target="_blank">
                    <Icon icon="mdi:home" />
                </a>
            </Button>

            <!-- Content -->
            <MenubarMenu v-for="content in data.content" :key="content.name">
                <template v-if="content.items?.length">
                    <MenubarTrigger>
                        <Icon v-if="content.icon" :icon="content.icon" />
                        <span class="hidden @4xl:inline">{{ content.name }}</span>
                    </MenubarTrigger>
                    <MenubarContent>
                        <MenuTree :items="content.items" />
                    </MenubarContent>
                </template>

                <template v-else>
                    <Button variant="ghost" :class="content.class" as-child>
                        <a :href="content.url" target="_blank">
                            <Icon :icon="content.icon" />
                            {{ content.name }}
                        </a>
                    </Button>
                </template>
            </MenubarMenu>

            <div id="admin-bar__actions" class="ml-auto flex items-center gap-2">
                <!-- Current Entry Items -->
                <template v-if="data.entry">
                    <EntryActions :entry="data.entry" />
                </template>

                <div id="admin-bar__meta" class="flex items-center gap-2">
                    <!-- Site Meta -->
                    <CacheMenu :cache="data.cache" />
                    <div class="flex gap-1">
                        <Badge
                            id="admin-bar__environment"
                            :variant="data.environment === 'production' ? 'success' : 'warning'"
                            title="Environment"
                        >
                            {{ data.environment }}
                        </Badge>
                        <Badge
                            v-if="data.entry?.localizations?.length === 1"
                            variant="outline"
                            class="uppercase text-muted-foreground"
                        >
                            {{ data.entry.short_locale }}
                        </Badge>
                    </div>

                    <!-- User Menu -->
                    <MenubarMenu id="admin-bar__user">
                        <MenubarTrigger>
                            <Icon :icon="data.user.icon" />
                            <span class="hidden @4xl:inline">{{ data.user.initials }}</span>
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
import Toaster from '@/components/ui/toast/Toaster.vue'

import { usePreferences } from '@/lib/preferences'
import { Icon } from '@iconify/vue'
import type { AdminBarResponse, Data } from '@types'
import axios, { AxiosError } from 'axios'
import { onMounted, ref } from 'vue'

import CacheMenu from './CacheMenu.vue'
import EntryActions from './EntryActions.vue'
import MenuTree from './MenuTree.vue'

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

        const rootEl = document.getElementById('admin-bar')

        if ('disabled' in response.data && response.data.disabled) {
            localStorage.removeItem('admin-bar-user')
            rootEl!.style.display = 'none'
            props.onStateChange?.('disabled')
        } else {
            data.value = response.data as Data
            rootEl!.style.display = 'block'
            axios.defaults.headers.common['X-CSRF-TOKEN'] = data.value.csrf_token
            syncPreferences(response.data.user.preferences)
            props.onStateChange?.('loaded')

            const adminBarHeight = rootEl!.dataset.adminBarHeight!

            localStorage.setItem('admin-bar-height', adminBarHeight)
            document.documentElement.style.setProperty('--admin-bar-height', adminBarHeight)
            rootEl!.classList.add(preferences.value.dark_mode ? 'dark' : '')
        }
    } catch (error: unknown) {
        if (error instanceof AxiosError && error.response?.status === 403) {
            unauthorized.value = true
            loginUrl.value = error.response.data.login
            props.onStateChange?.('unauthorized')
            return
        }
        props.onStateChange?.('error')
    }
})
</script>
