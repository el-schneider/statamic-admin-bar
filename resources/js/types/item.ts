interface ActionItem {
    name: string
    url: string
    type: 'post' | 'link'
    class?: string
    items?: Item[]
}

interface DividerItem {
    type: 'divider'
}

type Item = ActionItem | DividerItem

export type { ActionItem, DividerItem, Item }
