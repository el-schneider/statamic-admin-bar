import type { Collection } from './collections'
import type { Entry } from './entry'
import type { Site } from './site'
import type { User } from './user'

interface ItemsData {
    site: Site
    user: User
    collections: Collection[]
    entry: Entry | null
}

export type { ItemsData }
