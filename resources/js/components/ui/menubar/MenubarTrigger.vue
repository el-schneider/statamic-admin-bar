<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { MenubarTrigger, type MenubarTriggerProps, useForwardProps } from 'radix-vue'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<MenubarTriggerProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { class: _, ...delegated } = props

    return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <Button variant="ghost" size="icon" class="@4xl:w-auto @4xl:px-2" v-bind="forwardedProps" as-child>
        <MenubarTrigger>
            <slot />
        </MenubarTrigger>
    </Button>
</template>
