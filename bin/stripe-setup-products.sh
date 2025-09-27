#!/bin/bash

# COBOLCOBOL Stripe Product Setup Script
# This script creates the subscription products and prices in Stripe for the SaaS platform
# Uses demo keys - safe for public repositories

set -e

STRIPE_SECRET_KEY="sk_test_51SBvaQABz6lYP2dKRYyo5NS0n7N23cPHTYdMemGCj7A6RAJeStQoCa4WhCs4DN8y6gxXnX1bGKy0SEA2NDOK46HA00G8QYrJth"

echo "🚀 Setting up COBOLCOBOL Stripe products..."

# Check if Basic Plan Product already exists
echo "🔍 Checking for existing Basic Plan product..."
EXISTING_BASIC=$(curl -s https://api.stripe.com/v1/products/search \
  -u "$STRIPE_SECRET_KEY:" \
  -G -d "query=name:'Basic Plan'")

if echo "$EXISTING_BASIC" | grep -q '"id":"prod_'; then
  BASIC_PRODUCT_ID=$(echo $EXISTING_BASIC | grep -o '"id":"prod_[^"]*' | head -1 | cut -d'"' -f4)
  echo "✅ Basic Product already exists: $BASIC_PRODUCT_ID"
else
  echo "📦 Creating Basic Plan product..."
  BASIC_PRODUCT=$(curl -s https://api.stripe.com/v1/products \
    -u "$STRIPE_SECRET_KEY:" \
    -d "name=Basic Plan" \
    -d "description=Access to basic content and features" \
    -d "type=service")

  BASIC_PRODUCT_ID=$(echo $BASIC_PRODUCT | grep -o '"id":"prod_[^"]*' | cut -d'"' -f4)
  echo "✅ Basic Product created: $BASIC_PRODUCT_ID"
fi

# Check if Basic Plan Monthly Price already exists
echo "🔍 Checking for existing Basic Plan monthly price..."
EXISTING_BASIC_PRICE=$(curl -s https://api.stripe.com/v1/prices/search \
  -u "$STRIPE_SECRET_KEY:" \
  -G -d "query=product:'$BASIC_PRODUCT_ID' AND recurring.interval:'month'")

if echo "$EXISTING_BASIC_PRICE" | grep -q '"id":"price_'; then
  BASIC_PRICE_ID=$(echo $EXISTING_BASIC_PRICE | grep -o '"id":"price_[^"]*' | head -1 | cut -d'"' -f4)
  echo "✅ Basic Monthly Price already exists: $BASIC_PRICE_ID"
else
  echo "💰 Creating Basic Plan price (monthly)..."
  BASIC_PRICE=$(curl -s https://api.stripe.com/v1/prices \
    -u "$STRIPE_SECRET_KEY:" \
    -d "product=$BASIC_PRODUCT_ID" \
    -d "unit_amount=999" \
    -d "currency=usd" \
    -d "recurring[interval]=month" \
    -d "nickname=Basic Monthly")

  BASIC_PRICE_ID=$(echo $BASIC_PRICE | grep -o '"id":"price_[^"]*' | cut -d'"' -f4)
  echo "✅ Basic Price created: $BASIC_PRICE_ID"
fi

# Check if Premium Plan Product already exists
echo "🔍 Checking for existing Premium Plan product..."
EXISTING_PREMIUM=$(curl -s https://api.stripe.com/v1/products/search \
  -u "$STRIPE_SECRET_KEY:" \
  -G -d "query=name:'Premium Plan'")

if echo "$EXISTING_PREMIUM" | grep -q '"id":"prod_'; then
  PREMIUM_PRODUCT_ID=$(echo $EXISTING_PREMIUM | grep -o '"id":"prod_[^"]*' | head -1 | cut -d'"' -f4)
  echo "✅ Premium Product already exists: $PREMIUM_PRODUCT_ID"
