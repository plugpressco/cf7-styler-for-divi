/** App frame shared by every screen: header plus a padded main area. */
import { __ } from '@wordpress/i18n';
import { Header } from './Header';
import { Button, LoadingState, PageHeader, EmptyState, buttonVariants } from './ui';
import { cn } from '../lib/utils';
import { getConfig } from '../utils/config';

export function AppFrame({ children }) {
	return (
		<div className="cf7m-wrap min-w-0">
			<Header />
			{children}
		</div>
	);
}

export function Main({ narrow = false, className, children }) {
	return <main className={cn('cf7m-content flex w-full flex-col gap-4 px-5 pb-10 pt-5', narrow && 'max-w-[900px]', className)}>{children}</main>;
}

export function LoadingScreen() {
	return (
		<Main>
			<LoadingState rows={4} />
		</Main>
	);
}

export function LoadError({ title, message, onRetry }) {
	return (
		<Main narrow>
			<div role="alert" className="flex flex-col gap-3">
				<PageHeader title={title} description={message} />
				<div>
					<Button variant="primary" onClick={onRetry}>{__('Try again', 'cf7-styler-for-divi')}</Button>
				</div>
			</div>
		</Main>
	);
}

/** Shown when the feature behind a screen is off. */
export function FeatureOff({ title, message }) {
	const dash = getConfig().dash_url || 'admin.php?page=cf7-mate';
	return (
		<Main narrow>
			<PageHeader title={title} />
			<EmptyState
				title={message}
				action={<a className={buttonVariants({ variant: 'primary' })} href={`${dash}#/features`}>{__('Go to Features', 'cf7-styler-for-divi')}</a>}
			/>
		</Main>
	);
}
