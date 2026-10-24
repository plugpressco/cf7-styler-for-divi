/**
 * Buttons and button-styled links.
 *
 * @package CF7_Mate
 */

import { cn } from '../../lib/utils';

const variants = {
	primary: 'bg-primary text-primary-foreground hover:bg-primary-hover hover:text-primary-foreground',
	secondary: 'border border-input bg-card text-foreground hover:bg-hover hover:text-foreground',
	ghost: 'text-muted-foreground hover:bg-foreground/[.06] hover:text-foreground',
	link: 'h-auto px-0 text-primary hover:text-primary-hover hover:underline active:scale-100',
	danger: 'h-auto px-0 text-destructive hover:text-destructive hover:underline active:scale-100',
	'danger-outline': 'border border-input bg-card text-destructive hover:bg-destructive-tint hover:text-destructive',
};

const sizes = {
	default: 'h-[34px] px-3.5',
	sm: 'h-[30px] px-3',
	icon: 'h-8 w-8 rounded-full p-0',
};

export function buttonVariants({ variant = 'secondary', size = 'default', className } = {}) {
	return cn(
		'inline-flex select-none items-center justify-center gap-1.5 whitespace-nowrap rounded text-[13px] font-medium leading-none transition-[background-color,color,transform] duration-150 active:scale-[.97] disabled:pointer-events-none disabled:opacity-50 [&_svg]:h-4 [&_svg]:w-4',
		sizes[size],
		variants[variant],
		className
	);
}

export function Button({ variant, size, className, type = 'button', ...props }) {
	return <button type={type} className={buttonVariants({ variant, size, className })} {...props} />;
}
