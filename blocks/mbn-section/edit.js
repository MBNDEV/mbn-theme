/**
 * MBN Section block editor component.
 *
 * @package CustomTheme
 */

import LayoutShellEdit from '../shared/LayoutShellEdit';
import { CENTERED_CONTENT_CLASSES } from '../shared/use-layout-styles';

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
			contentClassName={ CENTERED_CONTENT_CLASSES }
		/>
	);
}
