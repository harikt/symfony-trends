<?php


namespace features\Fake;

use AppBundle\Client\Github\GithubApiInterface;
use AppBundle\Model\GithubCommit;
use AppBundle\Model\GithubFork;
use AppBundle\Model\GithubPullRequest;
use AppBundle\Model\GithubUser;
use DateTimeInterface;
use Iterator;

class GithubApi implements GithubApiInterface
{
    use FakeDataAware;

    /**
     * @param string $repositoryPath
     * @param DateTimeInterface|null $since
     *
     * @return GithubCommit[]|Iterator
     */
    public function getCommits($repositoryPath, DateTimeInterface $since = null)
    {
        $commits = [];
        foreach ($this->fakeData['commits'] as $item) {
            $commits[] = new GithubCommit($item);
        }

        return new \ArrayIterator($commits);
    }

    /**
     * @param string $login
     *
     * @return GithubUser
     */
    public function getUser($login)
    {
        $data = $this->findBy('users', 'login', $login);

        return GithubUser::createFromResponseData($data);
    }

    /**
     * @param string $repositoryPath
     *
     * @return GithubFork[]|\Iterator
     */
    public function getForks($repositoryPath)
    {
        $forks = [];
        foreach ($this->fakeData['forks'] as $item) {
            $forks[] = GithubFork::createFromArray($item);
        }

        return new \ArrayIterator($forks);
    }



    /**
     * @param $repositoryPath
     *
     * @return array
     */
    public function getIssues($repositoryPath)
    {
        // TODO: Implement getIssues() method.
    }

    /**
     * @param $repositoryPath
     * @param DateTimeInterface $since
     *
     * @return GithubPullRequest[]|\Iterator
     */
    public function getPullRequests($repositoryPath, DateTimeInterface $since = null)
    {
        // TODO: Implement getPullRequests() method.
    }
}
