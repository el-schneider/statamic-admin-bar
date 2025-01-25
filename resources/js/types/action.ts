interface Action {
    name: string
    url: string
    type?: 'post' | 'divider'
    class?: string
}

export type { Action }
