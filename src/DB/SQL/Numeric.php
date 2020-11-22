<?php

namespace Helix\DB\SQL;

/**
 * Represents a numeric expression. Produces various transformations.
 */
class Numeric extends Value {

    use NumericTrait;

    /**
     * Casts the expression to a character string.
     *
     * @return Text
     */
    public function toText () {
        if ($this->db == 'sqlite') {
            return $this->db->factory(Text::class, $this->db, "CAST({$this} AS TEXT)");
        }
        return $this->db->factory(Text::class, $this->db, "CAST({$this} AS CHAR)");
    }
}