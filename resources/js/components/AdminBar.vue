<template>
    <div
        v-if="shouldShow"
        class="fixed top-0 left-0 right-0 bg-yellow-500 font-mono text-xs font-medium shadow-md z-50 h-8"
    >
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center gap-2">
                <a
                    href="/cp"
                    class="flex items-center gap-2 py-0.5 px-3 text-gray-700 hover:bg-yellow-400 transition-colors"
                >
                    <span class="font-bold">⚡ Statamic</span>
                </a>
                <div v-if="data?.collections" class="flex items-center flex-1">
                    <NavItem
                        v-for="item in data.collections"
                        :key="item.name"
                        :name="item.name"
                        :url="item.url"
                        :icon="item.icon"
                    ></NavItem>
                </div>
                <div v-else class="py-1.5 px-3 text-gray-700">Loading...</div>
                <div v-if="data?.currentEntry" class="py-1.5 px-3 text-gray-700">
                    <a
                        :href="data.currentEntry.actions[0].url"
                        class="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-gray-100"
                        :class="data.currentEntry.actions[0].class"
                        target="_blank"
                    >
                        {{ data.currentEntry.actions[0].name }}
                    </a>
                </div>
                <User :user="data.user" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { ActionsData } from '../types/actionsData'
import NavItem from './NavItem.vue'
import User from './User.vue'

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
