import axios from 'axios'

const instance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
})

export default instance
export const setCsrfToken = (token: string) => {
    instance.defaults.headers.common['X-CSRF-TOKEN'] = token
}
