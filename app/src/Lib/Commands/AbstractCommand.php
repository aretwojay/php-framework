<?php

namespace App\Lib\Commands;

abstract class AbstractCommand
{
    abstract public function execute(): void;
    abstract public function undo(): void;
    abstract public function redo(): void;
}
