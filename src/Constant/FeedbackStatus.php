<?php

namespace Bolt\Extension\TwoKings\IsUseful\Constant;

/**
 *
 */
class FeedbackStatus {

    const UNREAD  = 'new'; // because `NEW` is not allowed
    const READ    = 'read';
    const DONE    = 'done';
    const REMOVED = 'removed'; // hidden for normal list

    /**
     * Returns a list of constants used in this class.
     */
    public static function getConstants()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }

}
