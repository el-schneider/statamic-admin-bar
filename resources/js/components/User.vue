<template>
    <div class="relative">
        <!-- Avatar Button -->
        <button
            class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-400 text-gray-700 transition-colors hover:bg-yellow-300"
            @click="isOpen = !isOpen"
        >
            <span v-if="!user.avatar" class="font-medium">{{ user.initials }}</span>
            <img v-else :src="user.avatar" :alt="user.name" class="h-full w-full rounded-full object-cover" />
        </button>

        <!-- Dropdown Menu -->
        <div v-if="isOpen" class="absolute right-0 z-50 mt-2 w-48 rounded-md bg-white py-1 shadow-lg">
            <ul>
                <li v-for="action in user.actions" :key="action.name">
                    <a
                        :href="action.url"
                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100"
                        :class="action.class"
                        target="_blank"
                    >
                        {{ action.name }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { User } from '@types'
import { ref } from 'vue'

const isOpen = ref(false)

defineProps<{
    user: User
}>()
</script>
