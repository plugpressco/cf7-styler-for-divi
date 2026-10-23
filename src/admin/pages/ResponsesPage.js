/**
 * Responses page: full-width Notion-style list of form submissions
 * + single entry view. Built with @wordpress/components.
 *
 * @package CF7_Mate
 */

import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	SearchControl,
	SelectControl,
	CheckboxControl,
	Spinner,
} from '@wordpress/components';
import {
	EyeIcon,
	TrashIcon,
	ArrowUturnLeftIcon,
	ArrowDownTrayIcon,
	ChevronLeftIcon,
	ChevronRightIcon,
	InboxIcon,
	PrinterIcon,
	ArrowLeftIcon,
	ArrowPathIcon,
} from '@heroicons/react/24/outline';

const STATUS_OPTIONS = [
	{ label: __('All statuses', 'cf7-styler-for-divi'), value: 'all' },
	{ label: __('New', 'cf7-styler-for-divi'), value: 'new' },
	{ label: __('Read', 'cf7-styler-for-divi'), value: 'read' },
	{ label: __('Trash', 'cf7-styler-for-divi'), value: 'trash' },
];

function getFirstFieldValue(entry) {
	const data = entry?.data && typeof entry.data === 'object' ? entry.data : {};
	const firstKey = Object.keys(data)[0];
	return firstKey != null ? String(data[firstKey] ?? '').slice(0, 80) : '—';
}

function parseUserAgent(ua) {
	if (!ua || typeof ua !== 'string') return { browser: '—', device: '—' };
	const u = ua.toLowerCase();
	let browser = '—';
	if (u.includes('chrome') && !u.includes('edg')) browser = 'Chrome';
	else if (u.includes('firefox')) browser = 'Firefox';
	else if (u.includes('safari') && !u.includes('chrome')) browser = 'Safari';
	else if (u.includes('edg')) browser = 'Edge';
	let device = '—';
	if (u.includes('mobile') || u.includes('android') || u.includes('iphone')) device = 'Mobile';
	else if (u.includes('ipad') || u.includes('tablet')) device = 'Tablet';
	else if (u.includes('mac') || u.includes('apple')) device = 'Apple';
	else if (u.includes('win')) device = 'Windows';
	return { browser, device };
}

function StatusPill({ status }) {
	const label = status === 'trash' ? __('Trash', 'cf7-styler-for-divi') : status;
	return <span className={`cf7m-resp-pill cf7m-resp-pill--${status}`}>{label}</span>;
}

