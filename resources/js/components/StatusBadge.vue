<template>
    <Badge v-if="status" :variant="statusVariant" :title="label">{{ label ?? status }}</Badge>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { computed, onMounted } from 'vue'

const props = defineProps<{
    status?: string | null
    label?: string | null
}>()

onMounted(() => {
    console.log(props)
})

const statusVariant = computed(() => {
    const variants = {
        published: 'success',
        draft: 'warning',
        scheduled: 'successOutline',
        expired: 'destructiveOutline',
        missing: 'destructiveOutline',
    } as const

    return variants[props.status as keyof typeof variants] ?? 'default'
})
</script>
