/**
 * White box with optional header and footer rows.
 *
 * @package CF7_Mate
 */

import { cn } from '../../lib/utils';

export function Card({ as: Tag = 'section', className, ...props }) {
	return <Tag className={cn('rounded-box border border-border bg-card shadow-box', className)} {...props} />;
}

export function CardHeader({ className, ...props }) {
	return <div className={cn('flex flex-wrap items-center justify-between gap-3 border-b border-divider px-[18px] py-3.5', className)} {...props} />;
}

export function CardTitle({ as: Tag = 'h2', className, ...props }) {
	return <Tag className={cn('text-sm font-semibold', className)} {...props} />;
}

export function CardContent({ className, ...props }) {
	return <div className={cn('px-[18px] py-4', className)} {...props} />;
}

export function CardFooter({ className, ...props }) {
	return <div className={cn('flex flex-wrap items-center gap-x-3 gap-y-2 border-t border-divider px-[18px] py-3', className)} {...props} />;
}
