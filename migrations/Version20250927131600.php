<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250927131600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user, plan, subscription, and article tables for COBOLCOBOL SaaS';
    }

    public function up(Schema $schema): void
    {
        // Create user table
        $this->addSql('CREATE TABLE "user" (
            id SERIAL NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            is_verified BOOLEAN NOT NULL DEFAULT FALSE,
            stripe_customer_id VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Create plan table
        $this->addSql('CREATE TABLE plan (
            id SERIAL NOT NULL,
            name VARCHAR(255) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            price_monthly INT NOT NULL,
            price_yearly INT DEFAULT NULL,
            stripe_product_id VARCHAR(255) NOT NULL,
            stripe_price_monthly_id VARCHAR(255) NOT NULL,
            stripe_price_yearly_id VARCHAR(255) DEFAULT NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            sort_order INT NOT NULL DEFAULT 0,
            features JSON NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('COMMENT ON COLUMN plan.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN plan.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Create subscription table
        $this->addSql('CREATE TABLE subscription (
            id SERIAL NOT NULL,
            user_id INT NOT NULL,
            plan_id INT NOT NULL,
            stripe_subscription_id VARCHAR(255) NOT NULL,
            status VARCHAR(255) NOT NULL,
            current_period_start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            current_period_end TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            trial_start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            trial_end TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            canceled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            cancel_at_period_end TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX IDX_A3C664D3A76ED395 ON subscription (user_id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3E899029B ON subscription (plan_id)');
        $this->addSql('COMMENT ON COLUMN subscription.current_period_start IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.current_period_end IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.trial_start IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.trial_end IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.canceled_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.cancel_at_period_end IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN subscription.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Create article table
        $this->addSql('CREATE TABLE article (
            id SERIAL NOT NULL,
            author_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            excerpt TEXT DEFAULT NULL,
            content TEXT NOT NULL,
            is_premium BOOLEAN NOT NULL DEFAULT FALSE,
            is_published BOOLEAN NOT NULL DEFAULT FALSE,
            published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            tags JSON NOT NULL,
            meta_description VARCHAR(255) DEFAULT NULL,
            featured_image VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23A0E66989D9B62 ON article (slug)');
        $this->addSql('COMMENT ON COLUMN article.published_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN article.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN article.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3E899029B FOREIGN KEY (plan_id) REFERENCES plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints first
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3A76ED395');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3E899029B');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66F675F31B');

        // Drop tables
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE "user"');
    }
}