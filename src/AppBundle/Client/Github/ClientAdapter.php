<?php

namespace AppBundle\Client\Github;

use AppBundle\Model\GithubCommit;
use DateTimeInterface;
use Github\Client;
use Iterator;

class ClientAdapter implements ClientAdapterInterface
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

            foreach ($commits as $commitData) {
                yield GithubCommit::createFromGithubResponseData($commitData);
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

        return $this->client->api('repo')->commits()->all($this->getOwner($repositoryPath), $this->getRepo($repositoryPath), $options);
    }

    /**
     * @param string $login
     *
     * @return array
     */
    public function getUser($login)
    {
        return $this->client->api('user')->show($login);
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
}
