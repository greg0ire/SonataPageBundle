<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\PageBundle\DependencyInjection\SonataPageExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Twig\Extra\String\StringExtension;

/**
 * @author Rémi Marseille <marseille@ekino.com>
 */
class SonataPageExtensionTest extends AbstractExtensionTestCase
{
    public function testRequestContextServiceIsDefined(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.router.request_context');
    }

    public function testApiServicesAreDefinedWhenSpecificBundlesArePresent(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'FOSRestBundle' => 42,
            'NelmioApiDocBundle' => 42,
        ]);
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.serializer.handler.page');
    }

    public function testAdminServicesAreDefinedWhenAdminBundlesIsPresent(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'SonataAdminBundle' => 42,
        ]);
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.admin.page');
    }

    public function testRouterAutoRegister(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'CmfRouterBundle' => 42,
        ]);
        $this->load([
            'router_auto_register' => [
                'enabled' => true,
                'priority' => 84,
            ],
        ]);
        $this->assertContainerBuilderHasParameter('sonata.page.router_auto_register.enabled', true);
        $this->assertContainerBuilderHasParameter('sonata.page.router_auto_register.priority', 84);
    }

    public function testDatePickerFormThemeFromSonataCore(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'SonataCoreBundle' => 'SonataCoreBundle',
            'SonataFormBundle' => 'SonataFormBundle',
        ]);
        $this->container->setParameter('kernel.bundles_metadata', []);
        $this->container->setParameter('kernel.project_dir', __DIR__);
        $this->container->setParameter('kernel.root_dir', __DIR__);
        $this->container->setParameter('kernel.debug', false);
        $this->container->registerExtension(new TwigExtension());

        $this->container->compile();
        $this->assertContains(
            '@SonataCore/Form/datepicker.html.twig',
            $this->container->getParameter('twig.form.resources')
        );
    }

    public function testDatePickerFormThemeFromSonataForm(): void
    {
        $this->container->setParameter('kernel.bundles', ['SonataFormBundle' => 'SonataFormBundle']);
        $this->container->setParameter('kernel.bundles_metadata', []);
        $this->container->setParameter('kernel.project_dir', __DIR__);
        $this->container->setParameter('kernel.root_dir', __DIR__);
        $this->container->setParameter('kernel.debug', false);
        $this->container->registerExtension(new TwigExtension());

        $this->container->compile();
        $this->assertContains(
            '@SonataForm/Form/datepicker.html.twig',
            $this->container->getParameter('twig.form.resources')
        );
    }

    public function testLoadTwigStringExtension(): void
    {
        $this->container->setParameter('kernel.bundles', []);

        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithTag(StringExtension::class, 'twig.extension');
    }

    protected function getContainerExtensions(): array
    {
        return [new SonataPageExtension()];
    }

    protected function getMinimalConfiguration(): array
    {
        return [
            'multisite' => 'host',
            'default_template' => null,
            'templates' => null,
        ];
    }
}
