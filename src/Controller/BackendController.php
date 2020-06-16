<?php

namespace Bolt\Extension\TwoKings\IsUseful\Controller;

use Bolt\Controller\Base;
use Bolt\Extension\TwoKings\IsUseful\Constant\FeedbackStatus;
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
        // General

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

        $ctr
            ->get('/unread', [$this, 'unreadGet'])
            ->before([$this, 'before'])
            ->bind('is_useful.unread')
        ;

        // Feedback

        $ctr
            ->get('/delete/{id}', [$this, 'deleteFeedback'])
            ->assert('id', '\d+')
            ->before([$this, 'before'])
            ->bind('is_useful.feedback.delete')
        ;

        $ctr
            ->get('/status/{id}/{status}', [$this, 'setFeedbackStatus'])
            ->assert('id', '\d+')
            ->assert('status', 'new|read|done|removed')
            ->before([$this, 'before'])
            ->bind('is_useful.feedback.status')
        ;

        $ctr
            ->get('/clean', [$this, 'cleanGet'])
            ->before([$this, 'before'])
            ->bind('is_useful.feedback.clean')
        ;

        $ctr
            ->post('/clean', [$this, 'cleanPost'])
            ->before([$this, 'before'])
            ->bind('is_useful.feedback.clean.post')
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
     */
    private function getUnreadFeedback($db)
    {
        $status = FeedbackStatus::UNREAD;

        $stmt = $db->prepare("SELECT * FROM `bolt_is_useful_feedback` WHERE `status` = :status");
        $stmt->bindParam('status', $status);
        $stmt->execute();
        $feedback = $stmt->fetchAll();

        return $feedback;
    }

    /**
     *
     *
     * @param Application $app
     * @param Request     $request
     */
    public function indexGet(Application $app, Request $request)
    {
        $status = FeedbackStatus::UNREAD;

        $sql  = "SELECT `bolt_is_useful`.*,";
        $sql .= " COUNT(`bolt_is_useful_feedback`.`is_useful_id`) AS count,";
        $sql .= " SUM(CASE WHEN `bolt_is_useful_feedback`.`status` = :status THEN 1 ELSE 0 END) AS count_unread";
        $sql .= " FROM `bolt_is_useful`";
        $sql .= " LEFT JOIN `bolt_is_useful_feedback` ON `bolt_is_useful`.`id` = `bolt_is_useful_feedback`.`is_useful_id`";
        $sql .= " GROUP BY `bolt_is_useful`.`id`";
        $stmt = $app['db']->prepare($sql);
        $stmt->bindParam('status', $status);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $this->render('@is_useful/backend/index.twig', [
            'title'        => 'Feedback',
            'data'         => $data,
            'total_unread' => count($this->getUnreadFeedback($app['db'])),
        ], []);
    }

    /**
     *
     */
    public function unreadGet(Application $app, Request $request)
    {
        $status = FeedbackStatus::UNREAD;

        $sql  = "SELECT `bolt_is_useful_feedback`.*,";
        $sql .= " `bolt_is_useful`.`contenttype`,";
        $sql .= " `bolt_is_useful`.`contentid`";
        $sql .= " FROM `bolt_is_useful_feedback`";
        $sql .= " LEFT JOIN `bolt_is_useful` ON `bolt_is_useful_feedback`.`is_useful_id` = `bolt_is_useful`.`id`";
        $sql .= " WHERE `status` = :status";

        $stmt = $app['db']->prepare($sql);
        $stmt->bindParam('status', $status);
        $stmt->execute();
        $feedback = $stmt->fetchAll();

        return $this->render('@is_useful/backend/unread.twig', [
            'title'        => 'Unread Feedback',
            'feedback'     => $feedback,
            'total_unread' => count($feedback),
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

        $status = FeedbackStatus::REMOVED;

        $stmt = $app['db']->prepare("SELECT * FROM `bolt_is_useful_feedback` WHERE `is_useful_id` = :id AND `status` != :status");
        $stmt->bindParam('id', $id);
        $stmt->bindParam('status', $status);
        $stmt->execute();
        $feedback = $stmt->fetchAll();

        $title = '№ ' . $id;

        if (!empty($data) && isset($data[0]['contenttype']) && isset($data[0]['contentid'])) {
            $contenttype = $data[0]['contenttype'];
            $contentid   = $data[0]['contentid'];
            $record      = $app['storage']->getContent("$contenttype/$contentid");
            if (! empty($record)) {
                $title = $record->getTitle();
            }
        }

        return $this->render('@is_useful/backend/feedback.twig', [
            'title'        => 'Feedback » ' . $title,
            'data'         => $data,
            'feedback'     => $feedback,
            'total_unread' => count($this->getUnreadFeedback($app['db'])),
        ], []);
    }

    /**
     * Removes feedback by ID
     */
    public function deleteFeedback(Application $app, Request $request, $id)
    {
        $status = FeedbackStatus::REMOVED;

        // (1) Remove
        $stmt = $app['db']->prepare("UPDATE `bolt_is_useful_feedback` SET `status` = :status WHERE `id` = :id");
        $stmt->bindParam('status', $status);
        $stmt->bindParam('id', $id);
        $stmt->execute();

        /*
        // (2) Fetch
        $stmt = $app['db']->prepare("SELECT * FROM `bolt_is_useful_feedback` WHERE `id` = :id");
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $feedback = $stmt->fetch();

        // (3) Get the parent item
        $sql  = "SELECT `bolt_is_useful`.*";
        $sql .= " FROM `bolt_is_useful`";
        $sql .= " JOIN `bolt_is_useful_feedback` ON `bolt_is_useful`.`id` = `bolt_is_useful_feedback`.`is_useful_id`";
        $sql .= " WHERE `bolt_is_useful_feedback`.`id` = :id";

        $stmt = $app['db']->prepare($sql);
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $parent = $stmt->fetch();

        $totals = json_decode($parent['totals']);
        $ips = json_decode($parent['ips']);

        // Warning: this can make data inconsistent!
        if (isset($totals->no)) {
            $totals->no--;
            if ($totals->no < 0) {
                $totals->no = 0;
            }
        }
        unset($ips->{$feedback['ip']});

        $totals = json_encode($totals);
        $ips = json_encode($ips);

        // (4) Set parent item
        $sql  = "UPDATE `bolt_is_useful`";
        $sql .= " SET totals = :totals,";
        $sql .= " ips = :ips";
        $sql .= " WHERE `id` = :id";

        $stmt = $app['db']->prepare($sql);
        $stmt->bindParam('id', $parent['id']);
        $stmt->bindParam('totals', $totals);
        $stmt->bindParam('ips', $ips);
        $stmt->execute();
        //*/

        return $this->redirect( $request->headers->get('referer') );
    }

    /**
     * Set feedback status
     */
    public function setFeedbackStatus(Application $app, Request $request, $id, $status)
    {
        if (in_array($status, FeedbackStatus::getConstants())) {
            $stmt = $app['db']->prepare("UPDATE `bolt_is_useful_feedback` SET `status` = :status WHERE `id` = :id");
            $stmt->bindParam('id', $id);
            $stmt->bindParam('status', $status);
            $stmt->execute();
        } else {
            // todo: invalid status
        }

        return $this->redirect( $request->headers->get('referer') );
    }

    /**
     * Clean up feedback using simple rules
     */
    public function cleanGet(Application $app, Request $request)
    {
        $feedback = [];

        $originalValue = $request->query->get('filter', false);
        if (! empty($originalValue)) {
            $likeValue = '%' . $originalValue . '%';
            $status = FeedbackStatus::REMOVED;

            $stmt = $app['db']->prepare("SELECT * FROM `bolt_is_useful_feedback` WHERE `status` != :status AND ( `message` LIKE :msg OR `ip` = :ip)");
            $stmt->bindParam('status', $status);
            $stmt->bindParam('msg', $likeValue);
            $stmt->bindParam('ip', $originalValue);
            $stmt->execute();

            $feedback = $stmt->fetchAll();
        }

       return $this->render('@is_useful/backend/clean.twig', [
            'title'        => 'Feedback » Clean',
            //'data'         => $data,
            'feedback'     => $feedback,
            'total_unread' => count($this->getUnreadFeedback($app['db'])),
        ], []);
    }

    /**
     * Cleans up feedback. Two options:
     *     (A) as filtered by user
     *     (B) general clean up inconsistencies
     */
    public function cleanPost(Application $app, Request $request)
    {
        $status = FeedbackStatus::REMOVED;

        $originalValue = $request->request->get('filter', false);

        // (A) Remove feedback via a LIKE query.
        if (! empty($originalValue)) {
            $likeValue = '%' . $originalValue . '%';

            $stmt = $app['db']->prepare("UPDATE `bolt_is_useful_feedback` SET `status` = :status WHERE ( `message` LIKE :msg OR `ip` = :ip )");
            $stmt->bindParam('status', $status);
            $stmt->bindParam('msg', $likeValue);
            $stmt->bindParam('ip', $originalValue);
            $stmt->execute();

            $app['logger.system']->info("[IsUseful] Purging feedback with [$originalValue]", [ 'event' => 'extensions' ]);
        }
        // (B) Clean up
        else {
            $app['logger.system']->info("[IsUseful] Cleaning feedback", [ 'event' => 'extensions' ]);

            // A deep get doesn't work?
            $ipBanlist = $app['is_useful.config']->get('ipBanlist');
            if (empty($ipBanlist)) {
                $ipBanlist = [];
            }

            // (1) Remove where is_useful_id = NULL
            $stmt = $app['db']->prepare("UPDATE `bolt_is_useful_feedback` SET `status` = :status WHERE `is_useful_id` IS NULL");
            $stmt->bindParam('status', $status);
            $stmt->execute();

            // (2) Remove where message = NULL
            $stmt = $app['db']->prepare("UPDATE `bolt_is_useful_feedback` SET `status` = :status WHERE `message` IS NULL");
            $stmt->bindParam('status', $status);
            $stmt->execute();

            // (3) Remove where ip is in ipBanlist (add it to `ipBanlist` in `isuseful.twokings_local.yml`)
            $sql = "UPDATE `bolt_is_useful_feedback` SET `status` = :status WHERE `ip` IN (:ip)";
            $values = [
                'status' => $status,
                'ip' => $ipBanlist,
            ];
            $types = [
                'status' => \PDO::PARAM_STR,
                'ip' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            ];
            $stmt = $app['db']->executeQuery($sql, $values, $types);
            $stmt->execute();
        }

        return $this->redirect( $app['url_generator']->generate('is_useful.feedback.clean') );
    }
}
