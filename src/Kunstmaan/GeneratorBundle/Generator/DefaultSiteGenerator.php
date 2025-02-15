<?php

namespace Kunstmaan\GeneratorBundle\Generator;

use Doctrine\Persistence\ManagerRegistry;
use Kunstmaan\GeneratorBundle\Helper\CommandAssistant;
use Kunstmaan\GeneratorBundle\Helper\GeneratorUtils;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;

/**
 * Generates a default website using several Kunstmaan bundles using default templates and assets
 */
class DefaultSiteGenerator extends KunstmaanGenerator
{
    /**
     * @var BundleInterface
     */
    private $bundle;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var bool
     */
    private $demosite;

    /**
     * @var bool
     */
    private $groundControl;
    /** @var DoctrineHelper */
    private $doctrineHelper;

    public function __construct(Filesystem $filesystem, ManagerRegistry $registry, $skeletonDir, CommandAssistant $assistant, ContainerInterface $container, DoctrineHelper $doctrineHelper)
    {
        parent::__construct($filesystem, $registry, $skeletonDir, $assistant, $container);
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Generate the website.
     *
     * @param string $prefix
     * @param string $rootDir
     * @param bool   $demosite
     */
    public function generate(BundleInterface $bundle, $prefix, $rootDir, $demosite = false, $groundControl = false)
    {
        $this->bundle = $bundle;
        $this->prefix = GeneratorUtils::cleanPrefix($prefix);
        $this->rootDir = $rootDir;
        $this->demosite = $demosite;
        $this->groundControl = $groundControl;

        $parameters = [
            'namespace' => $this->bundle->getNamespace(),
            'bundle' => $this->bundle,
            'bundle_name' => $this->bundle->getName(),
            'prefix' => $this->prefix,
            'demosite' => $this->demosite,
            'multilanguage' => $this->isMultiLangEnvironment(),
            'groundcontrol' => $this->groundControl,
            'canUseAttributes' => Kernel::VERSION_ID >= 50200,
            'canUseEntityAttributes' => $this->doctrineHelper->doesClassUsesAttributes('App\\Entity\\Unkown' . uniqid()),
        ];

        $this->generateControllers($parameters);
        $this->generateAdminLists($parameters);
        $this->generateEntities($parameters);
        $this->generateFormTypes($parameters);
        $this->generateTwigExtensions($parameters);
        $this->generateMenuAdaptors($parameters);
        $this->generateFixtures($parameters);
        $this->generatePagepartConfigs($parameters);
        $this->generatePagetemplateConfigs($parameters);
        $this->generateTemplates($parameters);
    }

    /**
     * Generate controller classes.
     */
    private function generateControllers(array $parameters)
    {
        $relPath = '/Controller/';
        $sourceDir = $this->skeletonDir . $relPath;
        $targetDir = $this->bundle->getPath() . $relPath;

        $this->renderSingleFile($sourceDir, $targetDir, 'DefaultController.php', $parameters, true);

        if ($this->demosite) {
            $this->renderSingleFile($sourceDir, $targetDir, 'BikeAdminListController.php', $parameters, true);
        }

        $this->assistant->writeLine('Generating controllers : <info>OK</info>');
    }

    /**
     * Generate admin list classes.
     */
    private function generateAdminLists(array $parameters)
    {
        if ($this->demosite) {
            $relPath = '/AdminList/';
            $this->renderFiles($this->skeletonDir . $relPath, $this->bundle->getPath() . $relPath, $parameters, true);

            $this->assistant->writeLine('Generating admin lists : <info>OK</info>');
        }
    }

    /**
     * Generate the entity classes.
     *
     * @param array $parameters The template parameters
     */
    public function generateEntities(array $parameters)
    {
        $relPath = '/Entity/Pages/';
        $sourceDir = $this->skeletonDir . $relPath;
        $targetDir = $this->bundle->getPath() . $relPath;

        $this->renderSingleFile($sourceDir, $targetDir, 'HomePage.php', $parameters);
        $this->renderSingleFile($sourceDir, $targetDir, 'ContentPage.php', $parameters);
        $this->renderSingleFile($sourceDir, $targetDir, 'BehatTestPage.php', $parameters);

        if ($this->demosite) {
            $this->renderSingleFile($sourceDir, $targetDir, 'FormPage.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'SearchPage.php', $parameters);
        }

        if ($this->demosite) {
            $relPath = '/Entity/PageParts/';
            $sourceDir = $this->skeletonDir . $relPath;
            $targetDir = $this->bundle->getPath() . $relPath;

            $this->renderSingleFile($sourceDir, $targetDir, 'PageBannerPagePart.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'ServicePagePart.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'UspPagePart.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'BikesListPagePart.php', $parameters);
        }

        if ($this->demosite) {
            $relPath = '/Entity/';
            $sourceDir = $this->skeletonDir . $relPath;
            $targetDir = $this->bundle->getPath() . $relPath;

            $this->renderSingleFile($sourceDir, $targetDir, 'Bike.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'UspItem.php', $parameters);
        }

        $this->assistant->writeLine('Generating entities : <info>OK</info>');
    }

    /**
     * Generate the form type classes.
     *
     * @param array $parameters The template parameters
     */
    public function generateFormTypes(array $parameters)
    {
        $relPath = '/Form/Pages/';
        $sourceDir = $this->skeletonDir . $relPath;
        $targetDir = $this->bundle->getPath() . $relPath;

        $this->renderSingleFile($sourceDir, $targetDir, 'HomePageAdminType.php', $parameters);
        $this->renderSingleFile($sourceDir, $targetDir, 'ContentPageAdminType.php', $parameters);
        $this->renderSingleFile($sourceDir, $targetDir, 'BehatTestPageAdminType.php', $parameters);

        if ($this->demosite) {
            $this->renderSingleFile($sourceDir, $targetDir, 'FormPageAdminType.php', $parameters);
        }

        if ($this->demosite) {
            $relPath = '/Form/PageParts/';
            $sourceDir = $this->skeletonDir . $relPath;
            $targetDir = $this->bundle->getPath() . $relPath;

            $this->renderSingleFile($sourceDir, $targetDir, 'PageBannerPagePartAdminType.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'ServicePagePartAdminType.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'UspPagePartAdminType.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'BikesListPagePartAdminType.php', $parameters);
        }

        if ($this->demosite) {
            $relPath = '/Form/';
            $sourceDir = $this->skeletonDir . $relPath;
            $targetDir = $this->bundle->getPath() . $relPath;

            $this->renderSingleFile($sourceDir, $targetDir, 'BikeAdminType.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'UspItemAdminType.php', $parameters);
        }

        $this->assistant->writeLine('Generating form types : <info>OK</info>');
    }

    /**
     * Generate the menu adaptors classes.
     *
     * @param array $parameters The template parameters
     */
    public function generateMenuAdaptors(array $parameters)
    {
        if ($this->demosite) {
            $relPath = '/Helper/Menu/';
            $sourceDir = $this->skeletonDir . $relPath;
            $targetDir = $this->bundle->getPath() . $relPath;

            $this->renderSingleFile($sourceDir, $targetDir, 'AdminMenuAdaptor.php', $parameters);
        }
    }

    /**
     * Generate the data fixtures classes.
     *
     * @param array $parameters The template parameters
     */
    public function generateFixtures(array $parameters)
    {
        $relPath = '/DataFixtures/ORM/DefaultSiteGenerator/';
        $sourceDir = $this->skeletonDir . $relPath;
        $targetDir = $this->bundle->getPath() . $relPath;

        $this->renderSingleFile($sourceDir, $targetDir, 'DefaultSiteFixtures.php', $parameters);

        if ($this->demosite) {
            $this->renderSingleFile($sourceDir, $targetDir, 'SliderFixtures.php', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'SitemapFixtures.php', $parameters);
        }

        $this->assistant->writeLine('Generating fixtures : <info>OK</info>');
    }

    /**
     * Generate the pagepart section configuration.
     *
     * @param array $parameters The template parameters
     */
    public function generatePagepartConfigs(array $parameters)
    {
        $basePath = $this->container->getParameter('kernel.project_dir');
        $relPath = '/config/kunstmaancms/pageparts';
        $sourceDir = $this->skeletonDir . '/Resources/config/pageparts/';
        $targetDir = $basePath . $relPath;

        $this->renderSingleFile($sourceDir, $targetDir, 'main.yml', $parameters);

        if ($this->demosite) {
            $this->renderSingleFile($sourceDir, $targetDir, 'header.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'section1.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'section2.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'section3.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'section4.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'section5.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'form.yml', $parameters);
        }

        $this->assistant->writeLine('Generating pagepart configuration : <info>OK</info>');
    }

    /**
     * Generate the page template configuration.
     *
     * @param array $parameters The template parameters
     */
    public function generatePagetemplateConfigs(array $parameters)
    {
        $basePath = $this->container->getParameter('kernel.project_dir');
        $relPath = '/config/kunstmaancms/pagetemplates/';
        $sourceDir = $this->skeletonDir . '/Resources/config/pagetemplates/';
        $targetDir = $basePath . $relPath;

        $this->renderSingleFile($sourceDir, $targetDir, 'homepage.yml', $parameters);
        $this->renderSingleFile($sourceDir, $targetDir, 'contentpage.yml', $parameters);
        $this->renderSingleFile($sourceDir, $targetDir, 'behat-test-page.yml', $parameters);

        if ($this->demosite) {
            $this->renderSingleFile($sourceDir, $targetDir, 'contentpage-with-submenu.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'formpage.yml', $parameters);
            $this->renderSingleFile($sourceDir, $targetDir, 'searchpage.yml', $parameters);
        }

        $this->assistant->writeLine('Generating pagetemplate configuration : <info>OK</info>');
    }

    /**
     * Append to the application config file.
     */
    public function generateConfig()
    {
        trigger_deprecation('kunstmaan/generator-bundle', '6.2', 'Method "%s" is deprecated and will be removed.', __METHOD__);

        if ($this->isSymfony4()) {
            return;
        }

        $configFile = $this->rootDir . '/app/config/config.yml';
        $config = file_get_contents($configFile);

        $data = Yaml::parse($config);
        if (!array_key_exists('white_october_pagerfanta', $data)) {
            $ymlData = "\n\nwhite_october_pagerfanta:";
            $ymlData .= "\n    default_view: twitter_bootstrap\n";
            file_put_contents($configFile, $ymlData, FILE_APPEND);
        }
    }

    /**
     * Generate bundle routing configuration.
     *
     * @param array $parameters The template parameters
     */
    public function generateRouting(array $parameters)
    {
        trigger_deprecation('kunstmaan/generator-bundle', '6.2', 'Method "%s" is deprecated and will be removed.', __METHOD__);

        if ($this->isSymfony4()) {
            return;
        }

        $relPath = '/Resources/config/';
        $sourceDir = $this->skeletonDir . $relPath;
        $targetDir = $this->bundle->getPath() . $relPath;

        $this->renderSingleFile($sourceDir, $targetDir, 'routing.yml', $parameters, true);

        $this->assistant->writeLine('Generating routing : <info>OK</info>');
    }

    /**
     * Generate the twig templates.
     *
     * @param array $parameters The template parameters
     */
    public function generateTemplates(array $parameters)
    {
        $relPath = '/Resources/views/Layout/';
        $sourceDir = $this->skeletonDir . $relPath;
        $targetDir = $this->getTemplateDir($this->bundle) . '/Layout/';

        if ($this->demosite) {
            $this->renderSingleFile($sourceDir, $targetDir, 'submenu.html.twig', $parameters);
        }

        // Pages

        $relPath = '/Resources/views/Pages/HomePage/';
        $sourceDir = $this->skeletonDir . $relPath;
        $targetDir = $this->getTemplateDir($this->bundle) . '/Pages/HomePage/';

        $this->renderSingleFile($sourceDir, $targetDir, 'pagetemplate.html.twig', $parameters);
        $this->renderSingleFile($sourceDir, $targetDir, 'view.html.twig', $parameters);

        $relPath = '/Resources/views/Pages/ContentPage/';
        $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/Pages/ContentPage/', $parameters, true);

        if ($this->demosite) {
            $relPath = '/Resources/views/Pages/FormPage/';
            $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/Pages/FormPage/', $parameters, true);

            $relPath = '/Resources/views/Pages/SearchPage/';
            $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/Pages/SearchPage/', $parameters, true);
        }

        // Pageparts

        if ($this->demosite) {
            $relPath = '/Resources/views/PageParts/PageBannerPagePart/';
            $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/PageParts/PageBannerPagePart/', $parameters, true);

            $relPath = '/Resources/views/PageParts/ServicePagePart/';
            $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/PageParts/ServicePagePart/', $parameters, true);

            $relPath = '/Resources/views/PageParts/UspPagePart/';
            $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/PageParts/UspPagePart/', $parameters, true);

            $relPath = '/Resources/views/PageParts/BikesListPagePart/';
            $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/PageParts/BikesListPagePart/', $parameters, true);
        }

        // Error templates
        $relPath = '/Resources/views/Error/';
        $this->renderFiles($this->skeletonDir . $relPath, $this->getTemplateDir($this->bundle) . '/Error/', $parameters, true);

        $sourcePath = '/app/TwigBundle/views';
        $targetPath = $this->rootDir . '/templates/bundles/TwigBundle';
        $this->renderFiles($this->skeletonDir . $sourcePath, $targetPath, $parameters, true);

        // Bundle overwrites

        if ($this->demosite) {
            $sourcePath = '/app/KunstmaanSitemapBundle/views';
            $targetPath = $this->rootDir . '/templates/bundles/KunstmaanSitemapBundle';

            $this->renderFiles($this->skeletonDir . $sourcePath, $targetPath, $parameters, true);

            $sourcePath = '/app/KunstmaanFormBundle/views';
            $targetPath = $this->rootDir . '/templates/bundles/KunstmaanFormBundle';
            $this->renderFiles($this->skeletonDir . $sourcePath, $targetPath, $parameters, true);
        }

        $this->assistant->writeLine('Generating template files : <info>OK</info>');
    }

    /**
     * Generate the twig extensions.
     *
     * @param array $parameters The template parameters
     */
    public function generateTwigExtensions($parameters)
    {
        $relPath = '/Twig/';
        if ($this->demosite) {
            $this->renderSingleFile($this->skeletonDir . $relPath, $this->bundle->getPath() . $relPath, 'BikesTwigExtension.php', $parameters, true);
        }
    }

    /**
     * Returns true if we detect the site uses the locale.
     *
     * @return bool
     */
    private function isMultiLangEnvironment()
    {
        // use the multilanguage parameter, if it exists
        if ($this->container->hasParameter('kunstmaan_admin.multi_language')) {
            return $this->container->getParameter('kunstmaan_admin.multi_language');
        }

        // This is a pretty silly implementation.
        // It just checks if it can find _locale in the routing.yml
        $routingFile = file_get_contents($this->rootDir . '/app/config/routing.yml');

        return preg_match('/_locale:/i', $routingFile);
    }
}
