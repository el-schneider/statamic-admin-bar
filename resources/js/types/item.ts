interface ActionItem {
    name: string
    url: string
    type: 'action'
    method?: 'put'
    class?: string
    items?: Item[]
    icon?: string
}

interface DividerItem {
    type: 'divider'
}

type Item = ActionItem | DividerItem

export type { ActionItem, DividerItem, Item }
