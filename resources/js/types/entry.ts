import type { ActionItem } from './item'

interface Entry {
    id: string
    title: string
    published: boolean
    editAction: ActionItem
    publishAction: ActionItem
}

export type { Entry }
