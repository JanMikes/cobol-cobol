<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Plan;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create Plans
        $basicPlan = new Plan();
        $basicPlan->setName('Basic Plan')
            ->setDescription('Access to basic content and features')
            ->setPriceMonthly(999) // $9.99
            ->setStripeProductId('prod_basic_demo')
            ->setStripePriceMonthlyId('price_basic_monthly_demo')
            ->setFeatures([
                'Access to basic articles',
                'Email support',
                'Mobile access',
                'Basic analytics'
            ])
            ->setSortOrder(1);

        $premiumPlan = new Plan();
        $premiumPlan->setName('Premium Plan')
            ->setDescription('Access to all premium content and advanced features')
            ->setPriceMonthly(2999) // $29.99
            ->setPriceYearly(29999) // $299.99
            ->setStripeProductId('prod_premium_demo')
            ->setStripePriceMonthlyId('price_premium_monthly_demo')
            ->setStripePriceYearlyId('price_premium_yearly_demo')
            ->setFeatures([
                'Access to all premium articles',
                'Priority email & chat support',
                'Mobile & desktop access',
                'Advanced analytics & insights',
                'Early access to new features',
                'Exclusive webinars & events'
            ])
            ->setSortOrder(2);

        $manager->persist($basicPlan);
        $manager->persist($premiumPlan);

        // Create Users
        $adminUser = new User();
        $adminUser->setEmail('admin@cobolcobol.com')
            ->setFirstName('Admin')
            ->setLastName('User')
            ->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'))
            ->setVerified(true)
            ->setRoles(['ROLE_ADMIN']);

        $testUser = new User();
        $testUser->setEmail('user@cobolcobol.com')
            ->setFirstName('Test')
            ->setLastName('User')
            ->setPassword($this->passwordHasher->hashPassword($testUser, 'user123'))
            ->setVerified(true);

        $subscriberUser = new User();
        $subscriberUser->setEmail('subscriber@cobolcobol.com')
            ->setFirstName('Premium')
            ->setLastName('Subscriber')
            ->setPassword($this->passwordHasher->hashPassword($subscriberUser, 'subscriber123'))
            ->setVerified(true)
            ->setStripeCustomerId('cus_demo_subscriber');

        $manager->persist($adminUser);
        $manager->persist($testUser);
        $manager->persist($subscriberUser);

        // Create Articles
        $articles = [
            [
                'title' => 'Getting Started with COBOLCOBOL',
                'slug' => 'getting-started-with-cobolcobol',
                'excerpt' => 'Learn the basics of using our platform and making the most of your subscription.',
                'content' => $this->getArticleContent('getting-started'),
                'isPremium' => false,
                'author' => $adminUser,
                'tags' => ['getting-started', 'basics', 'tutorial']
            ],
            [
                'title' => 'Advanced Features for Power Users',
                'slug' => 'advanced-features-for-power-users',
                'excerpt' => 'Discover advanced features and tips to supercharge your workflow.',
                'content' => $this->getArticleContent('advanced-features'),
                'isPremium' => true,
                'author' => $adminUser,
                'tags' => ['advanced', 'power-users', 'features']
            ],
            [
                'title' => 'Building Scalable SaaS Applications',
                'slug' => 'building-scalable-saas-applications',
                'excerpt' => 'Learn the fundamentals of building and scaling modern SaaS applications.',
                'content' => $this->getArticleContent('saas-applications'),
                'isPremium' => true,
                'author' => $adminUser,
                'tags' => ['saas', 'scalability', 'architecture']
            ],
            [
                'title' => 'Best Practices for User Authentication',
                'slug' => 'best-practices-for-user-authentication',
                'excerpt' => 'Implement secure and user-friendly authentication in your applications.',
                'content' => $this->getArticleContent('authentication'),
                'isPremium' => false,
                'author' => $adminUser,
                'tags' => ['authentication', 'security', 'best-practices']
            ],
            [
                'title' => 'Stripe Integration Guide',
                'slug' => 'stripe-integration-guide',
                'excerpt' => 'Complete guide to integrating Stripe payments in your application.',
                'content' => $this->getArticleContent('stripe-integration'),
                'isPremium' => true,
                'author' => $adminUser,
                'tags' => ['stripe', 'payments', 'integration']
            ],
            [
                'title' => 'Database Design Patterns',
                'slug' => 'database-design-patterns',
                'excerpt' => 'Common database design patterns and when to use them.',
                'content' => $this->getArticleContent('database-patterns'),
                'isPremium' => false,
                'author' => $adminUser,
                'tags' => ['database', 'design-patterns', 'architecture']
            ]
        ];

        foreach ($articles as $articleData) {
            $article = new Article();
            $article->setTitle($articleData['title'])
                ->setSlug($articleData['slug'])
                ->setExcerpt($articleData['excerpt'])
                ->setContent($articleData['content'])
                ->setPremium($articleData['isPremium'])
                ->setAuthor($articleData['author'])
                ->setTags($articleData['tags'])
                ->setPublished(true)
                ->setPublishedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));

            $manager->persist($article);
        }

        $manager->flush();
    }

    private function getArticleContent(string $type): string
    {
        $contents = [
            'getting-started' => "# Welcome to COBOLCOBOL\n\nCOBOLCOBOL is a modern SaaS platform designed to provide premium content through a subscription model. This guide will help you get started and make the most of your experience.\n\n## Getting Started\n\nOnce you've created your account, you can:\n\n1. **Browse Articles**: Explore our collection of free and premium articles\n2. **Subscribe**: Choose a plan that fits your needs\n3. **Access Premium Content**: Unlock exclusive articles and features\n\n## Features\n\n- **Responsive Design**: Access content on any device\n- **Search & Filter**: Find relevant content quickly\n- **User Dashboard**: Manage your account and subscription\n- **Secure Payments**: Powered by Stripe for secure transactions\n\nWe're excited to have you on board!",

            'advanced-features' => "# Advanced Features for Power Users\n\nAs a premium subscriber, you have access to advanced features that can significantly enhance your experience on COBOLCOBOL.\n\n## Premium Content Library\n\nOur premium content library includes:\n\n- In-depth tutorials and guides\n- Industry insights and analysis\n- Exclusive interviews with experts\n- Advanced technical documentation\n\n## Advanced Analytics\n\nTrack your reading progress and discover personalized recommendations based on your interests.\n\n## Early Access\n\nGet early access to new features and content before they're released to the general public.\n\n## Priority Support\n\nReceive priority customer support with faster response times and dedicated assistance.\n\nThese features are designed to provide maximum value for our most engaged users.",

            'saas-applications' => "# Building Scalable SaaS Applications\n\nBuilding a successful SaaS application requires careful planning, proper architecture, and attention to scalability from day one.\n\n## Key Principles\n\n### 1. Multi-tenancy\nDesign your application to serve multiple customers (tenants) efficiently while keeping their data isolated.\n\n### 2. Scalable Architecture\nUse microservices, load balancers, and cloud infrastructure to handle growing user bases.\n\n### 3. Subscription Management\nImplement robust billing and subscription management systems.\n\n### 4. Security\nPrioritize security at every level of your application.\n\n## Technology Stack\n\nPopular choices include:\n- **Backend**: Node.js, Python, PHP, Ruby\n- **Frontend**: React, Vue.js, Angular\n- **Database**: PostgreSQL, MongoDB\n- **Payments**: Stripe, PayPal\n- **Hosting**: AWS, Google Cloud, Azure\n\n## Best Practices\n\n1. Start with an MVP\n2. Focus on user experience\n3. Implement proper monitoring\n4. Plan for international expansion\n5. Automate deployment and testing\n\nBuilding a SaaS application is challenging but rewarding when done right.",

            'authentication' => "# Best Practices for User Authentication\n\nUser authentication is a critical component of any web application. Implementing it correctly ensures both security and a good user experience.\n\n## Core Principles\n\n### 1. Password Security\n- Use strong password hashing (bcrypt, Argon2)\n- Enforce password complexity requirements\n- Implement password reset functionality\n\n### 2. Session Management\n- Use secure session storage\n- Implement proper session timeout\n- Provide 'remember me' functionality\n\n### 3. Multi-Factor Authentication\nConsider implementing 2FA for enhanced security.\n\n## Implementation Tips\n\n### Registration Flow\n1. Validate user input\n2. Check for existing accounts\n3. Send email verification\n4. Hash and store password securely\n\n### Login Flow\n1. Validate credentials\n2. Check account status\n3. Create secure session\n4. Redirect to appropriate page\n\n### Security Considerations\n- Protect against brute force attacks\n- Implement CSRF protection\n- Use HTTPS everywhere\n- Log security events\n\nProper authentication is the foundation of application security.",

            'stripe-integration' => "# Stripe Integration Guide\n\nStripe is one of the most popular payment processors for SaaS applications. This guide covers the essential steps for integration.\n\n## Setup\n\n### 1. Create Stripe Account\nSign up for a Stripe account and obtain your API keys.\n\n### 2. Install SDK\n```bash\ncomposer require stripe/stripe-php\n```\n\n### 3. Configure Environment\nSet up your secret and publishable keys in your environment configuration.\n\n## Key Concepts\n\n### Customers\nRepresent your users in Stripe's system.\n\n### Products and Prices\nDefine what you're selling and at what price.\n\n### Subscriptions\nRecurring billing for your SaaS service.\n\n### Webhooks\nReceive real-time updates about payment events.\n\n## Implementation Steps\n\n1. **Create Products**: Define your subscription plans\n2. **Create Customers**: When users sign up\n3. **Create Checkout Sessions**: For subscription signup\n4. **Handle Webhooks**: Process payment events\n5. **Customer Portal**: Let users manage their billing\n\n## Best Practices\n\n- Always validate webhooks\n- Handle failed payments gracefully\n- Provide clear billing information\n- Test thoroughly with test cards\n- Implement proper error handling\n\nStripe provides excellent documentation and tools for testing your integration.",

            'database-patterns' => "# Database Design Patterns\n\nUnderstanding common database design patterns is crucial for building efficient and maintainable applications.\n\n## Common Patterns\n\n### 1. Repository Pattern\nEncapsulates data access logic and provides a more object-oriented view of the persistence layer.\n\n### 2. Active Record\nObjects carry both data and behavior, with methods for CRUD operations.\n\n### 3. Data Mapper\nSeparates in-memory objects from the database, allowing for more complex object models.\n\n### 4. Unit of Work\nMaintains a list of objects affected by a business transaction and coordinates changes.\n\n## Design Principles\n\n### Normalization\n- Eliminate data redundancy\n- Organize data efficiently\n- Reduce storage requirements\n\n### Indexing\n- Speed up query performance\n- Consider composite indexes\n- Balance read vs write performance\n\n### Relationships\n- One-to-many\n- Many-to-many\n- One-to-one\n\n## Performance Considerations\n\n1. **Query Optimization**: Write efficient queries\n2. **Caching**: Implement appropriate caching strategies\n3. **Connection Pooling**: Manage database connections efficiently\n4. **Partitioning**: For large datasets\n\n## Best Practices\n\n- Plan your schema carefully\n- Use migrations for schema changes\n- Implement proper backup strategies\n- Monitor query performance\n- Consider read replicas for scaling\n\nGood database design is fundamental to application performance and maintainability."
        ];

        return $contents[$type] ?? "# Sample Article Content\n\nThis is a sample article with some basic content to demonstrate the platform's capabilities.";
    }
}
