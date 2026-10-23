/**
 * Toast notification (auto-dismiss).
 *
 * @package CF7_Mate
 */

import { useEffect } from '@wordpress/element';

export function Toast({ message, type, onClose }) {
	useEffect(() => {
		const timer = setTimeout(onClose, 3000);
		return () => clearTimeout(timer);
	}, [onClose]);

	return (
		<div className={`cf7m-toast cf7m-toast--${type}`} role={type === 'error' ? 'alert' : 'status'}>
			<span>{message}</span>
		</div>
	);
}
