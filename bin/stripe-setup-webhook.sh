#!/bin/bash

# COBOLCOBOL Stripe Webhook Setup Script
# This script creates a webhook endpoint in Stripe for subscription events
# Uses demo keys - safe for public repositories

set -e

STRIPE_SECRET_KEY="sk_test_51SBvaQABz6lYP2dKRYyo5NS0n7N23cPHTYdMemGCj7A6RAJeStQoCa4WhCs4DN8y6gxXnX1bGKy0SEA2NDOK46HA00G8QYrJth"

# Default endpoint URL (can be overridden)
WEBHOOK_URL="${1:-http://localhost:8000/stripe/webhook}"

echo "🔗 Setting up Stripe webhook endpoint..."
echo "📍 Endpoint URL: $WEBHOOK_URL"

# Create webhook endpoint
WEBHOOK=$(curl -s https://api.stripe.com/v1/webhook_endpoints \
  -u "$STRIPE_SECRET_KEY:" \
  -d "url=$WEBHOOK_URL" \
  -d "enabled_events[]=customer.subscription.created" \
  -d "enabled_events[]=customer.subscription.updated" \
  -d "enabled_events[]=customer.subscription.deleted" \
  -d "enabled_events[]=invoice.payment_succeeded" \
  -d "enabled_events[]=invoice.payment_failed")

WEBHOOK_ID=$(echo $WEBHOOK | grep -o '"id":"we_[^"]*' | cut -d'"' -f4)
WEBHOOK_SECRET=$(echo $WEBHOOK | grep -o '"secret":"whsec_[^"]*' | cut -d'"' -f4)

echo "✅ Webhook endpoint created successfully!"
echo ""
echo "📋 Webhook Details:"
echo "Webhook ID: $WEBHOOK_ID"
echo "Webhook Secret: $WEBHOOK_SECRET"
echo "Endpoint URL: $WEBHOOK_URL"
echo ""
echo "📝 Events configured:"
echo "  - customer.subscription.created"
echo "  - customer.subscription.updated"
echo "  - customer.subscription.deleted"
echo "  - invoice.payment_succeeded"
echo "  - invoice.payment_failed"
echo ""
echo "💡 Update your .env file with:"
echo "STRIPE_WEBHOOK_SECRET=$WEBHOOK_SECRET"
echo ""
echo "💡 Visit https://dashboard.stripe.com/test/webhooks to view in Stripe Dashboard"