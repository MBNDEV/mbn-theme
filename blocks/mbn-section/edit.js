/**
 * MBN Section block editor component.
 *
 * @package CustomTheme
 */

import LayoutShellEdit from '../shared/LayoutShellEdit';

/**
 * @param {Object} props Block editor props.
 * @return {JSX.Element} MBN Section block editor.
 */
export default function Edit( props ) {
	return (
		<LayoutShellEdit
			{ ...props }
			blockSlug="mbn-section"
			wrapperClassName="relative isolate min-h-px w-full overflow-hidden"
			contentClassName="relative z-10 mx-auto w-full px-4 sm:px-6 lg:px-8"
		/>
	);
}
