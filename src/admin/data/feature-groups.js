import { __ } from '@wordpress/i18n';
import { Squares2X2Icon, CursorArrowRaysIcon, ArrowPathIcon, PuzzlePieceIcon } from '@heroicons/react/24/outline';

export const featureGroups = [
	{ id: 'layout', title: __('Layout & logic', 'cf7-styler-for-divi'), icon: Squares2X2Icon, keys: ['grid_layout', 'multi_column', 'multi_step', 'conditional', 'presets'] },
	{ id: 'fields', title: __('Form fields', 'cf7-styler-for-divi'), icon: CursorArrowRaysIcon, keys: ['star_rating', 'range_slider', 'phone_number', 'separator', 'heading', 'image', 'icon'] },
	{ id: 'submissions', title: __('Submissions & automation', 'cf7-styler-for-divi'), icon: ArrowPathIcon, keys: ['database_entries', 'analytics', 'webhook', 'form_scheduling', 'email_routing', 'redirect', 'partial_save'] },
	{ id: 'integrations', title: __('Builders & AI', 'cf7-styler-for-divi'), icon: PuzzlePieceIcon, keys: ['cf7_module', 'bricks_module', 'elementor_module', 'gutenberg_module', 'ai_form_generator'] },
];
