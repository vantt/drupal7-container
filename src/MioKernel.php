<?php
namespace D7ServiceContainer;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;


class MioKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $bundles = [
            // new D7ServiceContainerBundle(),

//            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
//            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
//            new Symfony\Bundle\TwigBundle\TwigBundle(),
//            new Symfony\Bundle\MonologBundle\MonologBundle(),
//            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
//            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
//            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
//            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
//            new AppBundle\AppBundle(),
        ];

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
//            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
//            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
//            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
//            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

     /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/cache/'.$this->environment;
    }

   
}
