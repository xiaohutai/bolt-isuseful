<?php

namespace Bolt\Extension\TwoKings\SocialHub;

use Bolt\Storage\Database\Schema\Table\BaseTable;

class IsUsefulTable extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id', 'integer', ['autoincrement' => true]);
        $this->table->addColumn('contenttype', 'string', ['notnull' => false]);
        $this->table->addColumn('contentid', 'integer', ['notnull' => false]);
        $this->table->addColumn('totals', 'json_array');
        $this->table->addColumn('ips', 'json_array');
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        // This will create a joint index of both columns
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
