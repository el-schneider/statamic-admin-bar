<template>
    <Badge :variant="statusVariant" title="Status">{{ status }}</Badge>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
})

const statusVariant = computed(() => {
    const variants = {
        published: 'success',
        draft: 'warning',
        scheduled: 'warningOutline',
        expired: 'destructiveOutline',
        missing: 'destructiveOutline',
    } as const

    return variants[props.status as keyof typeof variants] ?? 'default'
})
</script>
