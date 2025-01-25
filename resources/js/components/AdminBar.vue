<template>
    <Menubar v-if="shouldShow" class="container fixed left-0 right-0 top-0 z-50 text-xs font-medium shadow-md">
        <!-- Home -->
        <template v-if="data?.site">
            <Button
                v-for="action in data.site.actions"
                :key="action.name"
                variant="link"
                :class="action.class"
                as-child
            >
                <a :href="action.url" target="_blank">
                    {{ action.name }}
                </a>
            </Button>
        </template>

        <!-- Collections -->
        <MenubarMenu v-if="data?.collections">
            <MenubarTrigger>Collections</MenubarTrigger>
            <MenubarContent>
                <template v-for="item in data.collections" :key="item.name">
                    <MenubarSub>
                        <MenubarSubTrigger>{{ item.name }}</MenubarSubTrigger>
                        <MenubarSubContent>
                            <MenubarItem v-for="action in item.actions" :key="action.name" as-child>
                                <a :href="action.url" :class="action.class" target="_blank">
                                    {{ action.name }}
                                </a>
                            </MenubarItem>
                        </MenubarSubContent>
                    </MenubarSub>
                </template>
            </MenubarContent>
        </MenubarMenu>

        <div class="ml-auto flex">
            <!-- Current Entry Actions -->
            <template v-if="data?.currentEntry">
                <Button
                    v-for="action in data.currentEntry.actions"
                    :key="action.name"
                    :class="['bg-green-500 hover:bg-green-800', action.class]"
                    as-child
                >
                    <a :href="action.url" target="_blank">
                        {{ action.name }}
                    </a>
                </Button>
            </template>

            <!-- User Menu -->
            <MenubarMenu v-if="data?.user" class="ml-auto">
                <MenubarTrigger>{{ data.user.name }}</MenubarTrigger>
                <MenubarContent align="end">
                    <MenubarItem v-for="item in data.user.actions" :key="item.name" as-child>
                        <a :href="item.url" target="_blank">
                            {{ item.name }}
                        </a>
                    </MenubarItem>
                </MenubarContent>
            </MenubarMenu>
        </div>
    </Menubar>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
    Menubar,
    MenubarContent,
    MenubarItem,
    MenubarMenu,
    MenubarSub,
    MenubarSubContent,
    MenubarSubTrigger,
    MenubarTrigger,
} from '@/components/ui/menubar'
import { onMounted, ref } from 'vue'
import type { ActionsData } from '../types/actionsData'

const data = ref<ActionsData | null>(null)
const shouldShow = ref(false)

onMounted(async () => {
    try {
        const response = await fetch(`/!/statamic-admin-bar?uri=${window.location.pathname}`)
        if (response.status === 403) {
            return
        }
        data.value = await response.json()
        shouldShow.value = true
        console.log(data.value)
    } catch (error) {
        console.error('Error fetching admin bar data:', error)
    }
})
</script>

<style scoped></style>
