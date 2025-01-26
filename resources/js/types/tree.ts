import type { Item } from './item'

interface Tree {
    name: string
    icon?: string
    class?: string
    url?: string
    items: Item[]
}

export type { Tree }
