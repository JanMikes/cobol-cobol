<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\SubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    #[Route('/articles', name: 'app_articles')]
    public function index(): Response
    {
        $user = $this->getUser();
        $hasActiveSubscription = $user ? $this->subscriptionService->userHasActiveSubscription($user) : false;

        if ($hasActiveSubscription) {
            $articles = $this->articleRepository->findPublishedArticles();
        } else {
            $articles = $this->articleRepository->findFreeArticles();
        }

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'has_subscription' => $hasActiveSubscription,
        ]);
    }

    #[Route('/articles/{slug}', name: 'app_article_show')]
    public function show(string $slug): Response
    {
        $article = $this->articleRepository->findBySlug($slug);

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        if ($article->isPremium()) {
            $this->denyAccessUnlessGranted('ROLE_SUBSCRIBER');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/premium', name: 'app_premium')]
    public function premium(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUBSCRIBER');

        $articles = $this->articleRepository->findPremiumArticles();

        return $this->render('article/premium.html.twig', [
            'articles' => $articles,
        ]);
    }
}