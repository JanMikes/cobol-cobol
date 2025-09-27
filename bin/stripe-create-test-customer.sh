#!/bin/bash

# COBOLCOBOL Stripe Test Customer Creation Script
# This script creates a test customer with a test subscription
# Uses demo keys - safe for public repositories

set -e

STRIPE_SECRET_KEY="sk_test_51SBvaQABz6lYP2dKRYyo5NS0n7N23cPHTYdMemGCj7A6RAJeStQoCa4WhCs4DN8y6gxXnX1bGKy0SEA2NDOK46HA00G8QYrJth"

# Customer details (can be overridden with command line arguments)
CUSTOMER_EMAIL="${1:-test@cobolcobol.com}"
CUSTOMER_NAME="${2:-Test Customer}"
PRICE_ID="${3:-}"

echo "👤 Creating test customer in Stripe..."
echo "📧 Email: $CUSTOMER_EMAIL"
echo "📝 Name: $CUSTOMER_NAME"

# Create customer
CUSTOMER=$(curl -s https://api.stripe.com/v1/customers \
  -u "$STRIPE_SECRET_KEY:" \
  -d "name=$CUSTOMER_NAME" \
  -d "email=$CUSTOMER_EMAIL" \
  -d "description=Test customer for COBOLCOBOL SaaS demo")

CUSTOMER_ID=$(echo $CUSTOMER | grep -o '"id":"cus_[^"]*' | cut -d'"' -f4)
echo "✅ Customer created: $CUSTOMER_ID"

# If price ID provided, create a subscription
if [ ! -z "$PRICE_ID" ]; then
  echo ""
  echo "🔄 Creating test subscription with price: $PRICE_ID"

  SUBSCRIPTION=$(curl -s https://api.stripe.com/v1/subscriptions \
    -u "$STRIPE_SECRET_KEY:" \
    -d "customer=$CUSTOMER_ID" \
    -d "items[0][price]=$PRICE_ID" \
    -d "trial_period_days=7" \
    -d "payment_behavior=default_incomplete" \
    -d "payment_settings[save_default_payment_method]=on_subscription" \
    -d "expand[]=latest_invoice.payment_intent")

  SUBSCRIPTION_ID=$(echo $SUBSCRIPTION | grep -o '"id":"sub_[^"]*' | cut -d'"' -f4)
  SUBSCRIPTION_STATUS=$(echo $SUBSCRIPTION | grep -o '"status":"[^"]*' | cut -d'"' -f4)

  echo "✅ Subscription created: $SUBSCRIPTION_ID"
  echo "📊 Status: $SUBSCRIPTION_STATUS"
fi

echo ""
echo "📋 Summary:"
echo "Customer ID: $CUSTOMER_ID"
echo "Customer Email: $CUSTOMER_EMAIL"
echo "Customer Name: $CUSTOMER_NAME"

if [ ! -z "$PRICE_ID" ]; then
  echo "Subscription ID: $SUBSCRIPTION_ID"
  echo "Subscription Status: $SUBSCRIPTION_STATUS"
fi

echo ""
echo "💡 Visit https://dashboard.stripe.com/test/customers to view in Stripe Dashboard"
echo ""
echo "Usage examples:"
echo "  Create customer only:"
echo "    ./stripe-create-test-customer.sh user@example.com \"John Doe\""
echo ""
echo "  Create customer with subscription:"
echo "    ./stripe-create-test-customer.sh user@example.com \"John Doe\" price_1234567890"