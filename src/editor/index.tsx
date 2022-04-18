import { registerPlugin } from "@wordpress/plugins";
import { PluginDocumentSettingPanel } from "@wordpress/edit-post";

const PluginDocumentSettingPanelDemo = () => (
	<PluginDocumentSettingPanel
		name="custom-panel"
		title="Custom Panel"
		className="custom-panel"
	>
		Custom Panel Contents
	</PluginDocumentSettingPanel>
);

registerPlugin("plugin-document-setting-panel-demo", {
	render: PluginDocumentSettingPanelDemo,
	icon: "palmtree",
});
