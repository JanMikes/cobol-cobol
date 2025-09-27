#!/bin/bash

# COBOLCOBOL Stripe Products List Script
# This script lists all products and prices in your Stripe account
# Uses demo keys - safe for public repositories

set -e

STRIPE_SECRET_KEY="sk_test_51SBvaQABz6lYP2dKRYyo5NS0n7N23cPHTYdMemGCj7A6RAJeStQoCa4WhCs4DN8y6gxXnX1bGKy0SEA2NDOK46HA00G8QYrJth"

echo "📦 Listing all Stripe products and prices..."
echo ""

# List all products
PRODUCTS=$(curl -s https://api.stripe.com/v1/products?limit=100 \
  -u "$STRIPE_SECRET_KEY:")

echo "🏷️  Products:"
echo "$PRODUCTS" | grep -o '"id":"prod_[^"]*","object":"product","active":[^,]*,"created":[^,]*,"default_price":[^,]*,"description":"[^"]*","images":\[\],"livemode":[^,]*,"metadata":{},"name":"[^"]*"' | while read line; do
  PRODUCT_ID=$(echo $line | grep -o 'prod_[^"]*')
  PRODUCT_NAME=$(echo $line | grep -o 'name":"[^"]*' | cut -d'"' -f3)
  PRODUCT_DESC=$(echo $line | grep -o 'description":"[^"]*' | cut -d'"' -f3)
  ACTIVE=$(echo $line | grep -o 'active":[^,]*' | cut -d':' -f2)

  echo "  📦 $PRODUCT_NAME ($PRODUCT_ID)"
  echo "     📝 $PRODUCT_DESC"
  echo "     🔄 Active: $ACTIVE"
  echo ""
done

echo "💰 Prices:"
PRICES=$(curl -s https://api.stripe.com/v1/prices?limit=100 \
  -u "$STRIPE_SECRET_KEY:")

echo "$PRICES" | grep -o '"id":"price_[^"]*","object":"price","active":[^,]*,"billing_scheme":"[^"]*","created":[^,]*,"currency":"[^"]*","custom_unit_amount":null,"livemode":[^,]*,"lookup_key":null,"metadata":{},"nickname":"[^"]*","product":"[^"]*","recurring":{"aggregate_usage":null,"interval":"[^"]*","interval_count":[^,]*,"trial_period_days":null,"usage_type":"licensed"},"tax_behavior":"unspecified","tiers_mode":null,"transform_quantity":null,"type":"recurring","unit_amount":[^,]*' | while read line; do
  PRICE_ID=$(echo $line | grep -o 'price_[^"]*')
  NICKNAME=$(echo $line | grep -o 'nickname":"[^"]*' | cut -d'"' -f3)
  PRODUCT_ID=$(echo $line | grep -o 'product":"[^"]*' | cut -d'"' -f3)
  CURRENCY=$(echo $line | grep -o 'currency":"[^"]*' | cut -d'"' -f3)
  AMOUNT=$(echo $line | grep -o 'unit_amount":[^,]*' | cut -d':' -f2)
  INTERVAL=$(echo $line | grep -o 'interval":"[^"]*' | cut -d'"' -f3)
  ACTIVE=$(echo $line | grep -o 'active":[^,]*' | cut -d':' -f2)

  # Convert amount from cents to dollars
  AMOUNT_DOLLARS=$(echo "scale=2; $AMOUNT / 100" | bc -l)

  echo "  💰 $NICKNAME ($PRICE_ID)"
  echo "     📦 Product: $PRODUCT_ID"
  echo "     💵 Price: \$$AMOUNT_DOLLARS $CURRENCY per $INTERVAL"
  echo "     🔄 Active: $ACTIVE"
  echo ""
done

echo "💡 Visit https://dashboard.stripe.com/test/products to view in Stripe Dashboard"