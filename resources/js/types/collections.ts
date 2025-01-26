import type { Item } from './item'

interface Collection {
    name: string
    url: string
    items: Item[]
}

export type { Collection }
