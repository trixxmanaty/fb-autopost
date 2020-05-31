<?php

namespace App\Repository;

use App\Entity\SearchTerms;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SearchTerms|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchTerms|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchTerms[]    findAll()
 * @method SearchTerms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchTermsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchTerms::class);
    }

    public function update($id, $value)
    {
        return $this->createQueryBuilder('s')
            ->update()
            ->set('s.searched', ':searched')
            ->setParameter(':searched', $value)

            ->where('s.id = :id')
            ->setParameter(':id', $id)

            ->getQuery()
            ->execute();
    }
}
