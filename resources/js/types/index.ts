import type { Data } from './data'
import type { Entry } from './entry'
import type { ActionItem, DividerItem, Item } from './item'
import type { Site } from './site'
import type { Tree } from './tree'
import type { User } from './user'

export type { ActionItem, Data, DividerItem, Entry, Item, Site, Tree, User }
interface AuthRequiredResponse {
    login: string
}

interface DisabledResponse {
    disabled: boolean
}

export type AdminBarResponse = Data | AuthRequiredResponse | DisabledResponse
