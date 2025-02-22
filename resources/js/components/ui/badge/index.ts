import { cva, type VariantProps } from 'class-variance-authority'

export { default as Badge } from './Badge.vue'

export const badgeVariants = cva(
    'inline-flex items-center rounded-full border transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
                secondary: 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
                outline: 'text-foreground',
                success: 'border-transparent bg-success text-success-foreground',
                successOutline: 'border border-success',
                destructive: 'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80',
                destructiveOutline: 'border border-destructive text-destructive',
                warning: 'border-transparent bg-warning text-warning-foreground',
                warningOutline: 'border border-warning text-warning-foreground',
            },
            size: {
                sm: 'px-1 text-xs text-[10px] ',
                default: 'px-2 py-0.5 text-xs',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
)

export type BadgeVariants = VariantProps<typeof badgeVariants>
