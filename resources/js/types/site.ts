import type { ActionItem } from './item'

interface Site {
    handle: string
    name: string
    lang: string
    locale: string
    short_locale: string
    url: string
    permalink: string
    direction: string
    attributes: unknown[]
    home_action: ActionItem
    sites: {
        name: string
        icon: string
        items: {
            name: string
            url: string
            handle: string
            active: boolean
            icon: string
        }[]
    }
}

export type { Site }
