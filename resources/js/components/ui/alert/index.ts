import Action from "./alert-action.svelte";
import Description from "./alert-description.svelte";
import Title from "./alert-title.svelte";
import Root from "./Alert.svelte";
export { alertVariants, type AlertVariant } from "./Alert.svelte";

export {
	Root,
	Description,
	Title,
	Action,
	//
	Root as Alert,
	Description as AlertDescription,
	Title as AlertTitle,
	Action as AlertAction,
};
