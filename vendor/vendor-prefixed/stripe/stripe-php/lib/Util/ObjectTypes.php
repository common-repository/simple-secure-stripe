<?php

// File generated from our OpenAPI spec

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\Util;

class ObjectTypes
{
    /**
     * @var array Mapping from object types to resource classes
     *
     * @license MIT
     * Modified by sswp-bot on 26-December-2023 using Strauss.
     * @see https://github.com/BrianHenryIE/strauss
     */
    const mapping = [
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Account::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Account::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\AccountLink::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\AccountLink::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\AccountSession::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\AccountSession::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\ApplePayDomain::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\ApplePayDomain::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\ApplicationFee::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\ApplicationFee::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\ApplicationFeeRefund::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\ApplicationFeeRefund::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Apps\Secret::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Apps\Secret::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Balance::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Balance::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\BalanceTransaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\BalanceTransaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\BankAccount::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\BankAccount::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\BillingPortal\Configuration::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\BillingPortal\Configuration::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\BillingPortal\Session::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\BillingPortal\Session::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Capability::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Capability::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Capital\FinancingOffer::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Capital\FinancingOffer::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Capital\FinancingSummary::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Capital\FinancingSummary::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Capital\FinancingTransaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Capital\FinancingTransaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Card::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Card::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\CashBalance::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\CashBalance::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Charge::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Charge::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Checkout\Session::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Checkout\Session::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Collection::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Collection::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\CountrySpec::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\CountrySpec::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Coupon::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Coupon::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\CreditNote::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\CreditNote::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\CreditNoteLineItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\CreditNoteLineItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Customer::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Customer::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\CustomerBalanceTransaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\CustomerBalanceTransaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\CustomerCashBalanceTransaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\CustomerCashBalanceTransaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\CustomerSession::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\CustomerSession::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Discount::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Discount::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Dispute::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Dispute::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\EphemeralKey::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\EphemeralKey::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Event::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Event::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\ExchangeRate::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\ExchangeRate::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\File::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\File::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\File::OBJECT_NAME_ALT => \SimpleSecureWP\SimpleSecureStripe\Stripe\File::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FileLink::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FileLink::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\Account::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\Account::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\AccountOwner::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\AccountOwner::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\AccountOwnership::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\AccountOwnership::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\InferredBalance::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\InferredBalance::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\Session::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\Session::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\Transaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FinancialConnections\Transaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\FundingInstructions::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\FundingInstructions::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Card::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Card::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Transaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\GiftCards\Transaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Identity\VerificationReport::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Identity\VerificationReport::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Identity\VerificationSession::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Identity\VerificationSession::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Invoice::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Invoice::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\InvoiceItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\InvoiceItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\InvoiceLineItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\InvoiceLineItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Authorization::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Authorization::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Card::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Card::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\CardBundle::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\CardBundle::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\CardDesign::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\CardDesign::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\CardDetails::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\CardDetails::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Cardholder::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Cardholder::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Dispute::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Dispute::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Transaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Issuing\Transaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\LineItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\LineItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\LoginLink::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\LoginLink::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Mandate::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Mandate::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Order::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Order::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentLink::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentLink::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethod::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethod::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethodConfiguration::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentMethodConfiguration::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Payout::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Payout::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Person::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Person::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Plan::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Plan::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Price::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Price::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Product::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Product::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\PromotionCode::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\PromotionCode::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Quote::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Quote::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\QuoteLine::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\QuoteLine::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\QuotePhase::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\QuotePhase::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\EarlyFraudWarning::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\EarlyFraudWarning::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\ValueList::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\ValueList::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\ValueListItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Radar\ValueListItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Refund::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Refund::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Reporting\ReportRun::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Reporting\ReportRun::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Reporting\ReportType::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Reporting\ReportType::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Review::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Review::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\SearchResult::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\SearchResult::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\SetupAttempt::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\SetupAttempt::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\SetupIntent::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\SetupIntent::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\ShippingRate::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\ShippingRate::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Sigma\ScheduledQueryRun::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Sigma\ScheduledQueryRun::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Source::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Source::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\SourceTransaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\SourceTransaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Subscription::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Subscription::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\SubscriptionItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\SubscriptionItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\SubscriptionSchedule::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\SubscriptionSchedule::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Calculation::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Calculation::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\CalculationLineItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\CalculationLineItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Form::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Form::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Registration::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Registration::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Settings::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Settings::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Transaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\Transaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\TransactionLineItem::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Tax\TransactionLineItem::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\TaxCode::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\TaxCode::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\TaxId::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\TaxId::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\TaxRate::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\TaxRate::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\Configuration::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\Configuration::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\ConnectionToken::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\ConnectionToken::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\Location::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\Location::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\Reader::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Terminal\Reader::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\TestHelpers\TestClock::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\TestHelpers\TestClock::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Token::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Token::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Topup::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Topup::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Transfer::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Transfer::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\TransferReversal::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\TransferReversal::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\CreditReversal::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\CreditReversal::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\DebitReversal::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\DebitReversal::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\FinancialAccount::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\FinancialAccount::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\FinancialAccountFeatures::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\FinancialAccountFeatures::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\InboundTransfer::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\InboundTransfer::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\OutboundPayment::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\OutboundPayment::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\OutboundTransfer::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\OutboundTransfer::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\ReceivedCredit::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\ReceivedCredit::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\ReceivedDebit::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\ReceivedDebit::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\Transaction::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\Transaction::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\TransactionEntry::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\Treasury\TransactionEntry::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\UsageRecord::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\UsageRecord::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\UsageRecordSummary::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\UsageRecordSummary::class,
        \SimpleSecureWP\SimpleSecureStripe\Stripe\WebhookEndpoint::OBJECT_NAME => \SimpleSecureWP\SimpleSecureStripe\Stripe\WebhookEndpoint::class,
    ];
}
