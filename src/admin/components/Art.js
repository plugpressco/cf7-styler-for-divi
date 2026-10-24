/**
 * Small pieces of the WordPress.org banner: Fuji, the sun and the mist.
 * Decorative only (aria-hidden), colors taken from docs/brand.
 *
 * @package CF7_Mate
 */

import { cn } from '../lib/utils';

function Mountain({ id }) {
	return (
		<>
			<path d="M36 132 C76 124 104 100 118 68 L134 68 C148 100 176 124 216 132 Z" fill={`url(#${id}-fuji)`} />
			<path d="M118 68 L134 68 L140 82 L134 78 L129 86 L125 79 L120 86 L115 80 L111 84 Z" fill="#fff" />
			<path d="M126 68 L134 68 C148 100 176 124 216 132 L170 132 C150 116 136 96 126 68 Z" fill="#93B1A2" opacity=".35" />
		</>
	);
}

function Defs({ id, mist }) {
	return (
		<defs>
			<linearGradient id={`${id}-fuji`} x1="0" y1="0" x2="0" y2="1">
				<stop offset="0" stopColor="#A9C2B5" />
				<stop offset="1" stopColor="#D5E2DA" />
			</linearGradient>
			<radialGradient id={`${id}-sun`} cx="38%" cy="35%" r="75%">
				<stop offset="0" stopColor="#F4A79A" />
				<stop offset="1" stopColor="#E47E71" />
			</radialGradient>
			<linearGradient id={`${id}-mist`} x1="0" y1="0" x2="0" y2="1">
				<stop offset="0" stopColor={mist} stopOpacity="0" />
				<stop offset="1" stopColor={mist} />
			</linearGradient>
		</defs>
	);
}

/** Fuji with a slowly floating sun, for the setup card corner. Mirrored in RTL so the sun stays near the corner, away from the text. */
export function FujiArt({ className }) {
	const id = 'cf7m-fuji-art';
	return (
		<svg viewBox="0 0 250 132" aria-hidden="true" focusable="false" className={cn('pointer-events-none rtl:-scale-x-100', className)}>
			<Defs id={id} mist="#EEF3EF" />
			<g className="animate-float">
				<circle cx="184" cy="38" r="30" fill="#F4A79A" opacity=".18" />
				<circle cx="184" cy="38" r="21" fill={`url(#${id}-sun)`} />
			</g>
			<Mountain id={id} />
			<path d="M0 132 C24 112 52 104 84 110 C108 114 120 106 146 104 C176 102 204 112 250 108 L250 132 Z" fill="#C3D5CA" opacity=".55" />
			<path d="M20 132 C60 118 92 116 128 120 C168 124 200 116 250 122 L250 132 Z" fill="#ADC6B8" opacity=".45" />
			<rect y="104" width="250" height="28" fill={`url(#${id}-mist)`} />
		</svg>
	);
}

/** The sun rising behind Fuji, for the setup finished popup. */
export function SunriseArt({ className }) {
	const id = 'cf7m-sunrise-art';
	return (
		<svg viewBox="40 30 172 102" aria-hidden="true" focusable="false" className={cn('pointer-events-none', className)}>
			<Defs id={id} mist="#EEF3EF" />
			<rect x="40" y="30" width="172" height="102" fill="#EEF3EF" />
			<g className="animate-sunrise">
				<circle cx="160" cy="60" r="24" fill="#F4A79A" opacity=".2" />
				<circle cx="160" cy="60" r="16" fill={`url(#${id}-sun)`} />
			</g>
			<Mountain id={id} />
			<rect x="40" y="108" width="172" height="24" fill={`url(#${id}-mist)`} />
		</svg>
	);
}
