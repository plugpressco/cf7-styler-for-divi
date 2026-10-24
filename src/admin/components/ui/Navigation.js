/**
 * Pill tabs and the Help menu.
 *
 * @package CF7_Mate
 */

import { useEffect, useRef } from '@wordpress/element';
import { ChevronDownIcon } from '@heroicons/react/20/solid';
import { cn } from '../../lib/utils';

export function NavTabs({ label, items, active, className }) {
	return (
		<nav aria-label={label} className={cn('flex items-center gap-0.5 overflow-x-auto', className)}>
			{items.map((item) => {
				const current = item.id === active;
				return (
					<a
						key={item.id}
						href={item.href}
						aria-current={current ? 'page' : undefined}
						className={cn(
							'flex h-8 shrink-0 items-center rounded-full px-[13px] text-[13px] font-medium transition-colors',
							current ? 'bg-sage text-primary hover:text-primary' : 'text-muted-foreground hover:bg-hover hover:text-foreground'
						)}
					>
						{item.label}
					</a>
				);
			})}
		</nav>
	);
}

/** A <details> dropdown that closes on an outside click or Escape. */
export function Menu({ label, children }) {
	const ref = useRef(null);
	useEffect(() => {
		const close = (event) => {
			const menu = ref.current;
			if (menu && menu.open && !menu.contains(event.target)) menu.open = false;
		};
		const escape = (event) => {
			const menu = ref.current;
			if (event.key === 'Escape' && menu && menu.open) {
				menu.open = false;
				const summary = menu.querySelector('summary');
				if (summary) summary.focus();
			}
		};
		document.addEventListener('mousedown', close);
		document.addEventListener('keydown', escape);
		return () => {
			document.removeEventListener('mousedown', close);
			document.removeEventListener('keydown', escape);
		};
	}, []);
	return (
		<details ref={ref} className="group relative">
			<summary className="flex h-8 cursor-pointer list-none items-center gap-1 rounded-full px-2.5 text-[13px] font-medium text-muted-foreground transition-colors hover:bg-hover hover:text-foreground [&::-webkit-details-marker]:hidden">
				{label}
				<ChevronDownIcon aria-hidden="true" className="h-4 w-4 transition-transform group-open:rotate-180" />
			</summary>
			<div className="absolute end-0 top-10 z-50 w-[210px] rounded-xl border border-border bg-card p-1.5 shadow-menu">{children}</div>
		</details>
	);
}

export function MenuItem({ className, children, ...props }) {
	return (
		<a className={cn('flex h-[34px] items-center justify-between gap-2 rounded-lg px-2.5 text-[13px] text-foreground hover:bg-hover hover:text-foreground [&_svg]:h-3.5 [&_svg]:w-3.5 [&_svg]:text-muted-foreground', className)} {...props}>
			{children}
		</a>
	);
}

/** A menu row that toggles a setting; its state shows on the right. */
export function MenuToggle({ label, checked, onChange, onText, offText }) {
	return (
		<button
			type="button"
			aria-pressed={checked}
			onClick={() => onChange(!checked)}
			className="flex h-[34px] w-full items-center justify-between gap-2 rounded-lg px-2.5 text-start text-[13px] text-foreground hover:bg-hover"
		>
			{label}
			<span className={cn('text-xs', checked ? 'font-medium text-primary' : 'text-muted-foreground')}>{checked ? onText : offText}</span>
		</button>
	);
}

export function MenuSeparator() {
	return <div role="separator" className="mx-1 my-1.5 h-px bg-divider" />;
}
