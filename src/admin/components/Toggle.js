/**
 * Toggle switch for feature on/off.
 *
 * @package CF7_Mate
 */

export function Toggle({ checked, onChange, disabled, label, id }) {
	return (
		<button
			type="button"
			role="switch"
			id={id}
			aria-label={label}
			aria-checked={checked}
			disabled={disabled}
			onClick={() => !disabled && onChange(!checked)}
			className={`cf7m-toggle ${checked ? 'cf7m-toggle--active' : ''} ${disabled ? 'cf7m-toggle--disabled' : ''}`}
		>
			<span className="cf7m-toggle__track">
				<span className="cf7m-toggle__thumb" />
			</span>
		</button>
	);
}
