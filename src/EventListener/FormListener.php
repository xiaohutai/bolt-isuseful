<?php

namespace Bolt\Extension\TwoKings\IsUseful\EventListener;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\ProcessorEvent;
use Bolt\Extension\TwoKings\IsUseful\Model\Stats;
use Bolt\Storage\Entity\Entity;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Form listener.
 *
 * @author Xiao-Hu Tai <xiao@twokings.nl>
 */
class FormListener implements EventSubscriberInterface
{
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BoltFormsEvents::SUBMISSION_PRE_PROCESSOR => ['onFeedback']
        ];
    }

    /**
     * @param ProcessorEvent $event
     */
    public function onPreProcessor(ProcessorEvent $event)
    {
        /** @var array $mapping Mapping from form names defined in `boltforms.bolt.yml` to function names */
        $mapping = [
            'feedback' => 'onFeedback',
        ];

        if (isset( $mapping[$event->getFormName()] )) {
          $function = $mapping[$event->getFormName()];
          return call_user_func_array([ $this, $function], [ $event ]);
        }
    }

    /**
     * @param ProcessorEvent $event
     */
    public function onFeedback(ProcessorEvent $event) {
        /** @var Entity $data */
        $data = $event->getData();

        $config = $this->app['is_useful.config'];

        if ($config->get('statistics', false) !== false) {

            $stats = new Stats(
                $this->app['db'],
                $data->get('type'),
                $data->get('id')
            );

            $this->app['db']->insert('bolt_is_useful_feedback', [
                'contenttype'  => $data->get('type'),
                'contentid'    => $data->get('id'),
                'is_useful_id' => $stats->getId(),
                'ip'           => $this->app['request']->getClientIp(),
                'url'          => $data->get('url'),
                'message'      => $data->get('message'),
                'datetime'     => (new \DateTime('now'))->format('Y-m-d H:i:s'),
            ]);
        }

    }
}
