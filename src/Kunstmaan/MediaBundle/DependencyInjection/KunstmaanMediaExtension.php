<?php

namespace Kunstmaan\MediaBundle\DependencyInjection;

use Gedmo\DoctrineExtensions;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Yaml\Yaml;

class KunstmaanMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads configuration
     *
     * @param array            $configs   Configuration
     * @param ContainerBuilder $container Container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter(
            'twig.form.resources',
            array_merge(
                $container->hasParameter('twig.form.resources') ? $container->getParameter('twig.form.resources') : [],
                ['@KunstmaanMedia/Form/formWidgets.html.twig']
            )
        );
        $container->setParameter('kunstmaan_media.soundcloud_api_key', $config['soundcloud_api_key']);
        $container->setParameter('kunstmaan_media.remote_video', $config['remote_video']);
        $container->setParameter('kunstmaan_media.enable_pdf_preview', $config['enable_pdf_preview']);
        $container->setParameter('kunstmaan_media.blacklisted_extensions', $config['blacklisted_extensions']);
        $container->setParameter('kunstmaan_media.web_root', $config['web_root']);
        $container->setParameter('kunstmaan_media.full_media_path', $config['web_root'] . '%kunstmaan_media.media_path%');
        $container->setParameter('kunstmaan_media.cropping_views', $config['cropping_views']);

        $loader->load('services.yml');
        $loader->load('handlers.yml');

        if ($config['enable_pdf_preview'] === true) {
            $loader->load('pdf_preview.yml');
        }

        $container->setParameter('liip_imagine.filter.loader.background.class', 'Kunstmaan\MediaBundle\Helper\Imagine\BackgroundFilterLoader');

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('imagine.xml');

        $container->setAlias('liip_imagine.controller', 'Kunstmaan\MediaBundle\Helper\Imagine\ImagineController')->setPublic(true);
        $container->setAlias('Liip\ImagineBundle\Controller\ImagineController', 'Kunstmaan\MediaBundle\Helper\Imagine\ImagineController')->setPublic(true);
        $container->setAlias('liip_imagine.cache.resolver.prototype.web_path', 'Kunstmaan\MediaBundle\Helper\Imagine\WebPathResolver');
        $container->setAlias('liip_imagine.cache.manager', 'Kunstmaan\MediaBundle\Helper\Imagine\CacheManager')->setPublic(true);

        if (!$container->hasDefinition('mime_types')) {
            $mimeTypes = new Definition(MimeTypes::class);
            $mimeTypes->setPublic(true);
            $container->setDefinition('mime_types', $mimeTypes);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        if (!$container->hasParameter('kunstmaan_media.upload_dir')) {
            $container->setParameter('kunstmaan_media.upload_dir', '/uploads/media/');
        }

        $twigConfig = [];
        $twigConfig['globals']['upload_dir'] = $container->getParameter('kunstmaan_media.upload_dir');
        $twigConfig['globals']['mediabundleisactive'] = true;
        $twigConfig['globals']['mediamanager'] = '@kunstmaan_media.media_manager';
        $container->prependExtensionConfig('twig', $twigConfig);

        $liipConfig = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/imagine_filters.yml'));
        $container->prependExtensionConfig('liip_imagine', $liipConfig['liip_imagine']);

        $defaultLocale = $container->hasParameter('kunstmaan_admin.default_locale') ? $container->getParameter('kunstmaan_admin.default_locale') : 'en';
        $stofDoctrineExtensionsConfig = [
            'default_locale' => $defaultLocale,
            'translation_fallback' => true,
            'orm' => [
                'default' => [
                    'translatable' => true,
                ],
            ],
        ];

        $container->prependExtensionConfig('stof_doctrine_extensions', $stofDoctrineExtensionsConfig);

        $baseDir = version_compare(DoctrineExtensions::VERSION, '3.0.0', '>=') ? 'src' : 'lib/Gedmo';

        $doctrineGedmoEntityConfig = [
            'orm' => [
                'mappings' => [
                    'gedmo_translatable' => [
                        'type' => 'annotation',
                        'prefix' => 'Gedmo\Translatable\Entity',
                        'dir' => '%kernel.project_dir%/vendor/gedmo/doctrine-extensions/' . $baseDir . '/Translatable/Entity',
                        'alias' => 'GedmoTranslatable',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ];

        $container->prependExtensionConfig('doctrine', $doctrineGedmoEntityConfig);

        $configs = $container->getExtensionConfig($this->getAlias());
        $this->processConfiguration(new Configuration(), $configs);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'kunstmaan_media';
    }
}