export function ResponsesPage({ entryId, onBack }) {
	const [entries, setEntries] = useState([]);
	const [total, setTotal] = useState(0);
	const [pages, setPages] = useState(0);
	const [currentPage, setCurrentPage] = useState(1);
	const [perPage, setPerPage] = useState(25);
	const [loading, setLoading] = useState(true);
	const [singleEntry, setSingleEntry] = useState(null);
	const [singleLoading, setSingleLoading] = useState(false);
	const [statusFilter, setStatusFilter] = useState('all');
	const [formIdFilter, setFormIdFilter] = useState('all');
	const [searchTerm, setSearchTerm] = useState('');
	const [selectedIds, setSelectedIds] = useState([]);
	// confirmPending: { id: number|'bulk', action: 'trash'|'delete' } | null
	const [confirmPending, setConfirmPending] = useState(null);
	const restBase = '/cf7-styler/v1/entries';
	const apiSettings = typeof dcsCF7Styler !== 'undefined' ? dcsCF7Styler : (window.wp?.apiSettings || {});
	const apiRoot = (apiSettings.root || '').replace(/\/+$/, '');
	const apiNonce = apiSettings.nonce || '';

	// Auto-cancel inline confirm after 5s
	useEffect(() => {
		if (!confirmPending) return;
		const t = setTimeout(() => setConfirmPending(null), 5000);
		return () => clearTimeout(t);
	}, [confirmPending]);

	const fetchEntries = useCallback(() => {
		const params = new URLSearchParams({ per_page: perPage, page: currentPage });
		if (statusFilter !== 'all') params.set('status', statusFilter);
		if (formIdFilter !== 'all') params.set('form_id', formIdFilter);
		if (searchTerm.trim()) params.set('search', searchTerm.trim());
		setLoading(true);
		apiFetch({ path: `${restBase}?${params}` })
			.then((r) => {
				setEntries(r.items || []);
				setTotal(r.total ?? 0);
				setPages(r.pages ?? 1);
			})
			.catch(() => {
				setEntries([]);
				setTotal(0);
				setPages(0);
			})
			.finally(() => setLoading(false));
	}, [perPage, currentPage, statusFilter, formIdFilter, searchTerm]);

	useEffect(() => {
		if (!entryId) fetchEntries();
	}, [entryId, fetchEntries]);

	useEffect(() => {
		if (entryId) {
			setSingleLoading(true);
			apiFetch({ path: `${restBase}/${entryId}` })
				.then((r) => {
					setSingleEntry(r);
					if (r?.status === 'new') {
						apiFetch({
							path: `${restBase}/${r.id}`,
							method: 'POST',
							data: { status: 'read' },
						}).then(() =>
							setSingleEntry((s) => (s ? { ...s, status: 'read' } : null))
						);
					}
				})
				.catch(() => setSingleEntry(null))
				.finally(() => setSingleLoading(false));
		} else {
			setSingleEntry(null);
		}
	}, [entryId]);

	const exportUrl = useMemo(() => {
		if (!apiRoot || !apiNonce) return '';
		const params = new URLSearchParams({ per_page: 500 });
		if (statusFilter !== 'all') params.set('status', statusFilter);
		if (formIdFilter !== 'all') params.set('form_id', formIdFilter);
		if (searchTerm.trim()) params.set('search', searchTerm.trim());
		params.set('_wpnonce', apiNonce);
		return `${apiRoot}${restBase}/export?${params}`;
	}, [statusFilter, formIdFilter, searchTerm, apiRoot, apiNonce]);

	const moveToTrash = async (id) => {
		try {
			await apiFetch({
				path: `${restBase}/${id}`,
				method: 'POST',
				data: { status: 'trash' },
			});
			setEntries((prev) => prev.filter((e) => e.id !== id));
			if (singleEntry?.id === id) {
				setSingleEntry((s) => (s ? { ...s, status: 'trash' } : null));
			}
			setSelectedIds((prev) => prev.filter((x) => x !== id));
			if (entryId === id) window.location.hash = '#/';
		} catch (e) {}
	};

	const deletePermanently = async (id) => {
		try {
			await apiFetch({ path: `${restBase}/${id}`, method: 'DELETE' });
			setEntries((prev) => prev.filter((e) => e.id !== id));
			setSingleEntry(null);
			setSelectedIds((prev) => prev.filter((x) => x !== id));
			if (entryId === id) window.location.hash = '#/';
		} catch (e) {}
	};

	const updateStatus = async (id, status) => {
		try {
			await apiFetch({
				path: `${restBase}/${id}`,
				method: 'POST',
				data: { status },
			});
			setEntries((prev) =>
				prev.map((e) => (e.id === id ? { ...e, status } : e))
			);
			if (singleEntry?.id === id) {
				setSingleEntry((s) => (s ? { ...s, status } : null));
			}
		} catch (e) {}
	};

	const bulkMoveToTrash = async () => {
		if (!selectedIds.length) return;
		try {
			for (const id of selectedIds) {
				await apiFetch({
					path: `${restBase}/${id}`,
					method: 'POST',
					data: { status: 'trash' },
				});
			}
			setEntries((prev) => prev.filter((e) => !selectedIds.includes(e.id)));
			setSelectedIds([]);
		} catch (e) {}
	};

	const bulkDeletePermanently = async () => {
		if (!selectedIds.length) return;
		try {
			await apiFetch({
				path: `${restBase}/bulk-delete`,
				method: 'DELETE',
				data: { ids: selectedIds },
			});
			setEntries((prev) => prev.filter((e) => !selectedIds.includes(e.id)));
			setSelectedIds([]);
			setSingleEntry(null);
		} catch (e) {}
	};

	const toggleSelect = (id) =>
		setSelectedIds((prev) =>
			prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]
		);

	const allOnPageSelected =
		entries.length > 0 && selectedIds.length >= entries.length;

	const toggleSelectAll = () => {
		if (allOnPageSelected) setSelectedIds([]);
		else setSelectedIds((entries || []).map((e) => e.id));
	};

	const formOptions = useMemo(() => {
		const map = new Map();
		(entries || []).forEach((e) => {
			if (!map.has(e.form_id)) {
				map.set(e.form_id, e.form_title_with_id || e.form_title || `#${e.form_id}`);
			}
		});
		return [
			{ label: __('All forms', 'cf7-styler-for-divi'), value: 'all' },
			...Array.from(map, ([id, title]) => ({ label: title, value: String(id) })),
		];
	}, [entries]);

	// ===== Single entry view =====
	if (entryId) {
		return (
			<div className="cf7m-resp">
				<nav className="cf7m-resp__crumb" aria-label={__('Breadcrumb', 'cf7-styler-for-divi')}>
					<a href="#/" className="cf7m-resp__crumb-link">
						{__('Responses', 'cf7-styler-for-divi')}
					</a>
					<span className="cf7m-resp__crumb-sep" aria-hidden="true">/</span>
					<span className="cf7m-resp__crumb-current">
						{__('Response', 'cf7-styler-for-divi')} #{entryId}
					</span>
				</nav>

				{singleLoading ? (
					<div className="cf7m-resp__empty">
						<Spinner />
					</div>
				) : !singleEntry ? (
					<div className="cf7m-resp__empty">
						<p>{__('Response not found.', 'cf7-styler-for-divi')}</p>
					</div>
				) : (
					<div className="cf7m-resp__single">
						<div className="cf7m-resp__single-main">
							<section className="cf7m-resp__panel">
								<header className="cf7m-resp__panel-header">
									<h2 className="cf7m-resp__panel-title">
										{__('Submitted Data', 'cf7-styler-for-divi')}
									</h2>
								</header>
								<div className="cf7m-resp__panel-body">
									{Object.entries(singleEntry.data || {}).length === 0 ? (
										<p className="cf7m-resp__muted">
											{__('No field data.', 'cf7-styler-for-divi')}
										</p>
									) : (
										<dl className="cf7m-resp__data">
											{Object.entries(singleEntry.data || {}).map(([key, value]) => (
												<div key={key} className="cf7m-resp__data-row">
													<dt className="cf7m-resp__data-key">{key}</dt>
													<dd className="cf7m-resp__data-val">
														{typeof value === 'object'
															? JSON.stringify(value)
															: String(value)}
													</dd>
												</div>
											))}
										</dl>
									)}
								</div>
							</section>

							<section className="cf7m-resp__panel">
								<header className="cf7m-resp__panel-header">
									<h2 className="cf7m-resp__panel-title">
										{__('Submission Info', 'cf7-styler-for-divi')}
									</h2>
								</header>
								<div className="cf7m-resp__panel-body">
									{(() => {
										const { browser, device } = parseUserAgent(singleEntry.user_agent);
										const meta = [
											['ID', `#${singleEntry.id}`],
											[__('Form', 'cf7-styler-for-divi'), singleEntry.form_title || '—'],
											[__('IP', 'cf7-styler-for-divi'), singleEntry.ip || '—'],
											[__('Browser', 'cf7-styler-for-divi'), browser],
											[__('Device', 'cf7-styler-for-divi'), device],
											[__('Date', 'cf7-styler-for-divi'), singleEntry.created || '—'],
										];
										return (
											<dl className="cf7m-resp__data">
												{meta.map(([k, v]) => (
													<div key={k} className="cf7m-resp__data-row">
														<dt className="cf7m-resp__data-key">{k}</dt>
														<dd className="cf7m-resp__data-val">{v}</dd>
													</div>
												))}
												<div className="cf7m-resp__data-row">
													<dt className="cf7m-resp__data-key">
														{__('Status', 'cf7-styler-for-divi')}
													</dt>
													<dd className="cf7m-resp__data-val">
														<StatusPill status={singleEntry.status} />
													</dd>
												</div>
											</dl>
										);
									})()}
								</div>
							</section>
						</div>

						<aside className="cf7m-resp__single-side">
							<div className="cf7m-resp__panel">
								<header className="cf7m-resp__panel-header">
									<h2 className="cf7m-resp__panel-title">
										{__('Actions', 'cf7-styler-for-divi')}
									</h2>
								</header>
								<div className="cf7m-resp__panel-body cf7m-resp__actions">
									<a href="#/" className="cf7m-resp__action-btn">
										<ArrowLeftIcon className="cf7m-resp__btn-icon" aria-hidden="true" />
										{__('Back to list', 'cf7-styler-for-divi')}
									</a>
									{apiRoot && apiNonce && (
										<a
											href={`${apiRoot}${restBase}/${singleEntry.id}/print?_wpnonce=${encodeURIComponent(apiNonce)}`}
											target="_blank"
											rel="noopener noreferrer"
											className="cf7m-resp__action-btn"
										>
											<PrinterIcon className="cf7m-resp__btn-icon" aria-hidden="true" />
											{__('Print / Save PDF', 'cf7-styler-for-divi')}
										</a>
									)}
									{singleEntry.status === 'trash' ? (
										<>
											<button
												type="button"
												className="cf7m-resp__action-btn"
												onClick={() => updateStatus(singleEntry.id, 'read')}
											>
												<ArrowPathIcon className="cf7m-resp__btn-icon" aria-hidden="true" />
												{__('Restore', 'cf7-styler-for-divi')}
											</button>
											{confirmPending?.id === singleEntry.id && confirmPending?.action === 'delete' ? (
												<div className="cf7m-resp__action-confirm">
													<span>{__('Delete permanently?', 'cf7-styler-for-divi')}</span>
													<button
														type="button"
														className="cf7m-resp__confirm-yes"
														onClick={() => { setConfirmPending(null); deletePermanently(singleEntry.id); }}
													>
														{__('Yes', 'cf7-styler-for-divi')}
													</button>
													<button
														type="button"
														className="cf7m-resp__confirm-cancel"
														onClick={() => setConfirmPending(null)}
													>
														{__('Cancel', 'cf7-styler-for-divi')}
													</button>
												</div>
											) : (
												<button
													type="button"
													className="cf7m-resp__action-btn cf7m-resp__action-btn--danger"
													onClick={() => setConfirmPending({ id: singleEntry.id, action: 'delete' })}
												>
													<TrashIcon className="cf7m-resp__btn-icon" aria-hidden="true" />
													{__('Delete permanently', 'cf7-styler-for-divi')}
												</button>
											)}
										</>
									) : confirmPending?.id === singleEntry.id && confirmPending?.action === 'trash' ? (
										<div className="cf7m-resp__action-confirm">
											<span>{__('Move to trash?', 'cf7-styler-for-divi')}</span>
											<button
												type="button"
												className="cf7m-resp__confirm-yes"
												onClick={() => { setConfirmPending(null); moveToTrash(singleEntry.id); }}
											>
												{__('Yes', 'cf7-styler-for-divi')}
											</button>
											<button
												type="button"
												className="cf7m-resp__confirm-cancel"
												onClick={() => setConfirmPending(null)}
											>
												{__('Cancel', 'cf7-styler-for-divi')}
											</button>
										</div>
									) : (
										<button
											type="button"
											className="cf7m-resp__action-btn cf7m-resp__action-btn--danger"
											onClick={() => setConfirmPending({ id: singleEntry.id, action: 'trash' })}
										>
											<TrashIcon className="cf7m-resp__btn-icon" aria-hidden="true" />
											{__('Move to trash', 'cf7-styler-for-divi')}
										</button>
									)}
								</div>
							</div>
						</aside>
					</div>
				)}
			</div>
		);
	}

	// ===== List view =====
	return (
		<div className="cf7m-resp">
			<header className="cf7m-resp__head">
				<div><div className="cf7m-resp__head-titles">
					<h1 className="cf7m-resp__title">
						{__('Responses', 'cf7-styler-for-divi')}
					</h1>
					{total > 0 && (
						<span className="cf7m-resp__count">
							{total}
						</span>
					)}
				</div><p className="cf7m-resp__description">{__('Review and manage your form submissions.', 'cf7-styler-for-divi')}</p></div>
				{exportUrl && entries.length > 0 && (
					<a className="cf7m-resp__export" href={exportUrl}>
						<ArrowDownTrayIcon className="cf7m-resp__icon" aria-hidden="true" />
						{__('Export', 'cf7-styler-for-divi')}
					</a>
				)}
			</header>

			<div className="cf7m-resp__card">
			<div className="cf7m-resp__toolbar">
				<div className="cf7m-resp__filters">
					<SelectControl
						__nextHasNoMarginBottom
						value={statusFilter}
						options={STATUS_OPTIONS}
						onChange={(v) => {
							setStatusFilter(v);
							setCurrentPage(1);
						}}
						aria-label={__('Filter by status', 'cf7-styler-for-divi')}
					/>
					<SelectControl
						__nextHasNoMarginBottom
						value={formIdFilter}
						options={formOptions}
						onChange={(v) => {
							setFormIdFilter(v);
							setCurrentPage(1);
						}}
						aria-label={__('Filter by form', 'cf7-styler-for-divi')}
					/>
				</div>
				<div className="cf7m-resp__search">
					<SearchControl
						__nextHasNoMarginBottom
						value={searchTerm}
						onChange={(v) => setSearchTerm(v)}
						placeholder={__('Search responses…', 'cf7-styler-for-divi')}
					/>
				</div>
			</div>

			{selectedIds.length > 0 && (
				<div className="cf7m-resp__bulk">
					<span className="cf7m-resp__bulk-count">
						{__('%d selected', 'cf7-styler-for-divi').replace(
							'%d',
							selectedIds.length
						)}
					</span>
					<div className="cf7m-resp__bulk-actions">
						{confirmPending?.id === 'bulk' ? (
							<>
								<span className="cf7m-resp__confirm-label">
									{confirmPending.action === 'delete'
										? __('Delete permanently?', 'cf7-styler-for-divi')
										: __('Move to trash?', 'cf7-styler-for-divi')}
								</span>
								<button
									type="button"
									className="cf7m-resp__confirm-yes"
									onClick={() => {
										const action = confirmPending.action;
										setConfirmPending(null);
										if (action === 'delete') bulkDeletePermanently();
										else bulkMoveToTrash();
									}}
								>
									{__('Yes', 'cf7-styler-for-divi')}
								</button>
								<button
									type="button"
									className="cf7m-resp__confirm-cancel"
									onClick={() => setConfirmPending(null)}
								>
									{__('Cancel', 'cf7-styler-for-divi')}
								</button>
							</>
						) : statusFilter === 'trash' ? (
							<Button
								variant="secondary"
								isDestructive
								onClick={() => setConfirmPending({ id: 'bulk', action: 'delete' })}
							>
								{__('Delete permanently', 'cf7-styler-for-divi')}
							</Button>
						) : (
							<Button
								variant="secondary"
								onClick={() => setConfirmPending({ id: 'bulk', action: 'trash' })}
							>
								{__('Move to trash', 'cf7-styler-for-divi')}
							</Button>
						)}
						<Button variant="tertiary" onClick={() => setSelectedIds([])}>
							{__('Clear', 'cf7-styler-for-divi')}
						</Button>
					</div>
				</div>
			)}

			<div className="cf7m-resp__table-wrap">
				{loading ? (
					<div className="cf7m-resp__loading">
						<Spinner />
						<span>{__('Loading…', 'cf7-styler-for-divi')}</span>
					</div>
				) : !entries.length ? (
					<div className="cf7m-resp__empty">
						<InboxIcon className="cf7m-resp__empty-icon" aria-hidden="true" />
						<p className="cf7m-resp__empty-title">
							{__('No responses yet', 'cf7-styler-for-divi')}
						</p>
						<p className="cf7m-resp__empty-sub">
							{__('Form submissions will appear here.', 'cf7-styler-for-divi')}
						</p>
					</div>
				) : (
					<table className="cf7m-resp__table">
						<thead>
							<tr>
								<th className="cf7m-resp__th cf7m-resp__th--check">
									<CheckboxControl
										__nextHasNoMarginBottom
										checked={allOnPageSelected}
										onChange={toggleSelectAll}
										aria-label={__('Select all', 'cf7-styler-for-divi')}
									/>
								</th>
								<th className="cf7m-resp__th">
									{__('Form', 'cf7-styler-for-divi')}
								</th>
								<th className="cf7m-resp__th">
									{__('Preview', 'cf7-styler-for-divi')}
								</th>
								<th className="cf7m-resp__th">
									{__('Submitted', 'cf7-styler-for-divi')}
								</th>
								<th className="cf7m-resp__th cf7m-resp__th--actions" />
							</tr>
						</thead>
						<tbody>
							{entries.map((e) => (
								<tr key={e.id} className={`cf7m-resp__row${e.status === 'new' ? ' is-unread' : ''}`}>
									<td className="cf7m-resp__td cf7m-resp__td--check">
										<CheckboxControl
											__nextHasNoMarginBottom
											checked={selectedIds.includes(e.id)}
											onChange={() => toggleSelect(e.id)}
											aria-label={__('Select response', 'cf7-styler-for-divi')}
										/>
									</td>
									<td className="cf7m-resp__td cf7m-resp__td--strong">
										<span
											className={`cf7m-resp__dot cf7m-resp__dot--${e.status}`}
											aria-label={e.status}
										/>
										<a href={`#/responses/${e.id}`} className="cf7m-resp__id-link">
											{e.form_title_with_id || e.form_title || `#${e.id}`}
										</a>
									</td>
									<td className="cf7m-resp__td cf7m-resp__td--muted cf7m-resp__td--truncate">
										{getFirstFieldValue(e)}
									</td>
									<td className="cf7m-resp__td cf7m-resp__td--muted cf7m-resp__td--nowrap">
										{e.created}
									</td>
									<td className="cf7m-resp__td cf7m-resp__td--actions">
										<div className="cf7m-resp__row-actions">
											{confirmPending?.id === e.id ? (
												<>
													<span className="cf7m-resp__confirm-label">
														{confirmPending.action === 'delete'
															? __('Delete?', 'cf7-styler-for-divi')
															: __('Trash?', 'cf7-styler-for-divi')}
													</span>
													<button
														type="button"
														className="cf7m-resp__confirm-yes"
														onClick={() => {
															const action = confirmPending.action;
															const id = confirmPending.id;
															setConfirmPending(null);
															if (action === 'delete') deletePermanently(id);
															else moveToTrash(id);
														}}
													>
														{__('Yes', 'cf7-styler-for-divi')}
													</button>
													<button
														type="button"
														className="cf7m-resp__confirm-cancel"
														onClick={() => setConfirmPending(null)}
													>
														{__('No', 'cf7-styler-for-divi')}
													</button>
												</>
											) : (
												<>
													{e.status !== 'trash' && (
														<a
															href={`#/responses/${e.id}`}
															className="cf7m-resp__icon-btn"
															title={__('View', 'cf7-styler-for-divi')}
															aria-label={__('View', 'cf7-styler-for-divi')}
														>
															<EyeIcon aria-hidden="true" />
														</a>
													)}
													{e.status === 'trash' ? (
														<>
															<button
																type="button"
																className="cf7m-resp__icon-btn"
																title={__('Restore', 'cf7-styler-for-divi')}
																aria-label={__('Restore', 'cf7-styler-for-divi')}
																onClick={() => updateStatus(e.id, 'read')}
															>
																<ArrowUturnLeftIcon aria-hidden="true" />
															</button>
															<button
																type="button"
																className="cf7m-resp__icon-btn cf7m-resp__icon-btn--danger"
																title={__('Delete permanently', 'cf7-styler-for-divi')}
																aria-label={__('Delete permanently', 'cf7-styler-for-divi')}
																onClick={() => setConfirmPending({ id: e.id, action: 'delete' })}
															>
																<TrashIcon aria-hidden="true" />
															</button>
														</>
													) : (
														<button
															type="button"
															className="cf7m-resp__icon-btn cf7m-resp__icon-btn--danger"
															title={__('Move to trash', 'cf7-styler-for-divi')}
															aria-label={__('Move to trash', 'cf7-styler-for-divi')}
															onClick={() => setConfirmPending({ id: e.id, action: 'trash' })}
														>
															<TrashIcon aria-hidden="true" />
														</button>
													)}
												</>
											)}
										</div>
									</td>
								</tr>
							))}
						</tbody>
					</table>
				)}
			</div>

			{!loading && entries.length > 0 && (
				<footer className="cf7m-resp__pagination">
					<div className="cf7m-resp__pagination-info">
						<span>
							{__('Page %1$d of %2$d', 'cf7-styler-for-divi')
								.replace('%1$d', currentPage)
								.replace('%2$d', pages || 1)}
						</span>
						<SelectControl
							__nextHasNoMarginBottom
							value={String(perPage)}
							options={[10, 25, 50, 100].map((n) => ({
								label: __('%d per page', 'cf7-styler-for-divi').replace('%d', n),
								value: String(n),
							}))}
							onChange={(v) => {
								setPerPage(Number(v));
								setCurrentPage(1);
							}}
							aria-label={__('Per page', 'cf7-styler-for-divi')}
						/>
					</div>
					<div className="cf7m-resp__pagination-nav">
						<Button
							variant="tertiary"
							size="small"
							disabled={currentPage <= 1}
							onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
							icon={<ChevronLeftIcon className="cf7m-resp__icon" />}
							label={__('Previous page', 'cf7-styler-for-divi')}
							showTooltip
						/>
						<Button
							variant="tertiary"
							size="small"
							disabled={currentPage >= (pages || 1)}
							onClick={() => setCurrentPage((p) => Math.min(pages || 1, p + 1))}
							icon={<ChevronRightIcon className="cf7m-resp__icon" />}
							label={__('Next page', 'cf7-styler-for-divi')}
							showTooltip
						/>
					</div>
				</footer>
			)}
			</div>
		</div>
	);
}
