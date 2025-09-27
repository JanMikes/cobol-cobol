# Initial instructions

```
Create SaaS application - page "COBOLCOBOL" that will be selling subscriptions to users.
There will be content (articles for example - static content for now as dummy) that will be available only for subscribed users.
We are creating demo (sandbox) application that should demonstrate the basic concepts of sign in, sign up, subscribe, manage subscription with stripe and access private content available only to subscribed users.

Create compose.yml (for docker compose) and run ALWAYS everything in docker.
Feel free to use any starting point (sandbox skeleton) from symfony for example.

Technologies:
- Symfony
- Docker
- Stripe
- Postgres as database
- Use PHPStan for static analysis

Pages to include:
- Landing page
- Pricing (when im subscribed already, redirect to manage subscriptions)
- Private page
- Sign in/up

If mailer is needed use `axllent/mailpit` in docker compose

Try to follow best practices.
Write clean code.
```