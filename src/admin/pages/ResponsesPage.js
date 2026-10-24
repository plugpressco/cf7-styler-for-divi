/**
 * Responses: stored submissions list and single response view.
 *
 * @package CF7_Mate
 */

import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
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
} from '@heroicons/react/24/outline';
import { Main } from '../components/Layout';
import {
	Avatar,
	Badge,
	Button,
	Card,
	CardTitle,
	Checkbox,
	EmptyState,
	LoadingState,
	NativeSelect,
	PageHeader,
	SearchInput,
	Table,
	TableCell,
	TableHead,
	TableRow,
	Toast,
	buttonVariants,
} from '../components/ui';
import { cn } from '../lib/utils';
import { getConfig } from '../utils/config';

const STATUS_OPTIONS = [
	{ label: __('All statuses', 'cf7-styler-for-divi'), value: 'all' },
	{ label: __('Unread', 'cf7-styler-for-divi'), value: 'new' },
	{ label: __('Read', 'cf7-styler-for-divi'), value: 'read' },
	{ label: __('Trash', 'cf7-styler-for-divi'), value: 'trash' },
];

const STATUS_LABELS = {
	new: { label: __('Unread', 'cf7-styler-for-divi'), tone: 'coral' },
	read: { label: __('Read', 'cf7-styler-for-divi'), tone: 'sage' },
	trash: { label: __('Trash', 'cf7-styler-for-divi'), tone: 'danger' },
};

const NONE = __('None', 'cf7-styler-for-divi');

function getFirstFieldValue(entry) {
	const data = entry?.data && typeof entry.data === 'object' ? entry.data : {};
	const firstKey = Object.keys(data)[0];
	const value = firstKey != null ? String(data[firstKey] ?? '').slice(0, 80) : '';
	return value || NONE;
}

function parseUserAgent(ua) {
	if (!ua || typeof ua !== 'string') return { browser: NONE, device: NONE };
	const u = ua.toLowerCase();
	let browser = NONE;
	if (u.includes('chrome') && !u.includes('edg')) browser = 'Chrome';
	else if (u.includes('firefox')) browser = 'Firefox';
	else if (u.includes('safari') && !u.includes('chrome')) browser = 'Safari';
	else if (u.includes('edg')) browser = 'Edge';
	let device = NONE;
	if (u.includes('mobile') || u.includes('android') || u.includes('iphone')) device = __('Mobile', 'cf7-styler-for-divi');
	else if (u.includes('ipad') || u.includes('tablet')) device = __('Tablet', 'cf7-styler-for-divi');
	else if (u.includes('mac') || u.includes('apple')) device = 'Apple';
	else if (u.includes('win')) device = 'Windows';
	return { browser, device };
}

function StatusBadge({ status }) {
	const item = STATUS_LABELS[status] || { label: status, tone: 'default' };
	return <Badge tone={item.tone}>{item.label}</Badge>;
}

/** Inline yes/no prompt that replaces an action until answered. */
function Confirm({ label, onYes, onNo }) {
	return (
		<span className="inline-flex items-center gap-2 text-[13px]">
			<span className="text-muted-foreground">{label}</span>
			<Button variant="danger" onClick={onYes}>{__('Yes', 'cf7-styler-for-divi')}</Button>
			<Button variant="link" className="text-muted-foreground" onClick={onNo}>{__('No', 'cf7-styler-for-divi')}</Button>
		</span>
	);
}

function DataList({ rows }) {
	return (
		<dl>
			{rows.map(([key, value]) => (
				<div key={key} className="grid grid-cols-[150px_minmax(0,1fr)] gap-x-4 gap-y-1 border-t border-divider px-[18px] py-2.5 text-[13px] first:border-0 max-[782px]:grid-cols-1">
					<dt className="text-muted-foreground">{key}</dt>
					<dd className="whitespace-pre-line break-words"><bdi>{value}</bdi></dd>
				</div>
			))}
		</dl>
	);
}

