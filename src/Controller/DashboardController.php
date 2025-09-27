<?php

namespace App\Controller;

use App\Service\StripeService;
use App\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly StripeService $stripeService
    ) {
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $activeSubscription = $this->subscriptionService->getUserActiveSubscription($user);

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'active_subscription' => $activeSubscription,
        ]);
    }

    #[Route('/account/manage-subscription', name: 'app_manage_subscription')]
    public function manageSubscription(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$user->getStripeCustomerId()) {
            $this->addFlash('error', 'No subscription found to manage.');
            return $this->redirectToRoute('app_dashboard');
        }

        try {
            $session = $this->stripeService->createCustomerPortalSession($user);
            return $this->redirect($session->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Unable to access customer portal. Please try again.');
            return $this->redirectToRoute('app_dashboard');
        }
    }
}