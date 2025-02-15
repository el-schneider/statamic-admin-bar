import type { ActionItem } from './item'

interface BaseEntry {
    id: string
    title: string
    status?: 'published' | 'draft' | 'scheduled' | 'expired'
    localized_status?: string
    published: boolean
    locale: string
    short_locale: string
    site_name: string
    url: string | null
    switch_site: string
    is_origin: boolean
    is_current: boolean
    publish_date: string | null
    expiration_date: string | null
    edit_action?: ActionItem
    update_action?: ActionItem
}

type Entry = {
    switch_site_label: string
    short_site_label?: string
    short_locale: string
    localized_status: string
    localizations?: BaseEntry[]
}

export type { Entry }
