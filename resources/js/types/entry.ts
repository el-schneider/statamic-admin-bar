import type { ActionItem } from './item'

interface BaseEntry {
    id: string
    title: string
    status: 'published' | 'draft' | 'scheduled' | 'expired' | null
    published: boolean
    locale: string
    short_locale: string
    site_name: string
    url: string | null
    edit_url: string | null
    origin: boolean
    is_current: boolean
    publish_date: string | null
    expiration_date: string | null
}

type Entry = BaseEntry & {
    edit_action?: ActionItem
    publish_action?: ActionItem
    localizations?: BaseEntry[]
}

export type { Entry }
