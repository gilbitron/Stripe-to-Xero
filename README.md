# Stripe to Xero

Stripe to Xero is a PHP CLI that takes a [Stripe](http://stripe.com) payments, transfers or balance export file and
generates a new [Xero](http://www.xero.com) compatible bank statement for importing.

## Install

Install the dependencies using Composer:

    composer install

## Usage

First you need to export the required files from Stripe:

1. Login to the Stripe dashboard
2. Navigate to Payments > View All Payments
3. Filter as required (don't worry about status, the script will handle that)
4. Export and download the CSV file

The process is pretty much the same for transfers and balance history.

Then you can generate the Xero compatible bank statement by running

    php stripe-to-xero payments path/to/payments.csv

for payments and

    php stripe-to-xero transfers path/to/transfers.csv

for transfers and

	php stripe-to-xero balance path/to/balance_history.csv

for balance transfers. This will generate new `stripe-payments-to-xero.csv`, `stripe-transfers-to-xero.csv`
and `stripe-balance-to-xero.csv` files that you can then use as bank statement imports in Xero.
