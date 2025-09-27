<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginSuccessListener
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $user = $event->getUser();

        if ($user) {
            $session->getFlashBag()->add('success', 'Úspešne ste sa prihlásili!');
        }
    }
}