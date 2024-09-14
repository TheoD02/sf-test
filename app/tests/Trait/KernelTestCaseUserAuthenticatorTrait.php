<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\SessionFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

trait KernelTestCaseUserAuthenticatorTrait
{
    private bool $isLoggedIn = false;

    /**
     * @param array<mixed> $tokenAttributes
     */
    public function loginUser(
        UserInterface $user,
        string $firewallContext = 'main',
        array $tokenAttributes = [],
    ): static {
        if (! interface_exists(UserInterface::class)) {
            throw new \LogicException(\sprintf(
                '"%s" requires symfony/security-core to be installed. Try running "composer require symfony/security-core".',
                __METHOD__,
            ));
        }

        $token = new JWTPostAuthenticationToken($user, $firewallContext, $user->getRoles(), 'api_token');
        $token->setAttributes($tokenAttributes);

        $container = self::getContainer();

        if (! $container->has('security.token_storage')) {
            throw new \LogicException(\sprintf(
                '"%s" requires symfony/security-bundle to be installed. Try running "composer require symfony/security-bundle".',
                __METHOD__,
            ));
        }

        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $container->get('security.untracked_token_storage');
        $tokenStorage->setToken($token);

        if (! $container->has('session.factory')) {
            return $this;
        }

        /** @var SessionFactory $sessionFactory */
        $sessionFactory = $container->get('session.factory');
        $session = $sessionFactory->createSession();
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $this->isLoggedIn = true;

        return $this;
    }

    public function getAuthenticatedUser(): UserInterface
    {
        if (! $this->isLoggedIn) {
            throw new \LogicException(\sprintf('User is not logged in. Call "%s::loginUser()" first.', static::class));
        }

        Assert::isInstanceOf($this->getSecurity()->getUser(), UserInterface::class);

        return $this->getSecurity()->getUser();
    }

    public function getSecurity(): Security
    {
        if (! self::getContainer()->has(Security::class)) {
            throw new \LogicException(\sprintf(
                '"%s" requires symfony/security-bundle to be installed. Try running "composer require symfony/security-bundle".',
                __METHOD__,
            ));
        }

        /** @var Security */
        return self::getContainer()->get(Security::class);
    }

    public function getAuthenticatedToken(): TokenInterface
    {
        if (! $this->isLoggedIn) {
            throw new \LogicException(\sprintf('User is not logged in. Call "%s::loginUser()" first.', static::class));
        }

        Assert::isInstanceOf($this->getSecurity()->getToken(), TokenInterface::class);

        return $this->getSecurity()->getToken();
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }
}
