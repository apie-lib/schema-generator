<?php
namespace Apie\SchemaGenerator\Enums;

enum SchemaUsages: string
{
    case CREATE = 'post';
    case MODIFY = 'patch';
    case GET = 'get';
}