else
  echo "📦 Creating Premium Plan product..."
  PREMIUM_PRODUCT=$(curl -s https://api.stripe.com/v1/products \
    -u "$STRIPE_SECRET_KEY:" \
    -d "name=Premium Plan" \
    -d "description=Access to all premium content and advanced features" \
    -d "type=service")

  PREMIUM_PRODUCT_ID=$(echo $PREMIUM_PRODUCT | grep -o '"id":"prod_[^"]*' | cut -d'"' -f4)
  echo "✅ Premium Product created: $PREMIUM_PRODUCT_ID"
fi

# Check if Premium Plan Monthly Price already exists
echo "🔍 Checking for existing Premium Plan monthly price..."
EXISTING_PREMIUM_PRICE=$(curl -s https://api.stripe.com/v1/prices/search \
  -u "$STRIPE_SECRET_KEY:" \
  -G -d "query=product:'$PREMIUM_PRODUCT_ID' AND recurring.interval:'month'")

if echo "$EXISTING_PREMIUM_PRICE" | grep -q '"id":"price_'; then
  PREMIUM_PRICE_ID=$(echo $EXISTING_PREMIUM_PRICE | grep -o '"id":"price_[^"]*' | head -1 | cut -d'"' -f4)
  echo "✅ Premium Monthly Price already exists: $PREMIUM_PRICE_ID"
else
  echo "💰 Creating Premium Plan price (monthly)..."
  PREMIUM_PRICE=$(curl -s https://api.stripe.com/v1/prices \
    -u "$STRIPE_SECRET_KEY:" \
    -d "product=$PREMIUM_PRODUCT_ID" \
    -d "unit_amount=2999" \
    -d "currency=usd" \
    -d "recurring[interval]=month" \
    -d "nickname=Premium Monthly")

  PREMIUM_PRICE_ID=$(echo $PREMIUM_PRICE | grep -o '"id":"price_[^"]*' | cut -d'"' -f4)
  echo "✅ Premium Price created: $PREMIUM_PRICE_ID"
fi

# Check if Premium Plan Yearly Price already exists
echo "🔍 Checking for existing Premium Plan yearly price..."
EXISTING_PREMIUM_YEARLY_PRICE=$(curl -s https://api.stripe.com/v1/prices/search \
  -u "$STRIPE_SECRET_KEY:" \
  -G -d "query=product:'$PREMIUM_PRODUCT_ID' AND recurring.interval:'year'")

if echo "$EXISTING_PREMIUM_YEARLY_PRICE" | grep -q '"id":"price_'; then
  PREMIUM_YEARLY_PRICE_ID=$(echo $EXISTING_PREMIUM_YEARLY_PRICE | grep -o '"id":"price_[^"]*' | head -1 | cut -d'"' -f4)
  echo "✅ Premium Yearly Price already exists: $PREMIUM_YEARLY_PRICE_ID"
else
  echo "💰 Creating Premium Plan price (yearly)..."
  PREMIUM_YEARLY_PRICE=$(curl -s https://api.stripe.com/v1/prices \
    -u "$STRIPE_SECRET_KEY:" \
    -d "product=$PREMIUM_PRODUCT_ID" \
    -d "unit_amount=29999" \
    -d "currency=usd" \
    -d "recurring[interval]=year" \
    -d "nickname=Premium Yearly")

  PREMIUM_YEARLY_PRICE_ID=$(echo $PREMIUM_YEARLY_PRICE | grep -o '"id":"price_[^"]*' | cut -d'"' -f4)
  echo "✅ Premium Yearly Price created: $PREMIUM_YEARLY_PRICE_ID"
fi

echo ""
echo "🎉 All Stripe products and prices created successfully!"
echo ""
echo "📋 Summary:"
echo "Basic Plan Product ID: $BASIC_PRODUCT_ID"
echo "Basic Monthly Price ID: $BASIC_PRICE_ID ($9.99/month)"
echo ""
echo "Premium Plan Product ID: $PREMIUM_PRODUCT_ID"
echo "Premium Monthly Price ID: $PREMIUM_PRICE_ID ($29.99/month)"
echo "Premium Yearly Price ID: $PREMIUM_YEARLY_PRICE_ID ($299.99/year)"
echo ""
echo "💡 Add these IDs to your application configuration!"
echo "💡 Visit https://dashboard.stripe.com/test/products to view in Stripe Dashboard"