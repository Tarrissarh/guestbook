<?php

namespace App\Repository;

use App\Entity\Conference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ConferenceRepository
 *
 * @package App\Repository
 */
class ConferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    /**
     * @return Conference[]|array
     */
    public function findAll()
    {
        return $this->findBy([], ['year' => 'ASC', 'city' => 'ASC']);
    }
}
