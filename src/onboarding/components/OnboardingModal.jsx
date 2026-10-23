/**
 * Onboarding Modal - Full Screen Component (UserFeedback Style)
 * Closeable via X button, overlay click, or Escape key.
 *
 * @since 3.0.0
 */

import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import CF7MateLogo from '../../components/CF7MateLogo';
import StepWelcome from './StepWelcome';
import StepFeatures from './StepFeatures';
import StepHelp from './StepHelp';
import StepFinish from './StepFinish';

const TOTAL_STEPS = 4;

const OnboardingModal = ({
	currentStep,
	onClose,
	onSkip,
	onNext,
	onPrev,
	onComplete,
	featureSettings,
	onFeatureToggle,
}) => {
	// Escape key closes the modal
	useEffect(() => {
		const handleEscape = (e) => {
			if (e.key === 'Escape') {
				onSkip();
			}
		};
		window.addEventListener('keydown', handleEscape);
		return () => window.removeEventListener('keydown', handleEscape);
	}, [onSkip]);

	const renderStep = () => {
		switch (currentStep) {
			case 1:
				return <StepWelcome />;
			case 2:
				return <StepFeatures features={featureSettings} onFeaturesChange={onFeatureToggle} />;
			case 3:
				return <StepHelp />;
			case 4:
				return <StepFinish onComplete={onComplete} />;
			default:
				return <StepWelcome />;
		}
	};

	const getNextButtonText = () => {
		if (currentStep === 1) return __("Let's get started", 'cf7-styler-for-divi');
		if (currentStep === TOTAL_STEPS) return __('Finish', 'cf7-styler-for-divi');
		return __('Next', 'cf7-styler-for-divi');
	};

	const version = typeof dcsOnboarding !== 'undefined' && dcsOnboarding.version
		? dcsOnboarding.version
		: '3.0.0';

	return (
		<div
			className="cf7m-onboarding-overlay"
			role="dialog"
			aria-modal="true"
			aria-label={__('CF7 Mate onboarding', 'cf7-styler-for-divi')}
			onClick={(e) => {
				if (e.target === e.currentTarget) onSkip();
			}}
		>
			{/* Header – logo left, progress center, Exit right */}
			<div className="cf7m-onboarding-header" onClick={(e) => e.stopPropagation()}>
				<div className="cf7m-onboarding-header-inner">
					<div className="cf7m-onboarding-logo">
						<CF7MateLogo width={32} height={32} />
						<span className="cf7m-onboarding-logo-text" style={{ fontWeight: 700 }}>{__('CF7 Mate', 'cf7-styler-for-divi')}</span>
					</div>
					<div className="cf7m-onboarding-progress-wrap">
						<div className="cf7m-progress-bar">
							{[1, 2, 3, 4].map((step, index) => (
								<div key={step} className="cf7m-progress-step">
									<div
										className={`cf7m-progress-dot ${
											step < currentStep ? 'completed' : step === currentStep ? 'active' : ''
										}`}
									>
										{step < currentStep ? (
											<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3">
												<polyline points="20 6 9 17 4 12" />
											</svg>
										) : (
											<span className="cf7m-progress-num">{step}</span>
										)}
									</div>
									{index < TOTAL_STEPS - 1 && (
										<div className={`cf7m-progress-line ${step < currentStep ? 'completed' : ''}`} />
									)}
								</div>
							))}
						</div>
					</div>
					<button
						type="button"
						className="cf7m-exit-setup"
						onClick={onSkip}
						aria-label={__('Exit Guided Setup', 'cf7-styler-for-divi')}
					>
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
							<line x1="18" y1="6" x2="6" y2="18" />
							<line x1="6" y1="6" x2="18" y2="18" />
						</svg>
						<span className="cf7m-exit-setup-text">{__('Exit setup', 'cf7-styler-for-divi')}</span>
					</button>
				</div>
			</div>

			{/* Centered card – SureFORMS style */}
			<div className="cf7m-onboarding-content-wrap" onClick={(e) => e.stopPropagation()}>
				<div className="cf7m-onboarding-card" role="region" aria-label={__('Setup step', 'cf7-styler-for-divi')}>
					<div className="cf7m-onboarding-content">{renderStep()}</div>
					<div className="cf7m-onboarding-footer">
						{currentStep > 1 && currentStep < TOTAL_STEPS && (
							<button type="button" className="cf7m-onboarding-btn cf7m-onboarding-btn-back" onClick={onPrev}>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
									<polyline points="15 18 9 12 15 6" />
								</svg>
								{__('Back', 'cf7-styler-for-divi')}
							</button>
						)}
						<div className="cf7m-onboarding-footer-right">
							{currentStep < TOTAL_STEPS && (
								<>
									<button
										type="button"
										className="cf7m-onboarding-skip"
										onClick={onSkip}
										aria-label={__('Skip onboarding', 'cf7-styler-for-divi')}
									>
										{__('Skip for now', 'cf7-styler-for-divi')}
									</button>
									<button className="cf7m-onboarding-btn cf7m-onboarding-btn-primary" onClick={onNext}>
										{getNextButtonText()}
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
											<polyline points="9 18 15 12 9 6" />
										</svg>
									</button>
								</>
							)}
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default OnboardingModal;
