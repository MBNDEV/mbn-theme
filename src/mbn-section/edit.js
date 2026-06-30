import { LayoutShellEdit } from '../shared/controls';
import { WRAPPER } from '../shared/layout';

export default function Edit( props ) {
	return (
		<LayoutShellEdit
			{ ...props }
			blockSlug="mbn-section"
			wrapperClassName={ WRAPPER }
			contentClassName="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8"
		/>
	);
}
