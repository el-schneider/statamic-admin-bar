import { ref, watch } from 'vue'

const STORAGE_KEY = 'statamic-admin-bar-preferences'

// Initialize from localStorage
const stored = localStorage.getItem(STORAGE_KEY)
const initial = stored ? JSON.parse(stored) : {
    darkMode: false,
    site: null
}

const preferences = ref(initial)

// Watch for changes and persist to localStorage
watch(preferences, (newPrefs) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(newPrefs))
}, { deep: true })

export function usePreferences() {
    return {
        preferences,
        toggleDarkMode() {
            preferences.value.darkMode = !preferences.value.darkMode
        },
        setSite(site) {
            preferences.value.site = site
        }
    }
}
