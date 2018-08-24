<?php
/**
 * Build action
 * User: moyo
 * Date: 25/12/2017
 * Time: 12:06 PM
 */

namespace Carno\Database\SQL;

interface Action
{
    public const INSERT = 0xC1;
    public const SELECT = 0xC3;
    public const UPDATE = 0xC5;
    public const DELETE = 0xC7;
}
