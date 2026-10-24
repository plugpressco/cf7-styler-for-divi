/**
 * Admin Tailwind config. Utilities are scoped to #cf7-mate-app-root and
 * preflight is off, so nothing reaches WordPress's own screens or chrome.
 */
const token = (name) => `rgb(var(--cf7m-${name}) / <alpha-value>)`;

module.exports = {
	content: ['./src/admin/**/*.{js,jsx}', './src/components/**/*.{js,jsx}'],
	important: '#cf7-mate-app-root',
	corePlugins: { preflight: false },
	plugins: [],
	theme: {
		keyframes: {
			'cf7m-spin': { to: { transform: 'rotate(360deg)' } },
			'cf7m-pulse': { '50%': { opacity: '.5' } },
			'cf7m-pop': { '0%': { transform: 'scale(0)' }, '70%': { transform: 'scale(1.15)' }, '100%': { transform: 'scale(1)' } },
			'cf7m-toast-in': { '0%': { opacity: '0', transform: 'translateY(14px) scale(.96)' }, '100%': { opacity: '1', transform: 'none' } },
			'cf7m-toast-out': { to: { opacity: '0', transform: 'translateY(8px) scale(.98)' } },
			'cf7m-popup-in': { '0%': { opacity: '0', transform: 'translateY(18px) scale(.94)' }, '100%': { opacity: '1', transform: 'none' } },
			'cf7m-float': { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-3px)' } },
			'cf7m-sunrise': { '0%': { opacity: '.5', transform: 'translateY(22px)' }, '100%': { opacity: '1', transform: 'none' } },
		},
		animation: {
			none: 'none',
			spin: 'cf7m-spin .8s linear infinite',
			pulse: 'cf7m-pulse 2s ease-in-out infinite',
			pop: 'cf7m-pop .22s ease-out',
			'toast-in': 'cf7m-toast-in .45s cubic-bezier(.2, 1.5, .4, 1) both',
			'toast-out': 'cf7m-toast-out .2s ease-in both',
			'popup-in': 'cf7m-popup-in .55s cubic-bezier(.2, 1.4, .4, 1) both',
			float: 'cf7m-float 6s ease-in-out infinite',
			sunrise: 'cf7m-sunrise 1.2s cubic-bezier(.2, .8, .2, 1) .15s both',
		},
		extend: {
			colors: {
				foreground: token('foreground'),
				muted: { DEFAULT: token('muted'), foreground: token('muted-foreground') },
				card: token('card'),
				border: token('border'),
				divider: token('divider'),
				input: token('input'),
				hover: token('hover'),
				ring: token('ring'),
				primary: { DEFAULT: token('primary'), hover: token('primary-hover'), foreground: token('primary-foreground') },
				sage: { DEFAULT: token('sage'), border: token('sage-border'), line: token('sage-line'), foreground: token('primary') },
				coral: { DEFAULT: token('coral'), soft: token('coral-soft'), tint: token('coral-tint'), foreground: token('coral-foreground') },
				sand: { DEFAULT: token('sand'), foreground: token('sand-foreground') },
				success: token('success'),
				destructive: { DEFAULT: token('destructive'), tint: token('destructive-tint') },
			},
			borderRadius: {
				DEFAULT: '8px',
				box: '14px',
				tile: '10px',
			},
			boxShadow: {
				box: '0 1px 2px rgba(33, 55, 45, 0.05)',
				menu: '0 12px 32px rgba(33, 55, 45, 0.12)',
				toast: '0 10px 28px rgba(33, 55, 45, 0.24)',
			},
			fontFamily: {
				sans: ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Oxygen-Sans', 'Ubuntu', 'Cantarell', '"Helvetica Neue"', 'sans-serif'],
				mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Consolas', 'monospace'],
			},
			transitionTimingFunction: {
				spring: 'cubic-bezier(.3, 1.7, .5, 1)',
			},
		},
	},
};
