<?php

namespace App\Repository;

use App\Entity\Attribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Attribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attribute[]    findAll()
 * @method Attribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribute::class);
    }

    /**
     * @param string $code
     * @return int|mixed|string
     */
    public function findAttributesByCategory(string $code)
    {
        $query = $this->getEntityManager()->createQuery('
            SELECT a FROM App\Entity\Attribute a JOIN a.category c WHERE c.code = :code 
        ')->setParameters(['code' => $code]);
        return $query->getResult();
    }

    /**
     * @return int|mixed|string
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countAttributes()
    {
        $query = $this->getEntityManager()->createQuery('SELECT COUNT(n.id) FROM App\Entity\Attribute n');
        return $query->getSingleScalarResult();
    }

    /**
     * @param string $type
     * @return int|mixed|string
     */
    public function computeAveragesForType(string $type)
    {
        $query = $this->getEntityManager()->createQuery('
            SELECT a.title, a.uuid, c.uuid as categoryUuid, c.code as categoryCode, COUNT(a.uuid) FROM App\Entity\Number n 
                JOIN n.attributes a
                JOIN a.category c
                WHERE c.code = :code
                GROUP BY a.title, a.uuid, c.uuid, c.code
        ');

        $query->setParameters(['code' => $type]);

        return $query->getResult();
    }

    /**
     * @param string $type
     * @param string $personUuid
     * @return int|mixed|string
     */
    public function computeAveragesForTypeAndPerson(string $type, string $personUuid)
    {
        $query = $this->getEntityManager()->createQuery('
            SELECT a.title, a.uuid, c.uuid as categoryUuid, c.code as categoryCode, COUNT(a.uuid) FROM App\Entity\Number n 
                JOIN n.attributes a
                JOIN a.category c
                INNER JOIN App\Entity\Work w WITH w.targetUuid = n.uuid
                INNER JOIN App\Entity\Person p WITH p.id = w.person
                WHERE c.code = :code AND p.uuid = :personUuid
                GROUP BY a.title, a.uuid, c.uuid, c.code
        ');

        $query->setParameters([
            'code' => $type,
            'personUuid' => $personUuid
//            'personUuid' => '5fccf77e-6e15-4373-8e83-27a071011e7a' // fred Astaire dev
        ]);

        return $query->getResult();
    }
}
