/**
 * Status, alerts, loading placeholders and toast.
 *
 * @package CF7_Mate
 */

import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { CheckIcon, ExclamationCircleIcon } from '@heroicons/react/20/solid';
import { cn } from '../../lib/utils';
import { play } from '../../utils/sound';

export function Spinner({ className }) {
	return (
		<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" className={cn('h-4 w-4 animate-spin', className)}>
			<circle cx="12" cy="12" r="9" stroke="currentColor" strokeOpacity=".2" strokeWidth="3" />
			<path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
		</svg>
	);
}

/** Text that sits after a row of buttons, so it never moves them. */
export function InlineStatus({ status }) {
	if (!status) return <span role="status" />;
	const error = status.type === 'error';
	return (
		<span role={error ? 'alert' : 'status'} className={cn('text-[13px]', error ? 'text-destructive' : 'text-success')}>
			{status.message}
		</span>
	);
}

const alertVariants = {
	info: 'border-sage-border bg-sage text-foreground',
	error: 'border-destructive/20 bg-destructive-tint text-destructive',
};

export function Alert({ variant = 'info', role, className, action, children }) {
	return (
		<div role={role || (variant === 'error' ? 'alert' : 'status')} className={cn('flex flex-wrap items-center justify-between gap-x-4 gap-y-2 rounded-tile border px-3.5 py-2.5 text-[13px]', alertVariants[variant], className)}>
			<div className="min-w-0">{children}</div>
			{action}
		</div>
	);
}

export function Skeleton({ className }) {
	return <div aria-hidden="true" className={cn('animate-pulse rounded bg-foreground/[.06]', className)} />;
}

export function LoadingState({ rows = 3, className }) {
	return (
		<div role="status" className={cn('flex flex-col gap-2', className)}>
			<span className="sr-only">{__('Loading', 'cf7-styler-for-divi')}</span>
			{Array.from({ length: rows }, (_, index) => <Skeleton key={index} className="h-11 rounded-tile" />)}
		</div>
	);
}

export function Toast({ message, type = 'success', onClose }) {
	const close = useRef(onClose);
	close.current = onClose;
	const [leaving, setLeaving] = useState(false);
	useEffect(() => {
		if (type === 'error') play('error');
		setLeaving(false);
		const shown = type === 'error' ? 6000 : 4000;
		const fade = setTimeout(() => setLeaving(true), shown - 200);
		const timer = setTimeout(() => close.current && close.current(), shown);
		return () => {
			clearTimeout(fade);
			clearTimeout(timer);
		};
	}, [message, type]);
	const error = type === 'error';
	return (
		<div
			role={error ? 'alert' : 'status'}
			className={cn(
				'fixed bottom-6 end-6 z-[100000] flex max-w-[min(380px,calc(100vw-32px))] items-center gap-2 rounded-full px-4 py-2.5 text-[13px] text-white shadow-toast',
				leaving ? 'animate-toast-out' : 'animate-toast-in',
				error ? 'bg-destructive' : 'bg-foreground'
			)}
		>
			{error ? <ExclamationCircleIcon aria-hidden="true" className="h-4 w-4" /> : <CheckIcon aria-hidden="true" className="h-4 w-4 text-[#8FAF9D]" />}
			<span>{message}</span>
		</div>
	);
}
