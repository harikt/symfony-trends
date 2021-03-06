<?php

namespace spec\AppBundle\Model;

use AppBundle\Model\GithubUser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin GithubUser
 */
class GithubUserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('frodo', 'frodo@shire', 'Bag End');
        $this->shouldHaveType(GithubUser::class);
    }

    function it_can_be_created_from_resonse_data()
    {
        $this->beConstructedThrough('createFromResponseData', [[
            'name' =>'frodo',
            'email' => 'frodo@shire',
            'location' => 'Bag End'
        ]]);

        $this->shouldHaveType(GithubUser::class);
    }
}
