import { __ } from '@wordpress/i18n';
import { featureGroups } from '../../admin/data/feature-groups';
import { Toggle } from '../../admin/components/Toggle';

const StepFeatures = ({ features, onFeaturesChange }) => {
    const config = typeof dcsOnboarding !== 'undefined' ? dcsOnboarding : {};
    const settings = features || config.features || {};
    return (
        <div className="cf7m-onboarding-step cf7m-step-features">
            <div className="cf7m-step-features__header">
                <span className="cf7m-step-label">{__('Step 2 of 4', 'cf7-styler-for-divi')}</span>
                <h2 className="cf7m-onboarding-title">{__('Choose features', 'cf7-styler-for-divi')}</h2>
                <p className="cf7m-onboarding-description">{__('All features are free. Choose whether to store responses, track views, or send submissions to webhook destinations. You can change these choices anytime.', 'cf7-styler-for-divi')}</p>
            </div>
            <div className="cf7m-features-list">
                {featureGroups.map(group => {
                    const items = (config.feature_metadata || []).filter(feature => group.keys.includes(feature.id) || (group.id === 'integrations' && !featureGroups.some(item => item.keys.includes(feature.id))));
                    return <section key={group.id} className="cf7m-onboarding-feature-group"><h3>{group.title}</h3>{items.map(feature => <div key={feature.id} className="cf7m-feature-item"><div><strong>{feature.name}</strong><p>{feature.desc}</p></div><Toggle checked={!!settings[feature.id]} label={feature.name} onChange={value => onFeaturesChange({ ...settings, [feature.id]: value })} /></div>)}</section>;
                })}
            </div>
        </div>
    );
};
export default StepFeatures;
