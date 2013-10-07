<?php

namespace Flexy\DoctrineBehaviorsBundle\ORM\Translatable;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class CurrentLocaleCallable
 */
class CurrentLocaleCallable
{
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
     * Called when used in a closure
     *
     * @return mixed|string
     */
    public function __invoke()
    {
        if (!$this->container->isScopeActive('request')) {
            return;
        }

        $request = $this->container->get('request');

        // In the Backend application, we want the editLocale
        if ($this->container->get('flexy_system.core')->isLoaded() && $this->container->get('flexy_system.core')->getCurrentAppName() == 'backend') {
            return $this->container->get('flexy_backend.core')->getEditLocale();
        }

        // Request Locale
        if ($locale = $request->getLocale()) {
            return $locale;
        }

        // System locale
        return $this->container->getParameter('locale');
    }
}

