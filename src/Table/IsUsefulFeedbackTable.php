<?php

namespace Bolt\Extension\TwoKings\IsUseful\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

class IsUsefulFeedbackTable extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id'           , 'integer' , ['autoincrement' => true]);
        $this->table->addColumn('contenttype'  , 'string'  , ['notnull' => false]);
        $this->table->addColumn('contentid'    , 'integer' , ['notnull' => false]);
        $this->table->addColumn('is_useful_id' , 'integer' , ['notnull' => false]);
        $this->table->addColumn('ip'           , 'string'  , ['notnull' => false]);
        $this->table->addColumn('message'      , 'text'    , ['notnull' => false]);
        $this->table->addColumn('url'          , 'string'  , ['notnull' => false]);
        $this->table->addColumn('datetime'     , 'datetime', ['notnull' => false]);
        $this->table->addColumn('read'         , 'integer' , ['notnull' => false, 'default' => 0, ]);
        $this->table->addColumn('hide'         , 'integer' , ['notnull' => false, 'default' => 0, ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['contenttype', 'contentid']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }
}
