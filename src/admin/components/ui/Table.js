/**
 * Quiet list table: hairline rows, no stripes.
 *
 * @package CF7_Mate
 */

import { cn } from '../../lib/utils';

export function Table({ className, children, ...props }) {
	return (
		// relative: keeps sr-only (absolute) labels inside the scroll box.
		<div className="relative w-full overflow-x-auto">
			<table className={cn('w-full text-[13px]', className)} {...props}>{children}</table>
		</div>
	);
}

export function TableHead({ className, ...props }) {
	return <th scope="col" className={cn('h-9 whitespace-nowrap border-b border-divider px-3.5 text-xs font-normal text-muted-foreground', className)} {...props} />;
}

export function TableRow({ className, ...props }) {
	return <tr className={cn('transition-colors hover:bg-hover/70 [&:last-child>td]:border-0', className)} {...props} />;
}

export function TableCell({ className, ...props }) {
	return <td className={cn('border-b border-divider px-3.5 py-2.5 align-middle', className)} {...props} />;
}
