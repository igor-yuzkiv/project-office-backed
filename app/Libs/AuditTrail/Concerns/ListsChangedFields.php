<?php

namespace App\Libs\AuditTrail\Concerns;

/**
 * The shared half of every `*.updated` record: pick the columns worth reporting out of
 * getChanges(), and turn their names into one sentence. Each record keeps only its own
 * FIELD_NAMES map.
 */
trait ListsChangedFields
{
    /**
     * @param  string[]  $changedColumns  keys of Model::getChanges() taken right after update()
     * @return string[] the columns this event actually reports on
     */
    public static function reportableColumns(array $changedColumns): array
    {
        return array_values(array_intersect($changedColumns, array_keys(static::FIELD_NAMES)));
    }

    /**
     * @param  string[]  $changedColumns
     */
    protected function changedFieldsSentence(array $changedColumns): ?string
    {
        $names = array_map(fn (string $column) => static::FIELD_NAMES[$column], $changedColumns);

        if ($names === []) {
            return null;
        }

        $last = array_pop($names);
        $list = $names === [] ? $last : implode(', ', $names).' and '.$last;

        return "Changed {$list}";
    }
}
