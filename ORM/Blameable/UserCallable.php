<?php

namespace Unifik\DoctrineBehaviorsBundle\ORM\Blameable;

use Symfony\Component\DependencyInjection\Container;

/**
 * UserCallable can be invoked to return a blameable user
 */
class UserCallable
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke
     *
     * @return mixed
     */
    public function __invoke()
    {
        $token = $this->container->get('security.context')->getToken();

        if (null !== $token) {
            return $token->getUser();
        }
    }
}
