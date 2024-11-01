import {useState, useEffect} from '@wordpress/element';
import {wc} from '@woocommerce/blocks-registry';
import {getSettings, initStripe} from "../util";
import {LocalPaymentIntentContent} from './local-payment-method';
import {OffsiteNotice, PaymentMethod} from "../../components/checkout";
import {canMakePayment} from "./local-payment-method";
import {AfterpayClearpayMessageElement, Elements} from "@stripe/react-stripe-js";
import {__} from '@wordpress/i18n';
import {wc as wcBlocksCheckout} from '@woocommerce/blocks-checkout';
import {registerPlugin} from '@wordpress/plugins';

const getData = getSettings('stripe_afterpay_data');

const ExperimentalOrderMeta = wcBlocksCheckout.ExperimentalOrderMeta;
const TotalsWrapper = wcBlocksCheckout.TotalsWrapper;

const dispatchAfterpayChange = (options) => {
    document.dispatchEvent(new CustomEvent('stripeAfterpayChange', {
        detail: {
            options
        }
    }))
}

const isAvailable = ({total, currency, country}) => {
    let available = false;
    const billingCountry = country;
    const requiredParams = getData('requiredParams');
    const accountCountry = getData('accountCountry');
    const requiredParamObj = requiredParams.hasOwnProperty(currency) ? requiredParams[currency] : false;
    if (requiredParamObj) {
        let countries = requiredParamObj?.[0];
        if (!Array.isArray(countries)) {
            countries = [countries];
        }
        available = countries.indexOf(accountCountry) > -1
            && (currency !== 'EUR' || !billingCountry || accountCountry === billingCountry)
            && (total > requiredParamObj?.[1] && total < requiredParamObj?.[2]);
    }
    return available;
}

const PaymentMethodLabel = ({title, getData, ...props}) => {
    const {PaymentMethodLabel: Label} = props.components;
    const [options, setOptions] = useState({
        amount: getData('cartTotal'),
        currency: getData('currency'),
        isCartEligible: getData('msgOptions').isEligible
    });
    useEffect(() => {
        const updateOptions = e => setOptions(e.detail.options);
        document.addEventListener('stripeAfterpayChange', updateOptions);
        return () => document.removeEventListener('stripeAfterpayChange', updateOptions);
    }, []);
    return (
        <div className={'sswps-label-container'}>
            <Label text={title}/>
            <div className={'sswps-afterpay-message-container'}>
                <Elements stripe={initStripe} options={getData('elementOptions')}>
                    <div className='sswps-blocks-afterpay__label'>
                        <AfterpayClearpayMessageElement options={{
                            ...getData('msgOptions'),
                            ...options
                        }}/>
                    </div>
                </Elements>
            </div>
        </div>
    )
        ;
}

const AfterpayPaymentMethod = ({content, billing, shippingData, ...props}) => {
    const Content = content;
    const {cartTotal, currency, billingData: {country}} = billing;
    const {needsShipping} = shippingData
    const total = parseInt(cartTotal.value) / 10 ** currency.minorUnit;
    const isCartEligible = isAvailable({total, currency: currency.code, country});
    return (
        <>
            <div className='sswps-blocks-payment-method-content'>
                {isCartEligible && <div className="sswps-blocks-afterpay-offsite__container">
                    <OffsiteNotice paymentText={getData('title')} buttonText={getData('placeOrderButtonLabel')}/>
                </div>}
                <Content {...{...props, billing, shippingData}}/>
            </div>
        </>
    );
}

const OrderItemMessaging = ({cart, extensions, context}) => {
    const {cartTotals, cartNeedsShipping: needsShipping, billingAddress: {country}} = cart;
    const {total_price, currency_code: currency} = cartTotals;
    const totalInCents = parseInt(cartTotals.total_price);
    const total = parseInt(cartTotals.total_price) / (10 ** cartTotals.currency_minor_unit);
    if (!isAvailable({total, currency, country})) {
        return null;
    }
    return (
        <TotalsWrapper>
            <Elements stripe={initStripe} options={getData('elementOptions')}>
                <div className='sswps-blocks-afterpay-totals__item wc-block-components-totals-item'>
                    <AfterpayClearpayMessageElement options={{
                        ...getData('msgOptions'),
                        ...{
                            amount: totalInCents,
                            currency,
                            isCartEligible: isAvailable({total, currency, country})
                        }
                    }}/>
                </div>
            </Elements>
        </TotalsWrapper>
    );
}

if (getData()) {
    wc.registerPaymentMethod({
        name: getData('name'),
        label: <PaymentMethodLabel
            title={getData('title')}
            getData={getData}/>,
        ariaLabel: __('Afterpay', 'simple-secure-stripe'),
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: canMakePayment(getData, ({settings, cartTotals, billingData}) => {
            const {currency_code: currency, currency_minor_unit, total_price} = cartTotals;
            const {country} = billingData;
            const total = parseInt(total_price) / (10 ** currency_minor_unit);
            const available = isAvailable({total, currency, country});
            dispatchAfterpayChange({
                amount: parseInt(cartTotals.total_price),
                currency,
                isCartEligible: available
            });
            if (!available && !settings('hideIneligible')) {
                return true;
            }
            return available;
        }),
        content: <AfterpayPaymentMethod
            content={LocalPaymentIntentContent}
            getData={getData}
            confirmationMethod={'confirmAfterpayClearpayPayment'}/>,
        edit: <PaymentMethod content={LocalPaymentIntentContent} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    });

    const render = () => {
        return (
            <ExperimentalOrderMeta>
                <OrderItemMessaging/>
            </ExperimentalOrderMeta>
        )
    }
    /*registerPlugin('sswps', {
        render: render,
        scope: 'woocommerce-checkout'
    })*/
}