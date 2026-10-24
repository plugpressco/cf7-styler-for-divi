/**
 * Checkboxes: a native input kept for keyboard and screen readers, drawn as
 * a rounded box whose tick pops in when checked.
 *
 * @package CF7_Mate
 */

import { CheckIcon } from '@heroicons/react/16/solid';
import { cn } from '../../lib/utils';
import { play } from '../../utils/sound';

function Mark({ checked }) {
	return (
		<span
			aria-hidden="true"
			className={cn(
				'inline-flex h-[18px] w-[18px] shrink-0 items-center justify-center rounded-md border-[1.5px] transition-colors duration-150 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-ring',
				checked ? 'border-primary bg-primary' : 'border-[#a9b8ae] bg-card'
			)}
		>
			<CheckIcon className={cn('h-3 w-3 text-white transition-transform duration-200 ease-spring', checked ? 'scale-100' : 'scale-0')} />
		</span>
	);
}

function input(checked, onChange, props) {
	return (
		<input
			type="checkbox"
			className="peer sr-only"
			checked={!!checked}
			onChange={(event) => {
				play(event.target.checked ? 'tick' : 'untick');
				if (onChange) onChange(event.target.checked);
			}}
			{...props}
		/>
	);
}

/** Inline checkbox with an optional visible label. */
export function Checkbox({ checked, onChange, label, className, ...props }) {
	return (
		<label className={cn('relative inline-flex cursor-pointer select-none items-center gap-2 text-[13px]', props.disabled && 'cursor-not-allowed opacity-60', className)}>
			{input(checked, onChange, props)}
			<Mark checked={checked} />
			{label}
		</label>
	);
}

/** Tile used for feature lists: the whole tile toggles. */
export function CheckTile({ id, checked, onChange, label, description, disabled }) {
	const descriptionId = description ? `${id}-description` : undefined;
	return (
		<div className="relative min-w-0">
			<label
				title={description}
				className={cn(
					'relative flex cursor-pointer select-none items-center gap-2.5 rounded-tile border px-3 py-[11px] text-[13px] transition-[background-color,border-color,box-shadow,transform] duration-200 hover:-translate-y-px hover:shadow-[0_6px_16px_rgba(33,55,45,.08)] active:translate-y-0 active:scale-[.98]',
					checked ? 'border-sage-line bg-sage/60' : 'border-border bg-card hover:border-sage-line',
					disabled && 'cursor-wait'
				)}
			>
				{input(checked, onChange, { id, disabled, 'aria-describedby': descriptionId })}
				<Mark checked={checked} />
				<span className="min-w-0 truncate">{label}</span>
			</label>
			{description && <span id={descriptionId} className="sr-only">{description}</span>}
		</div>
	);
}
