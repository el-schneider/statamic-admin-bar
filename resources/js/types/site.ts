import type { Action } from './action'

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
    actions: Action[]
}

export type { Site }
