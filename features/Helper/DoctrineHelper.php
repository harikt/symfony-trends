<?php


namespace features\Helper;

use Aa\ArrayDiff\Calculator;
use Aa\ArrayDiff\Matcher\SimpleMatcher;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Traversable;

class DoctrineHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PurgerInterface
     */
    private $purger;

    public function __construct(EntityManager $em, PurgerInterface $purger)
    {
        $this->em = $em;
        $this->purger = $purger;
    }

    /**
     * Purge all entities
     */
    public function purgeEntities()
    {
        $this->purger->purge();
        $this->updatePostgresqlSequences();
    }

    /**
     * Create and store entities
     *
     * @param string $entityClass
     * @param Traversable $records
     */
    public function createEntities($entityClass, Traversable $records)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($records as $record) {

            $entity = new $entityClass();

            foreach ($record as $propertyName => $propertyValue) {

                if ('~' === $propertyValue) {
                    $propertyValue = null;
                }

                $matches = [];
                if (preg_match('/^\[(.*)\]$/', $propertyValue, $matches)) {
                    $propertyValue = explode(',', $matches[1]);
                }

                $propertyAccessor->setValue($entity, $propertyName, $propertyValue);
            }

            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    /**
     * Compare existing entities with given records
     *
     * @param string $entityClass
     * @param Traversable $records
     *
     * @throws Exception
     */
    public function checkEntities($entityClass, Traversable $records)
    {
        $queryBuilder = $this->em->getRepository($entityClass)->createQueryBuilder('data');
        $actualData = $queryBuilder->getQuery()->getArrayResult();

        $expectedData = [];
        foreach ($records as &$expectedRow) {
            foreach ($expectedRow as $key => $value) {
                if ('~' === $value) {
                    $expectedRow[$key] = null;
                }

                $matches = [];
                if (preg_match('/^\[(.*)\]$/', $value, $matches)) {
                    $expectedRow[$key] = explode(',', $matches[1]);
                }
            }

            $expectedData[] = $expectedRow;
        }

        $calc = new Calculator(new SimpleMatcher());
        $diff = $calc->calculateDiff($expectedData, $actualData);

        if (0 < count($diff->getMissing()) + count($diff->getUnmatched())) {
            throw new Exception('Expected entities are different from actual entities:'.PHP_EOL.$diff->toString());
        }
    }

    private function updatePostgresqlSequences()
    {
        if (!$this->em->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform) {
            return;
        }

        $sequences = $this->getSequencesNames();

        foreach ($sequences as $sequence) {
            $query = $this->em->createNativeQuery(sprintf('ALTER SEQUENCE %s RESTART;', $sequence), new ResultSetMapping());
            $query->execute();
        }
    }

    /**
     * @return array
     */
    private function getSequencesNames()
    {
        $sequences = [];

        /** @var ClassMetadata $metadata */
        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $metadata) {

            if (!$metadata->isMappedSuperclass &&
                !(isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass) &&
                isset($metadata->sequenceGeneratorDefinition)
            ) {
                $sequences[] = $metadata->sequenceGeneratorDefinition['sequenceName'];
            }
        }

        return $sequences;
    }
}
