// @ts-ignore
import { useEntityProp } from "@wordpress/core-data";
import { useRef } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import {
	Button,
	DateTimePicker,
	Dropdown,
	PanelRow,
} from "@wordpress/components";
import {
	dateI18n,
	__experimentalGetSettings as getSettings,
} from "@wordpress/date";
// @ts-ignore
import moment from "moment";

const TIMEZONELESS_FORMAT = "YYYY-MM-DDTHH:mm:ss";

interface DatetimeControlProps {
	label: string;
	term: string;
	taxonomy: string;
	postType: string;
	type: "attach" | "detach";
}

interface ScheduleTermsMeta {
	type: "attach" | "detach";
	datetime: string;
	term: string;
	taxonomy: string;
}

interface PostMeta {
	[ key: string ]: any;

	schedule_terms: ScheduleTermsMeta[];
}

export const DatetimeControl = ( {
	term,
	taxonomy,
	label,
	postType,
	type,
}: DatetimeControlProps ) => {
	const [ meta, setMeta ]: [ PostMeta, ( meta: PostMeta ) => void ] = useEntityProp(
		"postType",
		postType,
		"meta"
	);
	const anchorRef = useRef();
	const dateSettings = getSettings();

	// @ts-ignore
	const [ siteFormat = dateSettings.formats.date ] = useEntityProp(
		"root",
		"site",
		"date_format"
	);
	// @ts-ignore
	const [ siteTimeFormat = dateSettings.formats.time ] = useEntityProp(
		"root",
		"site",
		"time_format"
	);

	const getTimezoneOffsetString = () => {
		// @ts-ignore
		const { timezone } = dateSettings;
		const [ hour, time ] = timezone.offset.toString().split( "." );
		return `${ Number( hour ) > 0 ? "+" : "-" }${ String(
			Math.abs( hour )
		).padStart( 2, "0" ) }:${ String(
			Math.floor( Number( `0.${ time || 0 }` ) * 60 )
		).padStart( 2, "0" ) }`;
	};

	const updateDatetime = ( datetime: string ) => {
		const otherItems = meta?.schedule_terms?.filter( ( item ) => {
			return !(
				item.term === term && item.type === type
			);
		} ) || [];
		setMeta( {
			...meta,
			schedule_terms: [
				...otherItems,
				datetime
					? {
						term,
						taxonomy,
						type,
						// convert to UTC.
						datetime: moment(
							`${ datetime }${ getTimezoneOffsetString() }`
						)
						.utc()
						.format(),
					}
					: null,
			].filter( ( e ): e is ScheduleTermsMeta => e !== null ),
		} );
	};

	const getDatetime = ( format = TIMEZONELESS_FORMAT ) => {
		const val = meta?.schedule_terms?.find( ( item ) => {
			return item.term === term && item.type === type;
		} );

		if ( val?.datetime ) {
			return moment( val.datetime )
			.utcOffset( getTimezoneOffsetString() )
			.format( format );
		}

		return undefined;
	};


	const datetime = getDatetime();

	return (
		// @ts-ignore
		<PanelRow ref={ anchorRef }>
			<span>{ label }</span>
			<Dropdown
				// @ts-ignore
				popoverProps={ { anchorRef: anchorRef.current } }
				position="bottom left"
				renderToggle={ ( { onToggle, isOpen } ) => (
					<>
						<Button
							onClick={ onToggle }
							aria-expanded={ isOpen }
							variant="tertiary"
						>
							{
								datetime
									// @ts-ignore
									? dateI18n(
										`${ siteFormat } ${ siteTimeFormat }`,
										datetime
									)
									: __( "none", "schedule-terms" )
							}
						</Button>
					</>
				) }
				renderContent={ () => (
					<div>
						<DateTimePicker
							currentDate={ datetime }
							onChange={ ( newDate ) => updateDatetime( newDate ) }
						/>
					</div>
				) }
			/>
		</PanelRow>
	);
};
