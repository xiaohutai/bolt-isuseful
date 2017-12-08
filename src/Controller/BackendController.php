<?php

namespace Bolt\Extension\TwoKings\IsUseful\Controller;

use Bolt\Controller\Base;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 *
 * @author Xiao-Hu Tai <xiao@twokings.nl>
 */
class BackendController extends Base
{
    /**
     * {@inheritdoc}
     */
    public function addRoutes(ControllerCollection $ctr)
    {
        $ctr
            ->get('/', [$this, 'indexGet'])
            ->before([$this, 'before'])
            ->bind('is_useful.index')
        ;

        $ctr
            ->get('/{id}', [$this, 'view'])
            ->assert('id', '\d+')
            ->before([$this, 'before'])
            ->bind('is_useful.view')
        ;

        return $ctr;
    }

    /**
     * Check if the current user is logged in.
     *
     * @param Request     $request
     * @param Application $app
     */
    public function before(Request $request, Application $app)
    {
        $token = $app['session']->get('authentication', false);

        if (! $token) {
            return $this->redirectToRoute('dashboard');
        }
    }

    /**
     *
     *
     * @param Application $app
     * @param Request     $request
     */
    public function indexGet(Application $app, Request $request)
    {
        $stmt = $app['db']->prepare("SELECT * FROM `bolt_is_useful`");
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $this->render('@is_useful/backend/index.twig', [
            'title' => 'Feedback',
            'data'  => $data,
        ], []);
    }

    /**
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     */
    public function view(Application $app, Request $request, $id)
    {
        $stmt = $app['db']->prepare("SELECT * FROM `bolt_is_useful` WHERE `id` = :id");
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $data = $stmt->fetchAll();

        // check iff empty

        $stmt = $app['db']->prepare("SELECT * FROM `bolt_is_useful_feedback` WHERE `is_useful_id` = :id");
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $feedback = $stmt->fetchAll();

        return $this->render('@is_useful/backend/feedback.twig', [
            'title'    => 'Feedback » № ' . $id,
            'data'     => $data,
            'feedback' => $feedback,
        ], []);
    }
}
