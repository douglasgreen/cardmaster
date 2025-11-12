<?php

namespace CardMaster;

class Input
{
    public static function getChar(): ?string
    {
        $read = [STDIN];
        $write = null;
        $except = null;
        $count = stream_select($read, $write, $except, null);
        if ($count && in_array(STDIN, $read, true)) {
            $char = stream_get_contents(STDIN, 1);
            return $char;
        }
        return null;
    }
}
