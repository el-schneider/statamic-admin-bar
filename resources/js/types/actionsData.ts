import type { Collection } from './collections'
import type { CurrentEntry } from './currentEntry'
import type { Site } from './site'
import type { User } from './user'
interface ActionsData {
    site: Site
    user: User
    collections: Collection[]
    currentEntry: CurrentEntry | null
}

export type { ActionsData }
