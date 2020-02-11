<?php namespace D7ServiceContainer\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class MioServiceContainerExtension extends ConfigurableExtension {
    const CONFIG_EXTS = '\.{yaml,yml}';
    /**
     * Configures the passed container according to the merged configuration.
     *
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $this->registerAnphabeServices($container);
        $this->registerDrupalModulesServices($container);
    }

    private function registerAnphabeServices(ContainerBuilder $container) {

        // check if we have service lookup-path con
        //figured in the parameters section
        if ($container->hasParameter('mio.service_lookup_paths')) {
            $paths = $container->getParameter('mio.service_lookup_paths');
        }
        else {
            $paths = [
              "%kernel.project_dir%/vendor/mio",
              "%kernel.project_dir%/vendor/anphabe",
              "%kernel.project_dir%/vendor/anphabe-src",
            ];
        }
        
        

        $finder = new Finder();
        $finder->files()
               ->name('services'.self::CONFIG_EXTS);

        foreach ($paths as $path) {
            $finder->in($container->getParameterBag()->resolveValue($path));
        }



        foreach ($finder as $file) {
            /**
             * @var SplFileInfo $file
             */
            $loader = new Loader\YamlFileLoader($container, new FileLocator($file->getPath()));
            $loader->load('services.'.$file->getExtension());
        }

    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws \Exception
     */
    private function registerDrupalModulesServices(ContainerBuilder $container) {
        if (!$container->hasParameter('kernel.drupal_dir')) return;
        
        // recognize Drupal modules by finding their info files
        $finder = new Finder();
        $finder->files()
               ->name('*.info')
               ->in($container->getParameterBag()->resolveValue($container->getParameter('kernel.drupal_dir')) . '/sites/all/modules');

        $classes_map   = array();
        $service_files = array();

        foreach ($finder as $file) {
            /**
             * @var SplFileInfo $file
             */
            $module_name  = $file->getPathInfo()->getBasename();
            $module_path  = $file->getPathInfo()->getRealPath();


            // add namespace mapping
            $classes_map["Drupal\\" . $module_name] = $module_path . DIRECTORY_SEPARATOR . 'lib';

            // add service definition file
            $service_file = $module_path . DIRECTORY_SEPARATOR . $module_name . '.services.yml';
            if (file_exists($service_file)) {
                $service_files[$service_file] = $module_path;
            }
            else {
                $service_file = $module_path . DIRECTORY_SEPARATOR . $module_name . '.services.yaml';
                if (file_exists($service_file)) {
                    $service_files[$service_file] = $module_path;
                }
            }
        }

        // parse and register all Drupal module service definition into container
        foreach ($service_files as $filename => $filepath) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator($filepath));
            $loader->load($filename);
        }

        // register all Drupal module namespaces into container
        //$container->setParameter('drupal.classes_map', $classes_map);
    }

}