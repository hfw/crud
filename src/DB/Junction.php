<?php

namespace Helix\DB;

use Helix\DB;
use LogicException;
use ReflectionClass;
use ReflectionException;

/**
 * Represents a junction table, derived from an annotated interface.
 *
 * Interface Annotations:
 *
 * - `@junction TABLE`
 * - `@foreign COLUMN CLASS` or `@for COLUMN CLASS`
 */
class Junction extends Table {

    /**
     * `[column => class]`
     *
     * @var string[]
     */
    protected $classes = [];

    /**
     * @param DB $db
     * @param string $interface
     * @return Junction
     */
    public static function fromInterface (DB $db, string $interface) {
        try {
            $ref = new ReflectionClass($interface);
        }
        catch (ReflectionException $exception) {
            throw new LogicException('Unexpected ReflectionException', 0, $exception);
        }
        $doc = $ref->getDocComment();
        $classes = [];
        foreach (explode("\n", $doc) as $line) {
            if (preg_match('/@for(eign)?\s+(?<column>\S+)\s+(?<class>\S+)/', $line, $foreign)) {
                $classes[$foreign['column']] = $foreign['class'];
            }
        }
        preg_match('/@junction\s+(?<table>\S+)/', $doc, $junction);
        return new static($db, $junction['table'], $classes);
    }

    /**
     * @param DB $db
     * @param string $table
     * @param string[] $classes
     */
    public function __construct (DB $db, string $table, array $classes) {
        parent::__construct($db, $table, array_keys($classes));
        $this->classes = $classes;
    }

    /**
     * Returns a {@link Select} for entities referenced by a foreign key.
     *
     * The {@link Select} is literal and can be iterated directly.
     *
     * @param string $key The column referencing the class to collect.
     * @param array $match Keyed by junction column.
     * @return Select
     */
    public function getCollection (string $key, array $match = []) {
        $record = $this->db->getRecord($this->classes[$key]);
        $select = $record->select();
        $select->join($this, $this[$key]->isEqual($record['id']));
        foreach ($match as $a => $b) {
            $select->where($this->db->match($this[$a], $b));
        }
        return $select;
    }

    /**
     * `INSERT IGNORE` to link entities.
     *
     * @param int[] $ids Keyed by column.
     * @return int Rows affected.
     */
    public function link (array $ids): int {
        $link = $this->cache(__FUNCTION__, function() {
            $columns = implode(',', array_keys($this->columns));
            $slots = implode(',', SQL::slots(array_keys($this->columns)));
            switch ($this->db) {
                case 'sqlite':
                    return $this->db->prepare(
                        "INSERT OR IGNORE INTO {$this} ({$columns}) VALUES ({$slots})"
                    );
                default:
                    return $this->db->prepare(
                        "INSERT IGNORE INTO {$this} ({$columns}) VALUES ({$slots})"
                    );
            }
        });
        return $link($ids)->rowCount();
    }

    /**
     * Alias for {@link delete()}
     *
     * @param array $ids Keyed by Column
     * @return int Rows affected
     */
    public function unlink (array $ids): int {
        return $this->delete($ids);
    }
}