export function ResponsesPage({ entryId }) {
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
	const [toast, setToast] = useState(null);
	const restBase = '/cf7-styler/v1/entries';
	const apiSettings = getConfig();
	const apiRoot = (apiSettings.root || '').replace(/\/+$/, '');
	const apiNonce = apiSettings.nonce || '';
	const failed = () => setToast({ type: 'error', message: __('Could not update the response. Try again.', 'cf7-styler-for-divi') });

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
		} catch (e) {
			failed();
		}
	};

	const deletePermanently = async (id) => {
		try {
			await apiFetch({ path: `${restBase}/${id}`, method: 'DELETE' });
			setEntries((prev) => prev.filter((e) => e.id !== id));
			setSingleEntry(null);
			setSelectedIds((prev) => prev.filter((x) => x !== id));
			if (entryId === id) window.location.hash = '#/';
		} catch (e) {
			failed();
		}
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
		} catch (e) {
			failed();
		}
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
		} catch (e) {
			failed();
			fetchEntries();
		}
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
		} catch (e) {
			failed();
		}
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

	const toastNode = toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />;

	// ===== Single entry view =====
	if (entryId) {
		const { browser, device } = parseUserAgent(singleEntry?.user_agent);
		const fields = Object.entries(singleEntry?.data || {}).map(([key, value]) => [key, typeof value === 'object' ? JSON.stringify(value) : String(value)]);
		const details = singleEntry ? [
			[__('Form', 'cf7-styler-for-divi'), singleEntry.form_title || NONE],
			[__('Submitted', 'cf7-styler-for-divi'), singleEntry.created ? <span dir="ltr">{singleEntry.created}</span> : NONE],
			[__('IP address', 'cf7-styler-for-divi'), singleEntry.ip || NONE],
			[__('Browser', 'cf7-styler-for-divi'), browser],
			[__('Device', 'cf7-styler-for-divi'), device],
		] : [];
		const confirming = (action) => confirmPending?.id === singleEntry?.id && confirmPending?.action === action;
		return (
			<>
			<Main>
				<nav aria-label={__('Breadcrumb', 'cf7-styler-for-divi')} className="flex items-center gap-1.5 text-[13px] text-muted-foreground">
					<a href="#/" className="text-primary hover:underline">{__('Responses', 'cf7-styler-for-divi')}</a>
					<span aria-hidden="true">/</span>
					{/* translators: %d: response ID. */}
					<span>{sprintf(__('Response #%d', 'cf7-styler-for-divi'), entryId)}</span>
				</nav>

				{singleLoading && <LoadingState rows={4} />}
				{!singleLoading && !singleEntry && (
					<EmptyState icon={<InboxIcon />} title={__('Response not found.', 'cf7-styler-for-divi')} action={<a className={buttonVariants()} href="#/">{__('Back to responses', 'cf7-styler-for-divi')}</a>} />
				)}
				{!singleLoading && singleEntry && (
					<>
						<PageHeader
							/* translators: %d: response ID. */
							title={sprintf(__('Response #%d', 'cf7-styler-for-divi'), singleEntry.id)}
							meta={<StatusBadge status={singleEntry.status} />}
							actions={(
								<>
									<a href="#/" className={buttonVariants({ variant: 'ghost' })}>
										<ArrowLeftIcon aria-hidden="true" className="rtl:-scale-x-100" />
										{__('Back', 'cf7-styler-for-divi')}
									</a>
									{apiRoot && apiNonce && (
										<a
											href={`${apiRoot}${restBase}/${singleEntry.id}/print?_wpnonce=${encodeURIComponent(apiNonce)}`}
											target="_blank"
											rel="noopener noreferrer"
											className={buttonVariants()}
										>
											<PrinterIcon aria-hidden="true" />
											{__('Print / Save PDF', 'cf7-styler-for-divi')}
										</a>
									)}
									{singleEntry.status === 'trash' && (
										<Button onClick={() => updateStatus(singleEntry.id, 'read')}>
											<ArrowUturnLeftIcon aria-hidden="true" className="rtl:-scale-x-100" />
											{__('Restore', 'cf7-styler-for-divi')}
										</Button>
									)}
									{confirming(singleEntry.status === 'trash' ? 'delete' : 'trash') ? (
										<Confirm
											label={singleEntry.status === 'trash' ? __('Delete permanently?', 'cf7-styler-for-divi') : __('Move to trash?', 'cf7-styler-for-divi')}
											onYes={() => {
												setConfirmPending(null);
												if (singleEntry.status === 'trash') deletePermanently(singleEntry.id);
												else moveToTrash(singleEntry.id);
											}}
											onNo={() => setConfirmPending(null)}
										/>
									) : (
										<Button variant="danger-outline" onClick={() => setConfirmPending({ id: singleEntry.id, action: singleEntry.status === 'trash' ? 'delete' : 'trash' })}>
											<TrashIcon aria-hidden="true" />
											{singleEntry.status === 'trash' ? __('Delete permanently', 'cf7-styler-for-divi') : __('Move to trash', 'cf7-styler-for-divi')}
										</Button>
									)}
								</>
							)}
						/>
						<div className="flex flex-wrap items-start gap-4">
							<Card aria-labelledby="cf7m-response-fields" className="min-w-0 flex-[999_1_420px] overflow-hidden">
								<CardTitle id="cf7m-response-fields" className="border-b border-divider px-[18px] py-3.5">{__('Fields', 'cf7-styler-for-divi')}</CardTitle>
								{fields.length ? <DataList rows={fields} /> : <p className="px-[18px] py-4 text-[13px] text-muted-foreground">{__('No field data.', 'cf7-styler-for-divi')}</p>}
							</Card>
							<Card aria-labelledby="cf7m-response-details" className="min-w-0 flex-[1_1_300px] overflow-hidden">
								<CardTitle id="cf7m-response-details" className="border-b border-divider px-[18px] py-3.5">{__('Details', 'cf7-styler-for-divi')}</CardTitle>
								<DataList rows={details} />
							</Card>
						</div>
					</>
				)}
			</Main>
			{toastNode}
			</>
		);
	}

	// ===== List view =====
	return (
		<>
		<Main>
			<PageHeader
				title={__('Responses', 'cf7-styler-for-divi')}
				meta={total > 0 && <Badge>{total}</Badge>}
				description={__('Submissions saved on this site.', 'cf7-styler-for-divi')}
				actions={exportUrl && entries.length > 0 && (
					<a className={buttonVariants()} href={exportUrl}>
						<ArrowDownTrayIcon aria-hidden="true" />
						{__('Export', 'cf7-styler-for-divi')}
					</a>
				)}
			/>

			<Card className="overflow-hidden">
				<div className="flex flex-wrap items-center gap-2 border-b border-divider px-3.5 py-3">
					<NativeSelect
						aria-label={__('Filter by status', 'cf7-styler-for-divi')}
						value={statusFilter}
						options={STATUS_OPTIONS}
						onChange={(event) => {
							setStatusFilter(event.target.value);
							setCurrentPage(1);
						}}
					/>
					<NativeSelect
						aria-label={__('Filter by form', 'cf7-styler-for-divi')}
						className="max-w-[260px]"
						value={formIdFilter}
						options={formOptions}
						onChange={(event) => {
							setFormIdFilter(event.target.value);
							setCurrentPage(1);
						}}
					/>
					<SearchInput label={__('Search responses', 'cf7-styler-for-divi')} placeholder={__('Search responses', 'cf7-styler-for-divi')} value={searchTerm} onChange={setSearchTerm} className="w-[260px]" />

					{selectedIds.length > 0 && (
						<div className="ms-auto flex flex-wrap items-center gap-3 text-[13px]">
							{/* translators: %d: number of selected responses. */}
							<span className="text-muted-foreground">{sprintf(__('%d selected', 'cf7-styler-for-divi'), selectedIds.length)}</span>
							{confirmPending?.id === 'bulk' ? (
								<Confirm
									label={confirmPending.action === 'delete' ? __('Delete permanently?', 'cf7-styler-for-divi') : __('Move to trash?', 'cf7-styler-for-divi')}
									onYes={() => {
										const action = confirmPending.action;
										setConfirmPending(null);
										if (action === 'delete') bulkDeletePermanently();
										else bulkMoveToTrash();
									}}
									onNo={() => setConfirmPending(null)}
								/>
							) : (
								<Button size="sm" variant="danger-outline" onClick={() => setConfirmPending({ id: 'bulk', action: statusFilter === 'trash' ? 'delete' : 'trash' })}>
									{statusFilter === 'trash' ? __('Delete permanently', 'cf7-styler-for-divi') : __('Move to trash', 'cf7-styler-for-divi')}
								</Button>
							)}
							<Button variant="link" className="text-muted-foreground" onClick={() => setSelectedIds([])}>{__('Clear', 'cf7-styler-for-divi')}</Button>
						</div>
					)}
				</div>

				{loading && <LoadingState rows={4} className="p-3.5" />}
				{!loading && !entries.length && (
					<EmptyState
						className="rounded-none border-0"
						icon={<InboxIcon />}
						title={__('No responses yet', 'cf7-styler-for-divi')}
						description={__('New submissions show up here.', 'cf7-styler-for-divi')}
					/>
				)}
				{!loading && entries.length > 0 && (
					<Table>
						<thead>
							<tr>
								<TableHead className="w-11 pe-0">
									<Checkbox checked={allOnPageSelected} onChange={toggleSelectAll} aria-label={__('Select all', 'cf7-styler-for-divi')} />
								</TableHead>
								<TableHead>{__('Form', 'cf7-styler-for-divi')}</TableHead>
								<TableHead className="max-[782px]:hidden">{__('Preview', 'cf7-styler-for-divi')}</TableHead>
								<TableHead className="max-[600px]:hidden">{__('Submitted', 'cf7-styler-for-divi')}</TableHead>
								<TableHead className="w-24"><span className="sr-only">{__('Actions', 'cf7-styler-for-divi')}</span></TableHead>
							</tr>
						</thead>
						<tbody>
							{entries.map((e) => {
								const unread = e.status === 'new';
								const preview = getFirstFieldValue(e);
								return (
									<TableRow key={e.id}>
										<TableCell className="pe-0">
											{/* translators: %d: response ID. */}
											<Checkbox checked={selectedIds.includes(e.id)} onChange={() => toggleSelect(e.id)} aria-label={sprintf(__('Select response %d', 'cf7-styler-for-divi'), e.id)} />
										</TableCell>
										<TableCell className="py-3">
											<div className="flex items-center gap-3">
												<Avatar name={preview === NONE ? e.form_title : preview} seed={e.id} />
												<div className="min-w-0">
													<div className="flex min-w-0 items-center gap-2">
														<a href={`#/responses/${e.id}`} className={cn('max-w-[320px] truncate text-foreground hover:text-primary max-[600px]:max-w-[170px]', unread ? 'font-semibold' : 'font-medium')}>
															<bdi>{e.form_title || `#${e.form_id}`}</bdi>
														</a>
														{unread && (
															<>
																<span aria-hidden="true" title={__('Unread', 'cf7-styler-for-divi')} className="h-2 w-2 shrink-0 rounded-full bg-coral" />
																<span className="sr-only">{__('Unread', 'cf7-styler-for-divi')}</span>
															</>
														)}
													</div>
													<div className="text-xs text-muted-foreground">
														#{e.id}
														<span className="min-[601px]:hidden"> · <span dir="ltr">{e.created}</span></span>
													</div>
												</div>
											</div>
										</TableCell>
										<TableCell className="max-w-[320px] truncate text-foreground/80 max-[782px]:hidden"><bdi>{preview}</bdi></TableCell>
										<TableCell className="whitespace-nowrap text-muted-foreground max-[600px]:hidden"><span dir="ltr">{e.created}</span></TableCell>
										<TableCell>
											<div className="flex items-center justify-end gap-0.5">
												{confirmPending?.id === e.id ? (
													<Confirm
														label={confirmPending.action === 'delete' ? __('Delete?', 'cf7-styler-for-divi') : __('Trash?', 'cf7-styler-for-divi')}
														onYes={() => {
															const { action, id } = confirmPending;
															setConfirmPending(null);
															if (action === 'delete') deletePermanently(id);
															else moveToTrash(id);
														}}
														onNo={() => setConfirmPending(null)}
													/>
												) : (
													<>
														{e.status !== 'trash' && (
															<a href={`#/responses/${e.id}`} className={buttonVariants({ variant: 'ghost', size: 'icon' })} title={__('View', 'cf7-styler-for-divi')} aria-label={__('View', 'cf7-styler-for-divi')}>
																<EyeIcon aria-hidden="true" />
															</a>
														)}
														{e.status === 'trash' && (
															<Button variant="ghost" size="icon" title={__('Restore', 'cf7-styler-for-divi')} aria-label={__('Restore', 'cf7-styler-for-divi')} onClick={() => updateStatus(e.id, 'read')}>
																<ArrowUturnLeftIcon aria-hidden="true" className="rtl:-scale-x-100" />
															</Button>
														)}
														<Button
															variant="ghost"
															size="icon"
															className="hover:text-destructive"
															title={e.status === 'trash' ? __('Delete permanently', 'cf7-styler-for-divi') : __('Move to trash', 'cf7-styler-for-divi')}
															aria-label={e.status === 'trash' ? __('Delete permanently', 'cf7-styler-for-divi') : __('Move to trash', 'cf7-styler-for-divi')}
															onClick={() => setConfirmPending({ id: e.id, action: e.status === 'trash' ? 'delete' : 'trash' })}
														>
															<TrashIcon aria-hidden="true" />
														</Button>
													</>
												)}
											</div>
										</TableCell>
									</TableRow>
								);
							})}
						</tbody>
					</Table>
				)}

				{!loading && entries.length > 0 && (
					<div className="flex flex-wrap items-center justify-between gap-3 border-t border-divider px-3.5 py-2.5 text-xs text-muted-foreground">
						<div className="flex items-center gap-3">
							{/* translators: 1: current page, 2: total pages. */}
							<span>{sprintf(__('Page %1$d of %2$d', 'cf7-styler-for-divi'), currentPage, pages || 1)}</span>
							<NativeSelect
								aria-label={__('Per page', 'cf7-styler-for-divi')}
								selectClassName="h-[30px]"
								value={String(perPage)}
								/* translators: %d: responses per page. */
								options={[10, 25, 50, 100].map((n) => ({ label: sprintf(__('%d per page', 'cf7-styler-for-divi'), n), value: String(n) }))}
								onChange={(event) => {
									setPerPage(Number(event.target.value));
									setCurrentPage(1);
								}}
							/>
						</div>
						<div className="flex gap-1">
							<Button size="icon" disabled={currentPage <= 1} onClick={() => setCurrentPage((p) => Math.max(1, p - 1))} aria-label={__('Previous page', 'cf7-styler-for-divi')}>
								<ChevronLeftIcon aria-hidden="true" className="rtl:-scale-x-100" />
							</Button>
							<Button size="icon" disabled={currentPage >= (pages || 1)} onClick={() => setCurrentPage((p) => Math.min(pages || 1, p + 1))} aria-label={__('Next page', 'cf7-styler-for-divi')}>
								<ChevronRightIcon aria-hidden="true" className="rtl:-scale-x-100" />
							</Button>
						</div>
					</div>
				)}
			</Card>
		</Main>
		{toastNode}
		</>
	);
}
