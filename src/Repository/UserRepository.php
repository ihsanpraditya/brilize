<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Enum\UserStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Mencari user berdasarkan identifier (email, username, NIP, atau NISN)
     */
    public function findByIdentifier(string $identifier): ?User
    {
        $trimmed = trim($identifier);

        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) = :identifier')
            ->orWhere('LOWER(u.username) = :identifier')
            ->orWhere('u.identifierNumber = :rawIdentifier')
            ->setParameter('identifier', strtolower($trimmed))
            ->setParameter('rawIdentifier', $trimmed)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('JSON_GET_TEXT(u.roles, :role) IS NOT NULL OR u.roles LIKE :roleLike')
            ->setParameter('role', $role)
            ->setParameter('roleLike', '%' . $role . '%')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function searchUsers(?string $query = null, ?UserStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        if ($query) {
            $qb->andWhere('LOWER(u.name) LIKE :q OR LOWER(u.email) LIKE :q OR u.identifierNumber LIKE :q')
               ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($status) {
            $qb->andWhere('u.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }
}
