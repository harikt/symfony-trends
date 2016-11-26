<?php

namespace AppBundle\Repository;

use AppBundle\Util\DateUtils;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * ContributionRepository
 */
class ContributionRepository extends Repository
{
    /**
     * @return array
     */
    public function getContributorProjectIntersection()
    {
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('project_ids', 'project_ids')
            ->addScalarResult('contributor_count', 'contributor_count');

        $query = $this
                    ->getEntityManager()
                    ->createNativeQuery(
                        'select project_ids, count(*) as contributor_count
                            from (
                                select contributor_id, group_concat(project_id) as project_ids
                                from (
                                    select distinct contributor_id, project_id
                                    from contribution
                                    order by project_id asc
                                )
                                group by contributor_id
                            )
                            group by project_ids',
                        $rsm);

        $result = $query->getResult();

        return $result;
    }

    /**
     * @param int $projectId
     *
     * @return array
     */
    public function getContributorCommitCounts($projectId)
    {
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('contributor_id', 'contributor_id')
            ->addScalarResult('contribution_count', 'contribution_count');

        $query = $this
            ->getEntityManager()
            ->createNativeQuery(
                'select contributor_id, count(*) as contribution_count
                    from contribution
                    where project_id = :project_id
                    group by contributor_id
                    order by contribution_count asc;',
                $rsm)
            ->setParameter('project_id', $projectId);

        $result = $query->getResult();

        return $result;
    }

    /**
     * @param array $projects
     * @param string $interval
     *
     * @return array
     */
    public function getContributionsPerDate(array $projects, $interval)
    {
        $intervalFormat = DateUtils::getDbIntervalFormat($interval);

        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult('date', 'date')
            ->addScalarResult('project_id', 'project_id')
            ->addScalarResult('contribution_count', 'contribution_count')
            ->addScalarResult('core_team_contribution_count', 'core_team_contribution_count');

        $query = $this
            ->getEntityManager()
            ->createNativeQuery('select 
                                    strftime(:format, commited_at) as date, project_id, 
                                    count(*) as contribution_count,
                                    sum(c.is_core_member) as core_team_contribution_count
                                from contribution cn
                                left join contributor c on cn.contributor_id = c.id 
                                where project_id in (:projectIds)
                                group by strftime(:format, commited_at), project_id
                                order by commited_at asc
                                ', $rsm)
            ->setParameter('format', $intervalFormat)
            ->setParameter('projectIds', $projects)
        ;

        $result = $query->getResult();

        return $result;
    }

    /**
     * @param int $projectId
     * @param DateTime $startedAt
     * @param DateTime $finishedAt
     *
     * @return int
     */
    public function getContributorCount($projectId, DateTime $startedAt, DateTime $finishedAt)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count(distinct c.contributorId)')
            ->where('c.projectId = :projectId')
            ->andWhere('c.commitedAt > :startedAt')
            ->andWhere('c.commitedAt <= :finishedAt')
            ->setParameter('projectId', $projectId)
            ->setParameter('startedAt', $startedAt)
            ->setParameter('finishedAt', $finishedAt)
        ;

        $result = $qb->getQuery()->getSingleResult();

        return (int)$result[1];
    }

    /**
     * @param int $projectId
     *
     * @return DateTimeImmutable
     */
    public function getLastCommitDate($projectId)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('MAX(c.commitedAt)')
            ->where('c.projectId = :id')
            ->setParameter('id', $projectId);

        $result = $qb->getQuery()->getSingleScalarResult();

        return new DateTimeImmutable($result);
    }


}
