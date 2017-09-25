<?php

namespace Bolt\Extension\TwoKings\IsUseful;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\SimpleExtension;
use Bolt\Extension\TwoKings\IsUseful\Config\Config;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * @author Xiao-Hu Tai <xiao@twokings.nl>
 */
class Extension extends SimpleExtension
{
    // use DatabaseSchemaTrait;

    /**
     * {@inheritdoc}
     */
    // protected function registerExtensionTables()
    // {
    //     return [
    //         'is_useful' => IsUsefulTable::class,
    //     ];
    // }

    protected function registerFrontendRoutes(ControllerCollection $collection)
    {
        // $collection->match('/async/is-useful', [$this, '']);
        // Update with a 'yes' or a 'no' and count them in the database
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [
            'templates' => [
                'position'  => 'prepend',
                'namespace' => 'is_useful'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        $assets = [];

        $config = $this->getConfig();

        // By default, add JavaScript and CSS files, unless it is explicityly
        // set to `false`.
        if (!isset($config['add_js']) || $config['add_js'] !== false) {
            $assets[] = JavaScript::create()
                ->setFileName('extensions/vendor/twokings/is-useful/extension.js')
                ->setLate(true)
                ->setZone(Zone::FRONTEND)
            ;
        }

        if (!isset($config['add_css']) || $config['add_css'] !== false) {
            $assets[] = StyleSheet::create()
                ->setFileName('extensions/vendor/twokings/is-useful/extension.css')
                ->setLate(true)
                ->setZone(Zone::FRONTEND)
            ;
        }

        return $assets;
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        // $this->extendDatabaseSchemaServices();

        $app['is_useful.config'] = $app->share(function () { return new Config($this->getConfig()); });
    }
}
