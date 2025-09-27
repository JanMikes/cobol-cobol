<?php

namespace App\Controller;

use App\Service\StripeService;
use App\Service\SubscriptionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly SubscriptionService $subscriptionService,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(STRIPE_WEBHOOK_SECRET)%')] private readonly string $webhookSecret
    ) {
    }

    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');

        if (!$signature) {
            $this->logger->error('Missing Stripe signature header');
            return new Response('Missing signature', 400);
        }

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature, $this->webhookSecret);
        } catch (\RuntimeException $e) {
            $this->logger->error('Webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return new Response('Invalid signature', 400);
        }

        $this->logger->info('Webhook event received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        try {
            $this->handleWebhookEvent($event);
        } catch (\Exception $e) {
            $this->logger->error('Webhook event handling failed', [
                'type' => $event->type,
                'id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            return new Response('Event handling failed', 500);
        }

        return new Response('OK');
    }

    private function handleWebhookEvent(\Stripe\Event $event): void
    {
        switch ($event->type) {
            case 'customer.subscription.created':
                assert($event->data->object instanceof \Stripe\Subscription);
                $this->handleSubscriptionCreated($event->data->object);
                break;

            case 'customer.subscription.updated':
                assert($event->data->object instanceof \Stripe\Subscription);
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                assert($event->data->object instanceof \Stripe\Subscription);
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                assert($event->data->object instanceof \Stripe\Invoice);
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                assert($event->data->object instanceof \Stripe\Invoice);
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            default:
                $this->logger->info('Unhandled webhook event type', [
                    'type' => $event->type,
                ]);
        }
    }

    private function handleSubscriptionCreated(\Stripe\Subscription $subscription): void
    {
        $this->logger->info('Processing subscription.created', [
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer,
        ]);

        $this->subscriptionService->createSubscriptionFromStripeData(
            $subscription->customer,
            $subscription
        );
    }

    private function handleSubscriptionUpdated(\Stripe\Subscription $subscription): void
    {
        $this->logger->info('Processing subscription.updated', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);

        $this->subscriptionService->updateSubscriptionFromStripe($subscription);
    }

    private function handleSubscriptionDeleted(\Stripe\Subscription $subscription): void
    {
        $this->logger->info('Processing subscription.deleted', [
            'subscription_id' => $subscription->id,
        ]);

        $this->subscriptionService->updateSubscriptionFromStripe($subscription);
    }

    private function handleInvoicePaymentSucceeded(\Stripe\Invoice $invoice): void
    {
        $subscriptionId = $invoice->subscription;

        $this->logger->info('Processing invoice.payment_succeeded', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $subscriptionId,
            'amount_paid' => $invoice->amount_paid,
        ]);

        if ($subscriptionId && is_string($subscriptionId)) {
            $subscription = $this->stripeService->retrieveSubscription($subscriptionId);
            $this->subscriptionService->updateSubscriptionFromStripe($subscription);
        }
    }

    private function handleInvoicePaymentFailed(\Stripe\Invoice $invoice): void
    {
        $subscriptionId = $invoice->subscription;

        $this->logger->warning('Processing invoice.payment_failed', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $subscriptionId,
            'amount_due' => $invoice->amount_due,
        ]);

        if ($subscriptionId && is_string($subscriptionId)) {
            $subscription = $this->stripeService->retrieveSubscription($subscriptionId);
            $this->subscriptionService->updateSubscriptionFromStripe($subscription);
        }
    }
}