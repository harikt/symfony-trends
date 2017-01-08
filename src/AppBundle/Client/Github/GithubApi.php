<?php

namespace AppBundle\Client\Github;

use AppBundle\Model\GithubFork;
use AppBundle\Model\GithubCommit;
use AppBundle\Model\GithubUser;
use DateTimeInterface;
use Github\Client;
use Iterator;

class GithubApi implements GithubApiInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $repositoryPath
     * @param DateTimeInterface|null $since
     *
     * @return GithubCommit[]|Iterator
     */
    public function getCommits($repositoryPath, DateTimeInterface $since = null)
    {
        $page = 1;

        while ($commits = $this->getCommitsByPage($repositoryPath, $since, $page)) {

            foreach ($commits as $commit) {
                yield GithubCommit::createFromGithubResponseData($commit);
            }

            $page++;
        }
    }

    /**
     * @param string $repositoryPath
     * @param DateTimeInterface|null $since
     * @param int $page
     *
     * @return array
     */
    private function getCommitsByPage($repositoryPath, DateTimeInterface $since = null, $page = 1)
    {
        $options = ['page' => $page];

        if (null !== $since) {
            $options['since'] = $since->format('Y-m-d\TH:i:s\Z');
        }

        return $this->client->repo()->commits()->all($this->getOwner($repositoryPath), $this->getRepo($repositoryPath), $options);
    }

    /**
     * @param string $login
     *
     * @return GithubUser
     */
    public function getUser($login)
    {
        $data = $this->client->user()->show($login);

        return GithubUser::createFromGithubResponseData($data);
    }

    private function getOwner($repositoryPath)
    {
        $parts = explode('/', $repositoryPath);

        return $parts[0];
    }

    private function getRepo($repositoryPath)
    {
        $parts = explode('/', $repositoryPath);

        return $parts[1];
    }

    /**
     * @inheritdoc
     */
    public function getForks($repositoryPath)
    {
        $page = 1;

        while ($forks = $this->getForksByPage($repositoryPath, $page)) {

            foreach ($forks as $fork) {
                yield GithubFork::createFromGithubResponseData($fork);
            }

            $page++;
        }
    }

    /**
     * @param $repositoryPath
     * @param integer $page
     * @return array
     */
    protected function getForksByPage($repositoryPath, $page)
    {
        return $this->client->repo()->forks()->all($this->getOwner($repositoryPath), $this->getRepo($repositoryPath),
            ['page' => $page]);
    }
}