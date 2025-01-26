<script setup lang="ts">
import { cn } from '@/lib/utils'
import { MenubarRoot, type MenubarRootEmits, type MenubarRootProps, useForwardPropsEmits } from 'radix-vue'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<MenubarRootProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<MenubarRootEmits>()

const delegatedProps = computed(() => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { class: _, ...delegated } = props

    return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <div class="bg-background">
        <MenubarRoot v-bind="forwarded" :class="cn('container flex items-center gap-x-1 rounded-md p-1', props.class)">
            <slot />
        </MenubarRoot>
    </div>
</template>
