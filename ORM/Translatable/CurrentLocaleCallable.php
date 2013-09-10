<?php

namespace Egzakt\DoctrineBehaviorsBundle\ORM\Translatable;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class CurrentLocaleCallable
 * @package Egzakt\DoctrineBehaviorsBundle\ORM\Translatable
 *
 * This is a replacement for the KnpLabs CurrentLocaleCallable class.
 * In the backend, we need to use a different locale than the one in the Request.
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
     * Invoke
     *
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
        if ($this->container->get('egzakt_system.core')->isLoaded() && $this->container->get('egzakt_system.core')->getCurrentAppName() == 'backend') {
            return $this->container->get('egzakt_backend.core')->getEditLocale();
        }

        // Request Locale
        if ($locale = $request->getLocale()) {
            return $locale;
        }

        // System locale
        return $this->container->getParameter('locale');
    }
}

