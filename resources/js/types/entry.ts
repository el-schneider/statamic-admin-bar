import type { ActionItem } from './item'

interface Localization {
    site_name: string
    locale: string
    short_locale: string
    title: string
    url: string | null
    edit_url: string | null
    origin: boolean
    is_current: boolean
    status: 'published' | 'draft' | 'scheduled' | 'expired' | null
}

interface Entry {
    id: string
    title: string
    status: string
    published: boolean
    locale: string
    localizations: Localization[]
    publish_date: string | null
    expiration_date: string | null
    editAction?: ActionItem
    publishAction?: ActionItem
}

export type { Entry, Localization }
