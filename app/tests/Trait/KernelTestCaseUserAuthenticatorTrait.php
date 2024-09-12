<?php

namespace App\Tests\Trait;

use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

trait KernelTestCaseUserAuthenticatorTrait
{
    private bool $isLoggedIn = false;

    public function loginUser(UserInterface $user, string $firewallContext = 'main', array $tokenAttributes = []): static
    {
        if (! interface_exists(UserInterface::class)) {
            throw new \LogicException(\sprintf('"%s" requires symfony/security-core to be installed. Try running "composer require symfony/security-core".', __METHOD__));
        }

        $token = new TestBrowserToken($user->getRoles(), $user, $firewallContext);
        $token->setAttributes($tokenAttributes);

        $container = self::getContainer();
        $container->get('security.untracked_token_storage')->setToken($token);

        if (! $container->has('session.factory')) {
            return $this;
        }

        $session = $container->get('session.factory')->createSession();
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $this->isLoggedIn = true;

        return $this;
    }

    public function getAuthenticatedUser(): UserInterface
    {
        if (! $this->isLoggedIn) {
            throw new \LogicException(sprintf('User is not logged in. Call "%s::loginUser()" first.', static::class));
        }

        return $this->getSecurity()->getUser();
    }

    public function getSecurity(): Security
    {
        return self::getContainer()->get(Security::class);
    }

    public function getAuthenticatedToken(): TokenInterface
    {
        if (! $this->isLoggedIn) {
            throw new \LogicException(sprintf('User is not logged in. Call "%s::loginUser()" first.', static::class));
        }

        return $this->getSecurity()->getToken();
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }
}
