import type { Action } from './action'

interface CurrentEntry {
    id: string
    title: string
    actions: Action[]
}

export type { CurrentEntry }
