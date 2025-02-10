interface Cache {
    name: string
    icon: string
    items: Array<{
        name: string
        icon: string
        url: string
        method: string
    }>
}

export type { Cache }
