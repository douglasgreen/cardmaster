<?php

namespace CardMaster;

class Input
{
    public static function getChar(): ?string
    {
        $read = array(STDIN);
        $write = null;
        $except = null;
        $count = stream_select($read, $write, $except, null);
        if ($count && in_array(STDIN, $read)) {
            $char = stream_get_contents(STDIN, 1);
            return $char;
        }
        return null;
    }
}
