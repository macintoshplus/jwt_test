<?php
namespace JWT\Server;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProvider implements UserProviderInterface
{
    private $liste;

    public function __construct(array $liste)
    {
        $this->liste = $liste;
    }

    public function loadUserByUsername($username)
    {
        if (!isset($this->liste[$username])) {
            throw new UsernameNotFoundException($username);
        }

        return new User($username, $this->liste[$username]['pem'], $this->liste[$username]['roles'], true, true, true, true);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(get_class($user));
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === User::class;
    }
}
