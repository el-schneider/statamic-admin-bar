<template>
    <a
        target="_blank"
        :href="url"
        :class="class"
        class="flex items-center gap-2 px-3 py-0.5 text-gray-700 transition-colors hover:bg-yellow-400"
    >
        <!-- <span class="w-4 h-4" v-html="icon"></span> -->
        <span>{{ name }}</span>
    </a>
</template>

<script setup lang="ts">
interface Props {
    name: string
    url: string
    icon: string
    class: string
    title: string
}

defineProps<Props>()
</script>
