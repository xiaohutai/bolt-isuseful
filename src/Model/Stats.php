<?php

namespace Bolt\Extension\TwoKings\IsUseful\Model;

use Bolt\Storage\Database\Connection;

/**
 *
 * @author Xiao-Hu Tai <xiao@twokings.nl>
 */
class Stats {

    protected $contenttype;
    protected $contentid;
    protected $data;

    private $tableName = 'bolt_is_useful';

    /* @var Connection $db */
    private $db;

    /**
     * Constructs a new Stats model.
     */
    public function __construct(
        Connection $db,
        $contenttype,
        $contentid
    ) {
        $this->db          = $db;
        $this->contenttype = $contenttype;
        $this->contentid   = $contentid;
        $this->fetch();
    }

    /**
     * Hydrates the fields from the database if it exists, otherwise intializes.
     */
    private function fetch()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tableName} WHERE contenttype = :ct AND contentid = :id LIMIT 1;");
        $stmt->bindValue('ct', $this->contenttype);
        $stmt->bindValue('id', $this->contentid);
        $stmt->execute();
        $this->data = $stmt->fetch();

        if (!$this->data) {
            $this->data = [
               'id'          => null,
               'contenttype' => $this->contenttype,
               'contentid'   => $this->contentid,
               'totals'      => [],
               'ips'         => [],
            ];
        } else {
            $this->data['totals'] = json_decode($this->data['totals'], true);
            $this->data['ips']    = json_decode($this->data['ips'], true);
        }
    }

    /**
     * Stores the last vote for a given IP address.
     *
     * Note: This logic is a bit different compared to something that would be
     *       in a "likes"/"upvote" extension.
     *
     * @param string $ip   An IP-address
     * @param string $type Either `yes` or `no`.
     */
    public function set($ip, $type)
    {
        if (!isset($this->data['totals'][$type])) {
            $this->data['totals'][$type] = 0;
        }

        // Reset the previous vote if it exists.
        if (isset($this->data['ips'][$ip])) {
            $oldType = $this->data['ips'][$ip];
            $this->data['totals'][$oldType]--;
        }

        // Set the new vote.
        $this->data['ips'][$ip] = $type;
        $this->data['totals'][$type]++;

        $this->persist();
    }

    /**
     * Persists this object in the database.
     */
    private function persist()
    {
        $data = $this->data;
        $data['totals'] = json_encode($data['totals']);
        $data['ips'] = json_encode($data['ips']);

        if ($this->data['id'] === null) {
            $this->db->insert($this->tableName, $data);
        } else {
            $this->db->update($this->tableName, $data, ['id' => $data['id']]);
        }
    }
}
