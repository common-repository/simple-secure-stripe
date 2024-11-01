import {useState} from '@wordpress/element';
import {wc} from '@woocommerce/blocks-registry';
import {Elements} from '@stripe/react-stripe-js';
import {getSettings, initStripe as loadStripe} from '../util';
import {PaymentMethodLabel, PaymentMethod} from '../../components/checkout';
import SavedCardComponent from '../saved-card-component';
import {useCreateLinkToken, useProcessPayment} from './hooks';
import {useProcessCheckoutError} from "../hooks";

const getData = getSettings('stripe_ach_data');

const ACHPaymentContent = (
    {
        eventRegistration,
        components,
        emitResponse,
        onSubmit,
        billing,
        ...props
    }) => {
    const {responseTypes} = emitResponse;
    const {
        onPaymentProcessing,
        onCheckoutAfterProcessingWithError,
        onCheckoutAfterProcessingWithSuccess
    } = eventRegistration;

    useProcessCheckoutError({
        responseTypes,
        subscriber: onCheckoutAfterProcessingWithError
    });


    useProcessPayment({
        onCheckoutAfterProcessingWithSuccess,
        responseTypes,
        paymentMethod: getData('name'),
        billingAddress: billing.billingData
    });
    return (
        <div className={'sswps-ach__container'}>
            <Mandate text={getData('mandateText')}/>
        </div>
    )
}

const ACHComponent = (props) => {
    return (
        <Elements stripe={loadStripe}>
            <ACHPaymentContent {...props}/>
        </Elements>
    )
}

const Mandate = ({text}) => {
    return (
        <p className={'sswps-ach__mandate'}>
            {text}
        </p>
    )
}

wc.registerPaymentMethod({
    name: getData('name'),
    label: <PaymentMethodLabel title={getData('title')}
                               paymentMethod={getData('name')}
                               icons={getData('icons')}/>,
    ariaLabel: 'ACH Payment',
    canMakePayment: ({cartTotals}) => cartTotals.currency_code === 'USD',
    content: <PaymentMethod
        getData={getData}
        content={ACHComponent}/>,
    savedTokenComponent: <SavedCardComponent getData={getData}/>,
    edit: <ACHComponent/>,
    placeOrderButtonLabel: getData('placeOrderButtonLabel'),
    supports: {
        showSavedCards: getData('showSavedCards'),
        showSaveOption: false,
        features: getData('features')
    }
})