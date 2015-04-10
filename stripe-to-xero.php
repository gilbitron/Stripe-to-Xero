<?php

require_once('vendor/autoload.php');

$period = '-30 days';
$limit = 100;
\Stripe\Stripe::setApiKey('SET THIS');

$endline = php_sapi_name() == 'cli' ? "\n" : '<br>';

// Transfers
$transfers = \Stripe\Transfer::all([
	'created' => [
		'gte' => strtotime($period)
	],
	'limit' => $limit
]);

$transfersOutput = '';
foreach ($transfers['data'] as $transfer) {
	$amount = '-'. number_format($transfer['amount'] / 100, 2, '.', '');
	$fees = '-'. number_format(($transfer['summary']['charge_fees'] + $transfer['summary']['refund_fees']) / 100, 2, '.', '');
	$fee_adjustment = number_format(($transfer['summary']['adjustment_gross'] - $transfer['summary']['adjustment_fees']) / 100, 2, '.', '');
	$date = date('Y-m-d', $transfer['created']);
	$description = 'Transfer from Stripe';
	$payee = 'Stripe';
	$reference = $transfer['id'];
	$type = 'Transfer';

	if ($transfer['status'] == 'paid') {
		$transfersOutput .= implode(',', [$date, $description, $amount, $reference, $type, $payee]) . $endline;
		$transfersOutput .= implode(',', [$date, 'Stripe fees', $fees, $reference, 'Debit', $payee]) . $endline;
		if ($fee_adjustment > 0.00) {
			$transfersOutput .= implode(',', [$date, 'Stripe fee adjustment', $fee_adjustment, $reference, 'Credit', $payee]) . $endline;
		}
	}
}

// Charges
$charges = \Stripe\Charge::all([
	'created' => [
		'gte' => strtotime($period)
	],
	'expand' => ['data.customer', 'data.balance_transaction'],
	'limit' => $limit
]);

$chargesOutput = '';
foreach ($charges['data'] as $charge) {
	$amount = number_format($charge['balance_transaction']['amount'] / 100, 2, '.', '');
	$date = date('Y-m-d', $charge['created']);
	$description = $charge['description'];
	$payee = 'Stripe Customer';
	$reference = $charge['id'];
	$type = 'Credit';

	if (isset($charge['customer']['email']) && $charge['customer']['email']) {
		$payee = $charge['customer']['email'];
	}

	if ($charge['paid']) {
		$chargesOutput .= implode(',', [$date, $description, $amount, $reference, $type, $payee]) . $endline;
	}

	if ($charge['amount_refunded'] > 0) {
		$amount = '-'. number_format($charge['amount_refunded'] / 100, 2, '.', ''); // Wrong currency (balance_transaction)?
		$reference = $charge['balance_transaction']['id'];
		$type = 'Debit';

		$chargesOutput .= implode(',', [$date, $description, $amount, $reference, $type, $payee]) . $endline;
	}
}

$output = implode(',', ['Transaction Date','Description', 'Transaction Amount', 'Reference', 'Transaction Type', 'Payee']) . $endline;
$output .= $transfersOutput . $chargesOutput;

if (php_sapi_name() == 'cli') {
	file_put_contents('stripe-to-xero.csv', $output);
} else {
	echo $output;
}