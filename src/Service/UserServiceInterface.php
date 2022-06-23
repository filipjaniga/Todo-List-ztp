<?php
/**
 * User service interface.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(UserInterface $user): void;
}
