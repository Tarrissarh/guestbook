<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Conference;

/**
 * Class CommentRepository
 *
 * @package App\Repository
 */
class CommentRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentPaginator(Conference $conference, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c')
                      ->andWhere('c.conference = :conference')
                      ->setParameter('conference', $conference)
                      ->orderBy('c.createdAt', 'DESC')
                      ->setMaxResults(self::PAGINATOR_PER_PAGE)
                      ->setFirstResult($offset)
                      ->getQuery();

        return new Paginator($query);
    }
}
