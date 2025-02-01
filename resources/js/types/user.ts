import type { Item } from './item'

interface User {
    icon: string
    api_url: string | null
    avatar: string | null
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
    items: Item[]
}

export type { User }
