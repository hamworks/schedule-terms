// @ts-ignore
import { useEntityProp } from '@wordpress/core-data';
import { useRef } from '@wordpress/element';
import {
	Button,
	DateTimePicker,
	Dropdown,
	PanelRow,
} from '@wordpress/components';
import { __experimentalGetSettings as getSettings } from '@wordpress/date';
// @ts-ignore
import moment from 'moment';

const TIMEZONELESS_FORMAT = 'YYYY-MM-DDTHH:mm:ss';

interface DatetimeControlProps {
	label: string;
	term: string;
	taxonomy: string;
	postType: string;
	type: 'attach' | 'detach';
}

export const DatetimeControl = ( {
	term,
	taxonomy,
	label,
	postType,
	type,
}: DatetimeControlProps ) => {
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );
	const anchorRef = useRef();

	const getTimezoneOffsetString = () => {
		// @ts-ignore
		const { timezone } = getSettings();
		const [ hour, time ] = timezone.offset.toString().split( '.' );
		return `${ Number( hour ) > 0 ? '+' : '-' }${ String( Math.abs( hour ) ).padStart( 2, '0' ) }:${ String(
			Math.floor( Number( `0.${ time || 0 }` ) * 60 )
		).padStart( 2, '0' ) }`;
	};

	const updateDatetime = ( datetime: string ) => {
		const otherItems = meta.schedule_terms?.filter( ( item ) => {
			return !(
				item.term === term && item.type === type
			);
		} );
		setMeta( {
			...meta,
			schedule_terms: [
				...otherItems,
				datetime && {
					term,
					taxonomy,
					type,
					// convert to UTC.
					datetime: moment(`${ datetime }${ getTimezoneOffsetString() }`).utc().format(),
				},
			].filter( ( e ) => e ),
		} );
	};

	const getDatetime = () => {
		const val = meta.schedule_terms?.find( ( item ) => {
			return item.term === term && item.type === type;
		} );

		if ( val?.datetime ) {
			return moment(val.datetime).utcOffset( getTimezoneOffsetString() ).format( TIMEZONELESS_FORMAT );
		}

		return undefined;
	};

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
							{ getDatetime() || 'none' }
						</Button>
					</>
				) }
				renderContent={ () => (
					<div>
						<DateTimePicker
							currentDate={ getDatetime() }
							onChange={ ( newDate ) => updateDatetime( newDate ) }
						/>
					</div>
				) }
			/>
		</PanelRow>
	);
};
