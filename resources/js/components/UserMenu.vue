<template>
    <MenubarMenu id="admin-bar__user">
        <MenubarTrigger>
            <Icon icon="mdi:account" />
            <span class="hidden @4xl:inline">{{ user.initials }}</span>
        </MenubarTrigger>

        <MenubarContent v-if="user.items?.length" align="end">
            <div class="flex flex-col gap-2 p-2">
                <div class="flex items-center gap-2">
                    <Avatar class="-ml-1">
                        <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.email" />
                        <AvatarFallback v-else>{{ user.initials }}</AvatarFallback>
                    </Avatar>
                    <div class="flex flex-col items-start gap-1">
                        <span class="text-sm">{{ user.email }}</span>
                        <Badge v-if="user.is_super" variant="outline">Super Admin</Badge>
                    </div>
                </div>
                <template v-if="user.groups.length || user.roles.length">
                    <Separator class="my-1" />
                    <div v-if="user.groups.length" class="flex items-start justify-start gap-1">
                        <h3 class="w-16 text-xs text-muted-foreground">{{ user.groups_label }}</h3>
                        <div class="flex flex-col items-start gap-1">
                            <Badge v-for="group in user.groups" :key="group" variant="outline">
                                {{ group }}
                            </Badge>
                        </div>
                    </div>
                    <div v-if="user.roles.length" class="flex items-start justify-start gap-1">
                        <h3 class="w-16 text-xs text-muted-foreground">{{ user.roles_label }}</h3>
                        <div class="flex flex-col items-start gap-1">
                            <Badge v-for="role in user.roles" :key="role" variant="outline">
                                {{ role }}
                            </Badge>
                        </div>
                    </div>
                </template>
            </div>
            <Separator class="mb-2 mt-1" />

            <MenuTree :items="user.items" />
        </MenubarContent>
    </MenubarMenu>
</template>

<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { MenubarContent, MenubarMenu, MenubarTrigger } from '@/components/ui/menubar'
import { Separator } from '@/components/ui/separator'
import type { User } from '@/types'
import { Icon } from '@iconify/vue'
import MenuTree from './MenuTree.vue'
defineProps<{
    user: User
}>()
</script>
