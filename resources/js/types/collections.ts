import type { Action } from './action'

interface Collection {
    name: string
    url: string
    actions: Action[]
}

export type { Collection }
