import { createApp } from 'vue'
import '../css/admin-bar.css'

import AdminBar from './components/AdminBar.vue'

const rootEl = document.getElementById('admin-bar')
if (rootEl) {
    const setState = (state: string) => rootEl.setAttribute('data-admin-bar-state', state)
    setState('initializing')

    const app = createApp(AdminBar, {
        onStateChange: setState,
    })
    app.mount('#admin-bar')
}
