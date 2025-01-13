<template>
    <div class="relative">
        <!-- Avatar Button -->
        <button
            @click="isOpen = !isOpen"
            class="h-8 w-8 rounded-full bg-yellow-400 flex items-center justify-center text-gray-700 hover:bg-yellow-300 transition-colors"
        >
            <span v-if="!user.avatar" class="font-medium">{{ user.initials }}</span>
            <img v-else :src="user.avatar" :alt="user.name" class="h-full w-full rounded-full object-cover" />
        </button>

        <!-- Dropdown Menu -->
        <div v-if="isOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
            <a :href="user.edit_url" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" target="_blank">
                Settings
            </a>
            <a :href="user.preferences_url" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" target="_blank">
                Preferences
            </a>
            <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                Logout
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface UserProps {
    api_url: string | null
    avatar: string | null
    edit_url: string
    preferences_url: string
    email: string
    groups: string[]
    id: string
    initials: string
    is_user: boolean
    last_login: string
    name: string
    preferred_locale: string
    roles: string[]
    super: boolean
    title: string
}

const props = defineProps<{
    user: UserProps
}>()

const isOpen = ref(false)

const closeDropdown = () => {
    isOpen.value = false
}

const handleLogout = async () => {
    // Implement logout logic here
    // You might want to make a POST request to your logout endpoint
    window.location.href = '/logout'
}

// Click outside directive
const vClickOutside = {
    mounted(el: HTMLElement, binding: any) {
        el.clickOutsideEvent = (event: Event) => {
            if (!(el === event.target || el.contains(event.target as Node))) {
                binding.value()
            }
        }
        document.addEventListener('click', el.clickOutsideEvent)
    },
    unmounted(el: HTMLElement) {
        document.removeEventListener('click', el.clickOutsideEvent)
    },
}
</script>
