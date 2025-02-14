<template>
    <Badge v-if="status" :variant="statusVariant" :title="label">{{ label ?? status }}</Badge>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        default: undefined,
    },
    label: {
        type: String,
        default: undefined,
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
