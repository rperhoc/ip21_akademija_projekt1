<?php

class View 
{
    public function starButton(bool $state) : string
    {
        if ($state === true) {
            return 'star-button-coloured.png';
        }
    return 'star-button-empty.png';
    }
}
