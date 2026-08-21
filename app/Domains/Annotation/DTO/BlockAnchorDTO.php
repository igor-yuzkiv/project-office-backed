<?php

namespace App\Domains\Annotation\DTO;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Where an annotation points in the rendered document.
 *
 * The domain does not interpret these fields — resolving an anchor back to a block happens in
 * the browser, against the freshly rendered page. This type exists so the shape is named once
 * instead of being an untyped array travelling through the commands.
 *
 * @implements Arrayable<string, mixed>
 */
readonly class BlockAnchorDTO implements Arrayable
{
    public const int VERSION = 1;

    public function __construct(
        public ?int $line,
        public string $tag,
        public int $ordinal,
        public int $index,
        public string $textHash,
        public int $version = self::VERSION,
    ) {}

    /**
     * @param  array<string, mixed>  $anchor
     */
    public static function fromArray(array $anchor): self
    {
        return new self(
            line: isset($anchor['line']) ? (int) $anchor['line'] : null,
            tag: (string) $anchor['tag'],
            ordinal: (int) $anchor['ordinal'],
            index: (int) $anchor['index'],
            textHash: (string) $anchor['text_hash'],
            version: (int) ($anchor['version'] ?? self::VERSION),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'version'   => $this->version,
            'line'      => $this->line,
            'tag'       => $this->tag,
            'ordinal'   => $this->ordinal,
            'index'     => $this->index,
            'text_hash' => $this->textHash,
        ];
    }
}
