import { useState, useEffect, render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import OnboardingModal from './components/OnboardingModal';
import './onboarding.scss';

const TOTAL_STEPS = 4;

const Onboarding = () => {
	const [isVisible, setIsVisible] = useState(false);
	const [currentStep, setCurrentStep] = useState(1);
	const [featureSettings, setFeatureSettings] = useState(() => (typeof dcsOnboarding !== 'undefined' && dcsOnboarding.features) || {});

	useEffect(() => {
		fetch(dcsOnboarding.ajax_url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams({
				action: 'cf7m_check_onboarding_status',
				nonce: dcsOnboarding.nonce,
			}),
		})
			.then((res) => res.json())
			.then((data) => {
				if (!data.success || !data.data.should_show) return;
				const show = () => {
					setIsVisible(true);
					setCurrentStep(data.data.current_step || 1);
				};
				window.requestAnimationFrame(() => setTimeout(show, 150));
			})
			.catch((err) => console.error('Onboarding status:', err));
	}, []);

	const handleClose = () => skipOnboarding();
	const handleSkip = () => skipOnboarding();

	const skipOnboarding = () => {
		fetch(dcsOnboarding.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'cf7m_skip_onboarding',
				nonce: dcsOnboarding.nonce,
			}),
		})
			.then((response) => response.json())
			.then((data) => {
				if (data.success) {
					setIsVisible(false);
				}
			})
			.catch((error) => {
				console.error('Error skipping onboarding:', error);
			});
	};

	const handleNext = () => {
		if (currentStep < TOTAL_STEPS) {
			const nextStep = currentStep + 1;
			updateStep(nextStep);
		} else {
			completeOnboarding();
		}
	};

	const handlePrev = () => {
		if (currentStep > 1) {
			const prevStep = currentStep - 1;
			updateStep(prevStep);
		}
	};

	const updateStep = (step) => {
		fetch(dcsOnboarding.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'cf7m_next_onboarding_step',
				nonce: dcsOnboarding.nonce,
				step: step,
			}),
		})
			.then((response) => response.json())
			.then((data) => {
				if (data.success) {
					setCurrentStep(step);
				}
			})
			.catch((error) => {
				console.error('Error updating step:', error);
			});
	};

	const completeOnboarding = () => {
		return fetch(dcsOnboarding.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'cf7m_complete_onboarding',
				nonce: dcsOnboarding.nonce,
				features: JSON.stringify(featureSettings),
			}),
		})
			.then((response) => response.json())
			.then((data) => {
				if (!data.success) {
					throw new Error('Onboarding settings were not saved.');
				}
				setIsVisible(false);
			});
	};

	const handleFeatureToggle = (newSettings) => {
		setFeatureSettings(newSettings);
	};

	if (!isVisible) {
		return null;
	}

	return (
		<OnboardingModal
			currentStep={currentStep}
			onClose={handleClose}
			onSkip={handleSkip}
			onNext={handleNext}
			onPrev={handlePrev}
			onComplete={completeOnboarding}
			featureSettings={featureSettings}
			onFeatureToggle={handleFeatureToggle}
		/>
	);
};

domReady(() => {
	const container = document.getElementById('cf7m-onboarding-root');
	if (container) {
		render(<Onboarding />, container);
	}
});
