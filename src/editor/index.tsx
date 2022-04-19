import { registerPlugin } from "@wordpress/plugins";
import { useSelect } from "@wordpress/data";
// @ts-ignore
import { store as coreStore, useEntityProp } from "@wordpress/core-data";
// @ts-ignore
import { store as editorStore } from "@wordpress/editor";
import { PluginDocumentSettingPanel } from "@wordpress/edit-post";
import { Button, DateTimePicker } from "@wordpress/components";
import { WP_Taxonomy_Name } from "wp-types"; // TODO: replace to stable api.

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
	//const { timezone } = getDateSettings();
	const [meta, setMeta] = useEntityProp("postType", currentPostType, "meta");

	const updateDatetime = (key: string) => {
		return (term: string, datetime: string) => {
			setMeta({
				...meta,
				[key]: [
					...meta[key]?.filter((item) => item.term !== term),
					{
						term,
						datetime,
					},
				],
			});
		};
	};

	const updateAttachDateTime = updateDatetime(
		"use_schedule_set_attach_datetime"
	);
	const updateDetachDateTime = updateDatetime(
		"use_schedule_set_detach_datetime"
	);

	const getDatetime = (key: string) => {
		return (term: string) => {
			if (!Array.isArray(meta[key])) {
				return "";
			}
			const { datetime } = meta[key].find((item) => item.term === term);
			return datetime;
		};
	};

	const getAttachDateTime = getDatetime("use_schedule_set_attach_datetime");
	const getDetachDateTime = getDatetime("use_schedule_set_detach_datetime");

	return (
		<div>
			{taxonomies?.map((taxonomy) => (
				<div key={taxonomy.slug}>
					{taxonomy.slug}
					{terms[taxonomy.slug]?.map((term) => {
						console.log(meta);
						return (
							<div key={term.id}>
								<h4>{term.name}</h4>
								<h5>Attach Datetime</h5>
								<Button>Attach Datetime</Button>
								<DateTimePicker
									currentDate={getAttachDateTime(term.slug)}
									onChange={(newDate) =>
										updateAttachDateTime(term.slug, newDate)
									}
								/>
								<h5>Detach DateTime</h5>
								<DateTimePicker
									currentDate={getDetachDateTime(term.slug)}
									onChange={(newDate) =>
										updateDetachDateTime(term.slug, newDate)
									}
								/>
							</div>
						);
					})}
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
				const terms = getEntityRecords("taxonomy", taxonomy.slug, {
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

registerPlugin("schedule-terms", {
	render: PluginDocumentSetting,
	icon: "palmtree",
});
