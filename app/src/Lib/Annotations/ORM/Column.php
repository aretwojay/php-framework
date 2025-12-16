<?php

namespace App\Lib\Annotations\ORM;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column {
    public function __construct(
        public string $type,
        public bool $nullable = false,
        public int|null $size = null,
        public string|null $name = null
    ){}
}

?>
