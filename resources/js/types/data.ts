import type { Entry } from './entry'
import type { Site } from './site'
import type { Tree } from './tree'
import type { User } from './user'

interface ItemsData {
    site: Site
    user: User
    collections: Tree
    taxonomies: Tree
    entry: Entry | null
}

export type { ItemsData }
