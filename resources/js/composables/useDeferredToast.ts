import { useToast } from '@/components/ui/toast/use-toast'
import { onMounted } from 'vue'

const DEFERRED_TOAST_KEY = 'deferredToasts'

export interface DeferredToastOptions {
    description: string
    variant: string
}

export function useDeferredToast() {
    const { toast } = useToast()

    function addDeferredToast(options: DeferredToastOptions) {
        try {
            const stored = localStorage.getItem(DEFERRED_TOAST_KEY)
            const toasts: DeferredToastOptions[] = stored ? JSON.parse(stored) : []
            toasts.push(options)
            localStorage.setItem(DEFERRED_TOAST_KEY, JSON.stringify(toasts))
        } catch (error) {
            console.error('Error adding deferred toast:', error)
        }
    }

    onMounted(() => {
        const stored = localStorage.getItem(DEFERRED_TOAST_KEY)
        if (stored) {
            try {
                const toasts: DeferredToastOptions[] = JSON.parse(stored)
                toasts.forEach((options) => toast(options))
            } catch (error) {
                console.error('Error parsing deferred toasts:', error)
            } finally {
                localStorage.removeItem(DEFERRED_TOAST_KEY)
            }
        }
    })

    return { addDeferredToast }
}
