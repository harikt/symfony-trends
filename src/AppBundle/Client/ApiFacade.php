<?php

namespace AppBundle\Client;

use AppBundle\Model\GithubUser;

class ApiFacade
{
    /**
     * @var GithubApiClient
     */
    private $githubApi;
    /**
     * @var GeolocationApiClient
     */
    private $geoApi;

    /**
     * Constructor.
     *
     * @param GithubApiClient $githubApi
     * @param GeolocationApiClient $geoApi
     */
    public function __construct(GithubApiClient $githubApi, GeolocationApiClient $geoApi)
    {
        $this->githubApi = $githubApi;
        $this->geoApi = $geoApi;
    }

    /**
     * @param string $login
     *
     * @return GithubUser
     */
    public function getGithubUserWithLocation($login)
    {
        $user = $this->githubApi->getUser($login);

        $location = $country = '';

        $name = isset($user['name']) ? $user['name'] : '';
        $email = isset($user['email']) ? $user['email'] : '';

        if (isset($user['location'])) {
            $location = $user['location'];
            $countryData = $this->geoApi->findCountry($location);
            $country = $countryData['country'];
        }

        return new GithubUser($name, $email, $location, $country);
    }
}
