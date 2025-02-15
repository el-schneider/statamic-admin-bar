import type { ActionItem } from './item'
interface User extends ActionItem {
    initials: string
    preferred_locale: string
    title: string
    preferences: {
        dark_mode: boolean
    }
}

export type { User }
