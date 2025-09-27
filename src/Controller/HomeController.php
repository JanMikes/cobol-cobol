<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository, PlanRepository $planRepository): Response
    {
        $recentArticles = $articleRepository->findRecentArticles(3);
        $plans = $planRepository->findActivePlans();

        return $this->render('home/index.html.twig', [
            'recent_articles' => $recentArticles,
            'plans' => $plans,
        ]);
    }
}