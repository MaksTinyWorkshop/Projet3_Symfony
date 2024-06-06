<?php

namespace App\Security\Voter;

use App\Entity\GroupePrive;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GroupePriveVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['view', 'edit'])
            && $subject instanceof GroupePrive;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof GroupePrive) {
            return false;
        }

        switch ($attribute) {
            case 'view':
            case 'edit':
                return $subject->getCreateur() === $user;
        }

        return false;
    }
}
