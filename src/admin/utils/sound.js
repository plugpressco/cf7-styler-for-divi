/**
 * Small interface sounds, synthesized with the Web Audio API (no audio files).
 * Off by default; the Help menu turns them on for this browser.
 *
 * @package CF7_Mate
 */

const KEY = 'cf7m-sound';
let context = null;

export function soundOn() {
	try {
		return window.localStorage.getItem(KEY) === 'on';
	} catch (error) {
		return false;
	}
}

function audio() {
	const Context = window.AudioContext || window.webkitAudioContext;
	if (!Context) return null;
	if (!context) context = new Context();
	if (context.state === 'suspended') context.resume();
	return context;
}

function tone(ctx, frequency, start, length, type = 'sine', volume = 0.05) {
	const at = ctx.currentTime + start;
	const oscillator = ctx.createOscillator();
	const gain = ctx.createGain();
	oscillator.type = type;
	oscillator.frequency.setValueAtTime(frequency, at);
	gain.gain.setValueAtTime(0.0001, at);
	gain.gain.exponentialRampToValueAtTime(volume, at + 0.012);
	gain.gain.exponentialRampToValueAtTime(0.0001, at + length);
	oscillator.connect(gain);
	gain.connect(ctx.destination);
	oscillator.start(at);
	oscillator.stop(at + length + 0.02);
}

const sounds = {
	tick: (ctx) => tone(ctx, 880, 0, 0.07, 'triangle', 0.04),
	untick: (ctx) => tone(ctx, 600, 0, 0.07, 'triangle', 0.03),
	error: (ctx) => {
		tone(ctx, 247, 0, 0.16);
		tone(ctx, 196, 0.09, 0.2);
	},
	// A rising major arpeggio, like the banner's sun coming up.
	chime: (ctx) => [523.25, 659.25, 783.99, 1046.5].forEach((frequency, index) => tone(ctx, frequency, index * 0.08, 0.4, 'sine', 0.045)),
};

export function play(name) {
	if (!soundOn() || !sounds[name]) return;
	try {
		const ctx = audio();
		if (ctx) sounds[name](ctx);
	} catch (error) {
		// Sound is decoration; never let it break the page.
	}
}

export function setSoundOn(on) {
	try {
		window.localStorage.setItem(KEY, on ? 'on' : 'off');
	} catch (error) {
		// Private windows may block storage; sounds then stay off.
	}
	if (on) play('chime');
}
