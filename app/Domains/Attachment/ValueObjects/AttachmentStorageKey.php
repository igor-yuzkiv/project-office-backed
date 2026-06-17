<?php

namespace App\Domains\Attachment\ValueObjects;

class AttachmentStorageKey
{
    public function __construct(
        public string $id,
        public string $extension
    ) {}

    public static function make(string $id, string $extension): self
    {
        return new self($id, $extension);
    }

    public function value(): string
    {
        return sprintf('attachments/%s.%s', $this->id, $this->extension);
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
