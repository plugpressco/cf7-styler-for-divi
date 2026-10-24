import { useState, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ArrowPathIcon, ArrowTrendingUpIcon, ChartBarIcon, EnvelopeIcon, EyeIcon } from '@heroicons/react/24/outline';
import apiFetch from '@wordpress/api-fetch';
import { Main } from '../components/Layout';
import { Alert, Button, Card, EmptyState, IconChip, LoadingState, MeterBar, PageHeader, Table, TableCell, TableHead, TableRow } from '../components/ui';
import { cn } from '../lib/utils';
import { getConfig } from '../utils/config';

function Metric({ icon, tone, label, value }) {
	return (
		<Card as="div" className="px-[18px] py-4 transition-[transform,box-shadow] duration-200 hover:-translate-y-px hover:shadow-[0_6px_16px_rgba(33,55,45,.08)]">
			<dt className="flex flex-col items-start gap-3 text-[12.5px] text-muted-foreground">
				<IconChip tone={tone}>{icon}</IconChip>
				{label}
			</dt>
			<dd className="mt-0.5 text-[26px] font-semibold leading-tight tracking-[-0.5px] tabular-nums">{value}</dd>
		</Card>
	);
}

export function AnalyticsPage() {
	const [rows, setRows] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const fetchStats = useCallback(() => {
		setLoading(true);
		setError(null);
		apiFetch({ path: '/cf7-styler/v1/analytics/stats' })
			.then((data) => setRows(Array.isArray(data) ? data : []))
			.catch((err) => setError(err?.message || __('Could not load analytics.', 'cf7-styler-for-divi')))
			.finally(() => setLoading(false));
	}, []);
	useEffect(() => { fetchStats(); }, [fetchStats]);

	const views = rows.reduce((sum, row) => sum + (row.views || 0), 0);
	const submissions = rows.reduce((sum, row) => sum + (row.submissions || 0), 0);
	const best = rows.reduce((max, row) => Math.max(max, Number(row.conversion) || 0), 0);
	const responsesUrl = getConfig().responses_url || 'admin.php?page=cf7-mate-responses';
	const hasData = !loading && !error && rows.length > 0;

	return (
		<Main>
			<PageHeader
				title={__('Analytics', 'cf7-styler-for-divi')}
				actions={(
					<Button variant="ghost" onClick={fetchStats} disabled={loading} aria-busy={loading}>
						<ArrowPathIcon aria-hidden="true" className={cn(loading && 'animate-spin')} />
						{__('Refresh', 'cf7-styler-for-divi')}
					</Button>
				)}
			/>

			{loading && <LoadingState rows={3} />}
			{!loading && error && (
				<Alert variant="error" action={<Button size="sm" onClick={fetchStats}>{__('Try again', 'cf7-styler-for-divi')}</Button>}>{error}</Alert>
			)}
			{!loading && !error && rows.length === 0 && (
				<EmptyState icon={<ChartBarIcon />} title={__('No views yet', 'cf7-styler-for-divi')} description={__('Visit a page with a form to start counting.', 'cf7-styler-for-divi')} />
			)}

			{hasData && (
				<>
					<dl className="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-3">
						<Metric icon={<EyeIcon />} tone="sage" label={__('Views', 'cf7-styler-for-divi')} value={views.toLocaleString()} />
						<Metric icon={<EnvelopeIcon />} tone="coral" label={__('Submissions', 'cf7-styler-for-divi')} value={submissions.toLocaleString()} />
						<Metric icon={<ArrowTrendingUpIcon />} tone="sand" label={__('Conversion', 'cf7-styler-for-divi')} value={views ? `${Math.round((submissions / views) * 1000) / 10}%` : __('None', 'cf7-styler-for-divi')} />
					</dl>
					<Card>
						<Table>
							<caption className="px-3.5 pb-1.5 pt-3.5 text-start text-sm font-semibold">{__('Performance by form', 'cf7-styler-for-divi')}</caption>
							<thead>
								<tr>
									<TableHead>{__('Form', 'cf7-styler-for-divi')}</TableHead>
									<TableHead className="text-end">{__('Views', 'cf7-styler-for-divi')}</TableHead>
									<TableHead className="text-end">{__('Submissions', 'cf7-styler-for-divi')}</TableHead>
									<TableHead className="text-end">{__('Conversion', 'cf7-styler-for-divi')}</TableHead>
									<TableHead>{__('Last submission', 'cf7-styler-for-divi')}</TableHead>
								</tr>
							</thead>
							<tbody>
								{rows.map((row) => (
									<TableRow key={row.form_id}>
										<TableCell className="py-3">
											<a href={responsesUrl} className="font-medium text-foreground hover:text-primary"><bdi>{row.form_title || `#${row.form_id}`}</bdi></a>
											<span className="ms-2 text-xs text-muted-foreground">#{row.form_id}</span>
										</TableCell>
										<TableCell className="text-end tabular-nums">{row.views.toLocaleString()}</TableCell>
										<TableCell className="text-end tabular-nums">{row.submissions.toLocaleString()}</TableCell>
										<TableCell className="text-end tabular-nums">
											<span className="inline-flex items-center gap-2.5">
												<MeterBar value={Number(row.conversion) || 0} max={best} />
												<span className="min-w-[40px]">{row.conversion}%</span>
											</span>
										</TableCell>
										<TableCell className="whitespace-nowrap text-muted-foreground">{row.last_submission ? <span dir="ltr">{row.last_submission}</span> : __('None', 'cf7-styler-for-divi')}</TableCell>
									</TableRow>
								))}
							</tbody>
						</Table>
					</Card>
					<p className="text-xs text-muted-foreground">{__('All time. Submissions count saved responses. Conversion is an estimate.', 'cf7-styler-for-divi')}</p>
				</>
			)}
		</Main>
	);
}
