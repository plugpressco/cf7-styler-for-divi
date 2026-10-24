/**
 * Page heading, badges, chips, avatars, meters and empty states.
 *
 * @package CF7_Mate
 */

import { cn } from '../../lib/utils';

export function PageHeader({ title, meta, description, actions, className }) {
	return (
		<div className={cn('flex flex-wrap items-center justify-between gap-3', className)}>
			<div className="min-w-0">
				<div className="flex flex-wrap items-baseline gap-x-2.5 gap-y-1">
					<h1 className="text-[22px] font-semibold leading-tight tracking-[-0.3px]">{title}</h1>
					{meta}
				</div>
				{description && <p className="mt-1 text-[13px] text-muted-foreground">{description}</p>}
			</div>
			{actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
		</div>
	);
}

const tints = {
	default: 'bg-hover text-muted-foreground',
	sage: 'bg-sage text-primary',
	coral: 'bg-coral-tint text-coral-foreground',
	sand: 'bg-sand text-sand-foreground',
	danger: 'bg-destructive-tint text-destructive',
};

export function Badge({ tone = 'default', className, ...props }) {
	return <span className={cn('inline-flex h-5 items-center rounded-full px-2 text-[11px] font-medium tabular-nums', tints[tone], className)} {...props} />;
}

export function IconChip({ tone = 'sage', size = 'default', className, children }) {
	return (
		<span aria-hidden="true" className={cn('inline-flex shrink-0 items-center justify-center [&_svg]:h-4 [&_svg]:w-4', size === 'sm' ? 'h-[30px] w-[30px] rounded-[9px]' : 'h-[34px] w-[34px] rounded-tile', tints[tone], className)}>
			{children}
		</span>
	);
}

const avatarTones = ['sage', 'coral', 'sand'];

export function initials(text) {
	const value = String(text || '').trim();
	if (!value) return '?';
	if (value.includes('@')) return Array.from(value)[0].toUpperCase();
	const words = value.split(/\s+/).filter((word) => /\p{L}/u.test(word));
	if (!words.length) return Array.from(value)[0].toUpperCase();
	return words.slice(0, 2).map((word) => Array.from(word)[0].toUpperCase()).join('');
}

export function Avatar({ name, seed = 0 }) {
	const tone = avatarTones[Math.abs(Number(seed) || 0) % avatarTones.length];
	return (
		<span aria-hidden="true" className={cn('inline-flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-full text-xs font-semibold tracking-[.02em]', tints[tone])}>
			{initials(name)}
		</span>
	);
}

export function MeterBar({ value, max }) {
	// A small minimum keeps tiny values visible; zero stays empty.
	const width = max > 0 && value > 0 ? Math.max(4, Math.min(100, (value / max) * 100)) : 0;
	return (
		<span aria-hidden="true" className="inline-block h-1.5 w-[72px] overflow-hidden rounded-full bg-divider">
			<span className="block h-full rounded-full bg-ring" style={{ width: `${width}%` }} />
		</span>
	);
}

export function EmptyState({ icon, title, description, action, className }) {
	return (
		<div className={cn('flex flex-col items-center gap-2 rounded-box border border-dashed border-input bg-card px-5 py-11 text-center', className)}>
			{icon && <IconChip tone="sage">{icon}</IconChip>}
			<p className="text-sm font-medium">{title}</p>
			{description && <p className="text-[13px] text-muted-foreground">{description}</p>}
			{action && <div className="mt-2">{action}</div>}
		</div>
	);
}
