<template>
    <MenubarMenu>
        <MenubarTrigger>
            <Icon icon="mdi-light:earth" class="h-4 w-4" />
            {{ currentSite.name }}
        </MenubarTrigger>
        <MenubarContent>
            <MenubarRadioGroup v-model="selectedSite">
                <MenubarRadioItem
                    v-for="site in sites"
                    :key="site.handle"
                    :value="site.handle"
                >
                    {{ site.name }}
                </MenubarRadioItem>
            </MenubarRadioGroup>
        </MenubarContent>
    </MenubarMenu>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePreferences } from '@/lib/preferences'
import { MenubarMenu, MenubarTrigger, MenubarContent, MenubarRadioGroup, MenubarRadioItem } from '@/components/ui/menubar'
import { Icon } from '@iconify/vue'

const props = defineProps<{
    sites: Array<{
        handle: string
        name: string
    }>
}>()

const { preferences } = usePreferences()
const selectedSite = computed({
    get: () => preferences.value.site || props.sites[0].handle,
    set: (site) => {
        preferences.value.site = site
        window.location.href = `/${site}`
    }
})

const currentSite = computed(() =>
    props.sites.find(s => s.handle === selectedSite.value) || props.sites[0]
)
</script>
