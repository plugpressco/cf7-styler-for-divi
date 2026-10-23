import { useState, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner, Button } from '@wordpress/components';
import { ChartBarIcon } from '@heroicons/react/24/outline';
import apiFetch from '@wordpress/api-fetch';

export function AnalyticsPage() {
	const [rows, setRows] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const fetchStats = useCallback(() => {
		setLoading(true);
		setError(null);
		apiFetch({ path: '/cf7-styler/v1/analytics/stats' })
			.then(data => setRows(Array.isArray(data) ? data : []))
			.catch(err => setError(err?.message || __('Could not load analytics.', 'cf7-styler-for-divi')))
			.finally(() => setLoading(false));
	}, []);
	useEffect(() => { fetchStats(); }, [fetchStats]);
	const views = rows.reduce((sum, row) => sum + (row.views || 0), 0);
	const submissions = rows.reduce((sum, row) => sum + (row.submissions || 0), 0);
	const responsesUrl = typeof dcsCF7Styler !== 'undefined' ? dcsCF7Styler.responses_url : 'admin.php?page=cf7-mate-responses';
	return (
		<main className="cf7m-resp">
			<div className="cf7m-page-heading"><div><h1>{__('Analytics', 'cf7-styler-for-divi')}</h1><p>{__('A clearer picture of how your forms perform.', 'cf7-styler-for-divi')}</p></div><Button variant="secondary" onClick={fetchStats} disabled={loading}>{__('Refresh', 'cf7-styler-for-divi')}</Button></div>
			{loading && <div className="cf7m-resp__loading" role="status"><Spinner />{__('Loading analytics…', 'cf7-styler-for-divi')}</div>}
			{!loading && error && <div className="cf7m-tool-notice cf7m-tool-notice--error" role="alert">{error}<Button variant="tertiary" onClick={fetchStats}>{__('Try again', 'cf7-styler-for-divi')}</Button></div>}
			{!loading && !error && rows.length === 0 && <div className="cf7m-resp__card cf7m-resp__empty"><ChartBarIcon className="cf7m-resp__empty-icon" aria-hidden="true" /><h2 className="cf7m-resp__empty-title">{__('Your form insights start here', 'cf7-styler-for-divi')}</h2><p className="cf7m-resp__empty-sub">{__('Visit a page with a form to start tracking views.', 'cf7-styler-for-divi')}</p></div>}
			{!loading && !error && rows.length > 0 && <>
				<dl className="cf7m-metrics"><div className="cf7m-metric"><dt>{__('Form views', 'cf7-styler-for-divi')}</dt><dd>{views.toLocaleString()}</dd></div><div className="cf7m-metric"><dt>{__('Saved submissions', 'cf7-styler-for-divi')}</dt><dd>{submissions.toLocaleString()}</dd></div><div className="cf7m-metric"><dt>{__('Estimated conversion', 'cf7-styler-for-divi')}</dt><dd>{views ? `${Math.round(submissions / views * 100)}%` : '—'}</dd></div></dl>
				<div className="cf7m-resp__card"><div className="cf7m-analytics__caption"><h2>{__('Performance by form', 'cf7-styler-for-divi')}</h2><span>{__('All time', 'cf7-styler-for-divi')}</span></div><div className="cf7m-resp__table-wrap"><table className="cf7m-resp__table"><thead><tr>
					{[__('Form', 'cf7-styler-for-divi'), __('Views', 'cf7-styler-for-divi'), __('Submissions', 'cf7-styler-for-divi'), __('Conversion', 'cf7-styler-for-divi'), __('Last submission', 'cf7-styler-for-divi')].map((label, index) => <th key={label} scope="col" className={`cf7m-resp__th${index > 0 && index < 4 ? ' cf7m-resp__th--num' : ''}`}>{label}</th>)}
				</tr></thead><tbody>{rows.map(row => <tr key={row.form_id} className="cf7m-resp__row"><td className="cf7m-resp__td cf7m-resp__td--strong"><a href={responsesUrl} className="cf7m-resp__id-link">{row.form_title || `#${row.form_id}`}</a><span className="cf7m-resp__td--sub">#{row.form_id}</span></td><td className="cf7m-resp__td cf7m-resp__td--num">{row.views.toLocaleString()}</td><td className="cf7m-resp__td cf7m-resp__td--num">{row.submissions.toLocaleString()}</td><td className="cf7m-resp__td cf7m-resp__td--num"><span className="cf7m-analytics__rate">{row.conversion}%</span></td><td className="cf7m-resp__td cf7m-resp__td--muted">{row.last_submission || '—'}</td></tr>)}</tbody></table></div></div>
				<p className="cf7m-page-note">{__('All-time views and saved submissions. Submission counts require Form responses and exclude deleted or trashed entries. Conversion is an estimate.', 'cf7-styler-for-divi')}</p>
			</>}
		</main>
	);
}
