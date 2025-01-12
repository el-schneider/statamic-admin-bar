<template>
    <div class="fixed top-0 left-0 right-0 bg-yellow-500 font-mono text-sm font-medium shadow-md z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center gap-2">
                <a
                    href="/cp"
                    class="flex items-center gap-2 py-0.5 px-3 text-gray-700 hover:bg-yellow-400 transition-colors"
                >
                    <span class="font-bold">⚡ Statamic</span>
                </a>
                <div class="flex items-center flex-1" v-if="data">
                    <NavItem
                        v-for="item in data.navItems"
                        :key="item.name"
                        :name="item.name"
                        :url="item.url"
                        :icon="item.icon"
                    ></NavItem>
                    <NavItem
                        v-if="data.currentEntry"
                        name="Edit"
                        :title="data.currentEntry.title"
                        :url="data.currentEntry.edit_url"
                        :icon="data.currentEntry.icon"
                        class="bg-green-500 ml-auto"
                    ></NavItem>
                </div>
                <div v-else class="py-1.5 px-3 text-gray-700">Loading...</div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import NavItem from './NavItem.vue'

interface NavItemType {
    name: string
    url: string
    icon: string
    section: string
}

const data = ref<NavItemType[] | null>(null)

onMounted(async () => {
    try {
        const response = await fetch(`/!/statamic-admin-bar?uri=${window.location.pathname}`)
        data.value = await response.json()
        console.log(data.value)
    } catch (error) {
        console.error('Error fetching admin bar data:', error)
    }
})
</script>

<style scoped></style>
