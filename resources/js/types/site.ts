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
    attributes: any[]
    homeAction: ActionItem
}

export type { Site }
