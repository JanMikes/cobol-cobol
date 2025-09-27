<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PricingController extends AbstractController
{
    public function __construct(
        private readonly PlanRepository $planRepository,
        private readonly StripeService $stripeService
    ) {
    }

    #[Route('/pricing', name: 'app_pricing')]
    public function index(): Response
    {
        $plans = $this->planRepository->findForPricingPage();

        return $this->render('pricing/index.html.twig', [
            'plans' => $plans,
            'stripe_public_key' => $this->stripeService->getPublicKey(),
        ]);
    }

    #[Route('/subscribe/{id}', name: 'app_subscribe', requirements: ['id' => '\d+'])]
    public function subscribe(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $plan = $this->planRepository->find($id);
        if (!$plan || !$plan->isActive()) {
            throw $this->createNotFoundException('Plan not found');
        }

        $user = $this->getUser();
        assert($user instanceof \App\Entity\User);
        $isYearly = $request->query->getBoolean('yearly', false);

        try {
            $session = $this->stripeService->createCheckoutSession($user, $plan, $isYearly);
            return $this->redirect($session->url ?? '');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Unable to create checkout session. Please try again.');
            return $this->redirectToRoute('app_pricing');
        }
    }

    #[Route('/subscription/success', name: 'app_subscription_success')]
    public function subscriptionSuccess(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $sessionId = $request->query->get('session_id');
        if (!$sessionId) {
            return $this->redirectToRoute('app_dashboard');
        }

        try {
            $session = $this->stripeService->retrieveCheckoutSession($sessionId);

            return $this->render('subscription/success.html.twig', [
                'session' => $session,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Unable to retrieve session information.');
            return $this->redirectToRoute('app_dashboard');
        }
    }
}