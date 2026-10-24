/**
 * Text input, native select and pill search field.
 *
 * @package CF7_Mate
 */

import { ChevronUpDownIcon, MagnifyingGlassIcon } from '@heroicons/react/20/solid';
import { cn } from '../../lib/utils';

const field = 'h-[34px] rounded border border-input bg-card text-[13px] text-foreground transition-[border-color,box-shadow] hover:border-foreground/25 focus:border-ring focus:ring-[3px] focus:ring-ring/15 disabled:cursor-not-allowed disabled:opacity-60';

export function Input({ className, type = 'text', ...props }) {
	return <input type={type} className={cn(field, 'w-full px-3 placeholder:text-muted-foreground/80 aria-[invalid=true]:border-destructive', className)} {...props} />;
}

export function NativeSelect({ className, selectClassName, options = [], ...props }) {
	return (
		<span className={cn('relative inline-flex min-w-0', className)}>
			<select className={cn(field, 'w-full cursor-pointer appearance-none pe-8 ps-3', selectClassName)} {...props}>
				{options.map((option) => (
					<option key={option.value} value={option.value}>{option.label}</option>
				))}
			</select>
			<ChevronUpDownIcon aria-hidden="true" className="pointer-events-none absolute end-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
		</span>
	);
}

export function SearchInput({ label, value, onChange, placeholder, className }) {
	return (
		<label className={cn('relative flex h-[34px] w-60 max-w-full items-center gap-2 rounded-full border border-input bg-card px-3 text-muted-foreground transition-[border-color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/15', className)}>
			<MagnifyingGlassIcon aria-hidden="true" className="h-4 w-4" />
			<span className="sr-only">{label}</span>
			<input
				type="search"
				value={value}
				placeholder={placeholder}
				onChange={(event) => onChange(event.target.value)}
				className="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-[13px] text-foreground placeholder:text-muted-foreground focus:ring-0"
			/>
		</label>
	);
}
