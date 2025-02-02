import type { User } from '@/types/user'
import { ref, watch } from 'vue'

const STORAGE_KEY = 'statamic-admin-bar-preferences'

const stored = localStorage.getItem(STORAGE_KEY)

const initial = stored
    ? JSON.parse(stored)
    : {
          darkMode: false,
      }

const preferences = ref(initial)

watch(
    preferences,
    (newPrefs) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newPrefs))
    },
    { deep: true },
)

export function usePreferences() {
    return {
        preferences,
        syncPreferences(prefs: User['preferences']) {
            preferences.value = prefs
        },
    }
}
