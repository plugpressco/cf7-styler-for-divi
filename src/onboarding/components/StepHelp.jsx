import { __ } from '@wordpress/i18n';
import { SaddleIntro } from '../../components/SaddleIntro';

const StepHelp = () => {
 const config = (typeof dcsOnboarding !== 'undefined' && dcsOnboarding.saddle) || {};
 return <div className="cf7m-onboarding-step cf7m-step-help">
  <div className="cf7m-step-header">
   <span className="cf7m-step-label">{__('Step 3 of 4 · Optional', 'cf7-styler-for-divi')}</span>
   <h2 className="cf7m-onboarding-title">{__('Build forms with your AI', 'cf7-styler-for-divi')}</h2>
   <p className="cf7m-onboarding-description">{__('Describe what you need. Connect an agent to help create and style it on your own site.', 'cf7-styler-for-divi')}</p>
  </div>
  <SaddleIntro config={config} compact />
 </div>;
};
export default StepHelp;
