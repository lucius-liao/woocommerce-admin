/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { Button, CheckboxControl, FormToggle } from '@wordpress/components';
import { includes, filter, get } from 'lodash';
import { withDispatch, withSelect } from '@wordpress/data';
import { getSetting } from '@woocommerce/wc-admin-settings';
import { H, Card } from '@woocommerce/components';
import { ONBOARDING_STORE_NAME } from '@woocommerce/data';
import { recordEvent } from '@woocommerce/tracks';

/**
 * Internal dependencies
 */
import ProductTypeLabel from './label';
import './style.scss';

class ProductTypes extends Component {
	constructor( props ) {
		super();
		const profileItems = get( props, 'profileItems', {} );

		const { productTypes = {} } = getSetting( 'onboarding', {} );
		const defaultProductTypes = Object.keys( productTypes ).filter(
			( key ) => !! productTypes[ key ].default
		);

		this.state = {
			error: null,
			isMonthlyPricing: true,
			selected: profileItems.product_types || defaultProductTypes,
		};

		this.onContinue = this.onContinue.bind( this );
		this.onChange = this.onChange.bind( this );
	}

	async validateField() {
		const error = this.state.selected.length
			? null
			: __(
					'Please select at least one product type',
					'woocommerce-admin'
			  );
		this.setState( { error } );
	}

	async onContinue() {
		await this.validateField();
		if ( this.state.error ) {
			return;
		}

		const {
			createNotice,
			goToNextStep,
			isError,
			updateProfileItems,
		} = this.props;

		recordEvent( 'storeprofiler_store_product_type_continue', {
			product_type: this.state.selected,
		} );
		await updateProfileItems( { product_types: this.state.selected } );

		if ( ! isError ) {
			goToNextStep();
		} else {
			createNotice(
				'error',
				__(
					'There was a problem updating your product types.',
					'woocommerce-admin'
				)
			);
		}
	}

	onChange( slug ) {
		this.setState(
			( state ) => {
				if ( includes( state.selected, slug ) ) {
					return {
						selected:
							filter( state.selected, ( value ) => {
								return value !== slug;
							} ) || [],
					};
				}
				const newSelected = state.selected;
				newSelected.push( slug );
				return {
					selected: newSelected,
				};
			},
			() => this.validateField()
		);
	}

	render() {
		const { productTypes = {} } = getSetting( 'onboarding', {} );
		const { error, isMonthlyPricing, selected } = this.state;

		return (
			<div className="woocommerce-profile-wizard__product-types">
				<H className="woocommerce-profile-wizard__header-title">
					{ __(
						'What type of products will be listed?',
						'woocommerce-admin'
					) }
				</H>
				<H className="woocommerce-profile-wizard__header-subtitle">
					{ __( 'Choose any that apply' ) }
				</H>

				<Card>
					<div className="woocommerce-profile-wizard__checkbox-group">
						{ Object.keys( productTypes ).map( ( slug ) => {
							return (
								<CheckboxControl
									key={ slug }
									label={
										<ProductTypeLabel
											description={
												productTypes[ slug ].description
											}
											label={ productTypes[ slug ].label }
											annualPrice={
												productTypes[ slug ]
													.yearly_price
											}
											isMonthlyPricing={
												isMonthlyPricing
											}
											moreUrl={
												productTypes[ slug ].more_url
											}
											slug={ slug }
										/>
									}
									onChange={ () => this.onChange( slug ) }
									checked={ selected.includes( slug ) }
									className="woocommerce-profile-wizard__checkbox"
								/>
							);
						} ) }
						<div className="woocommerce-profile-wizard__product-types-pricing-toggle woocommerce-profile-wizard__checkbox">
							{ __(
								'Display monthly prices',
								'woocommerce-admin'
							) }
							<FormToggle
								checked={ isMonthlyPricing }
								onChange={ () =>
									this.setState( {
										isMonthlyPricing: ! isMonthlyPricing,
									} )
								}
							/>
						</div>
						{ error && (
							<span className="woocommerce-profile-wizard__error">
								{ error }
							</span>
						) }
					</div>

					<div className="woocommerce-profile-wizard__card-actions">
						<Button
							isPrimary
							onClick={ this.onContinue }
							disabled={ ! selected.length }
						>
							{ __( 'Continue', 'woocommerce-admin' ) }
						</Button>
					</div>
				</Card>
			</div>
		);
	}
}

export default compose(
	withSelect( ( select ) => {
		const { getProfileItems, getOnboardingError } = select(
			ONBOARDING_STORE_NAME
		);

		return {
			isError: Boolean( getOnboardingError( 'updateProfileItems' ) ),
			profileItems: getProfileItems(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { updateProfileItems } = dispatch( ONBOARDING_STORE_NAME );
		const { createNotice } = dispatch( 'core/notices' );

		return {
			createNotice,
			updateProfileItems,
		};
	} )
)( ProductTypes );
