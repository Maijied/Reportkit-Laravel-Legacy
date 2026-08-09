<?php

namespace ReportKit\Laravel\Legacy\Source;

use ReportKit\Core\Contracts\RowSource;
use ReportKit\Core\Date\DateRangeChunker;

/**
 * RowSource backed by a Laravel DB connection + query callback (L4.1–5.4).
 */
class ConnectionRowSource implements RowSource
{
    /** @var string */
    private $connection;

    /** @var callable */
    private $callback;

    /** @var DateRangeChunker */
    private $chunker;

    /** @var string|null */
    private $label;

    public function __construct($connection, $callback, $chunker = null, $label = null)
    {
        $this->connection = (string) $connection;
        $this->callback = $callback;
        $this->chunker = $chunker instanceof DateRangeChunker ? $chunker : new DateRangeChunker();
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label !== null ? $this->label : $this->connection;
    }

    public function getWeeks(array $filters)
    {
        $start = isset($filters['start_date']) ? $filters['start_date'] : null;
        $end = isset($filters['end_date']) ? $filters['end_date'] : null;

        if (!$start || !$end) {
            return array();
        }

        return $this->chunker->getWeeklyRanges($start, $end);
    }

    public function getRows(array $filters)
    {
        if (class_exists('DB')) {
            $db = \DB::connection($this->connection);
        } elseif (function_exists('app')) {
            $db = app('db')->connection($this->connection);
        } else {
            return array();
        }

        $query = method_exists($db, 'table') ? $db->table(\DB::raw('(select 1) as rk_seed'))->select(\DB::raw('1')) : null;

        // Prefer query builder from connection
        if (method_exists($db, 'query')) {
            $query = $db->query();
        } elseif (method_exists($db, 'table')) {
            // Host callback should call from()/table() itself — pass connection helper
            $query = $db;
        }

        $result = call_user_func($this->callback, $query, $filters);

        if (is_array($result)) {
            return $result;
        }

        if (is_object($result) && method_exists($result, 'get')) {
            $rows = $result->get();
            $out = array();
            foreach ($rows as $row) {
                $out[] = (array) $row;
            }
            return $out;
        }

        return array();
    }

    public function getSummary(array $rows)
    {
        return array(
            'total_rows' => count($rows),
            'source' => $this->getLabel(),
        );
    }
}
