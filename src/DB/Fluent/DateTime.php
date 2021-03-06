<?php

namespace Helix\DB\Fluent;

use Helix\DB;
use Helix\DB\Fluent\DateTime\DateTimeTrait;
use Helix\DB\Fluent\Str\StrCastTrait;

/**
 * A date-time expression.
 */
class DateTime extends Expression implements ValueInterface
{

    use DateTimeTrait;
    use FactoryFormatTrait;
    use StrCastTrait;

    /**
     * An expression for the current date and time.
     *
     * @param DB $db
     * @return static
     */
    public static function now(DB $db)
    {
        return static::fromFormat($db, [
            'mysql' => "NOW()",
            'sqlite' => "DATETIME()"
        ]);
    }

    /**
     * An expression for the current date.
     *
     * @param DB $db
     * @return static
     */
    public static function today(DB $db)
    {
        return static::fromFormat($db, [
            'mysql' => "CURDATE()",
            'sqlite' => "DATE()"
        ]);
    }

    /**
     * An expression for tomorrow's date.
     *
     * @param DB $db
     * @return static
     */
    public static function tomorrow(DB $db)
    {
        return static::today($db)->addDay();
    }

    /**
     * An expression for yesterday's date.
     *
     * @param DB $db
     * @return static
     */
    public static function yesterday(DB $db)
    {
        return static::today($db)->subDay();
    }
}
