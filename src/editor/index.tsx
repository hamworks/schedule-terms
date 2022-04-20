import { registerPlugin } from '@wordpress/plugins';
import { useSelect } from '@wordpress/data';
// @ts-ignore
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
// @ts-ignore
import { store as editorStore } from '@wordpress/editor';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { WP_Taxonomy_Name } from 'wp-types';
import { DatetimeControl } from './components/DatetimeControl';

interface Term {
	id: number;
	name: string;
	slug: string;
	term_group: number;
	term_taxonomy_id: number;
	taxonomy: WP_Taxonomy_Name | string;
	description: string;
	parent: number;
	count: number;
}
interface Props {
	currentPostType: string;
	taxonomies: Term[];
	terms: Record<string, Term[]>;
}

const ControlUI = ({ taxonomies, terms, currentPostType }: Props) => {
	return (
		<div>
			{taxonomies?.map((taxonomy) => (
				<div key={taxonomy.slug}>
					{terms[taxonomy.slug] &&
						terms[taxonomy.slug].length > 0 &&
						terms[taxonomy.slug]?.map((term) => (
							<div key={term.id}>
								<h4>
									{taxonomy.slug}: {term.name}
								</h4>
								<DatetimeControl
									label="Attach"
									term={term.slug}
									type="attach"
									postType={currentPostType}
								/>
								<DatetimeControl
									label="Detach"
									term={term.slug}
									type="detach"
									postType={currentPostType}
								/>
							</div>
						))}
				</div>
			))}
		</div>
	);
};

const PluginDocumentSetting = () => {
	const { postType, taxonomies, terms } = useSelect((select) => {
		// @ts-ignore
		const { getTaxonomies, getEntityRecords } = select(coreStore);
		// @ts-ignore
		const _postType = select(editorStore).getCurrentPostType();
		const _taxonomies = (getTaxonomies({ per_page: -1 }) || []).filter(
			(taxonomy) => taxonomy.types.includes(_postType)
		);
		const _terms = Object.fromEntries(
			_taxonomies.map((taxonomy) => {
				const terms = getEntityRecords('taxonomy', taxonomy.slug, {
					per_page: -1,
				})?.filter(({ meta: { use_schedule } }) => use_schedule);
				return [taxonomy.slug, terms];
			})
		);

		return {
			postType: _postType,
			taxonomies: _taxonomies,
			terms: _terms,
		};
	});

	return (
		<PluginDocumentSettingPanel
			name="custom-panel"
			title="Custom Panel"
			className="custom-panel"
		>
			<ControlUI
				currentPostType={postType}
				taxonomies={taxonomies}
				terms={terms}
			/>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin('schedule-terms', {
	render: PluginDocumentSetting,
	icon: 'palmtree',
});
