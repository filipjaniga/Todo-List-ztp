<?php
/**
 * User service.
 */

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * Constructor.
     *
     * @param UserRepository $userRepository User repository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Save entity.
     *
     * @param UserInterface $user User
     */
    public function save(UserInterface $user): void
    {
        $this->userRepository->save($user);
    }
}
