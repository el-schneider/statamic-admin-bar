import type { Collection } from './collections'
import type { CurrentEntry } from './currentEntry'
import type { User } from './user'

interface ActionsData {
    user: User
    collections: Collection[]
    currentEntry: CurrentEntry | null
}

export type { ActionsData }
