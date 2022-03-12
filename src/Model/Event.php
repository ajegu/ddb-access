<?php

namespace Ajegu\DdbAccess\Model;

enum Event: string
{
    case BEFORE_SAVE = 'before.save';
    case AFTER_QUERY = 'after.query';
}