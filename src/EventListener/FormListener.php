<?php

namespace Bolt\Extension\TwoKings\IsUseful\EventListener;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\ProcessorEvent;
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
    private function onFeedback(ProcessorEvent $event) {
        /** @var Entity $data */
        $data = $event->getData();

        $url = $data->get('url');

        $stats = new Stats(
            $this->app['db'],
            $data->get('contenttype'),
            $data->get('contentid')
        );
        $stats->set(
            $this->app['request']->getClientIp(),
            'no' // $type
        );

        // Note: This would only store 'no' values, because 'yes' doesn't submit
        //       a form.
    }
}
