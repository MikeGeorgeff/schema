<?php

namespace Georgeff\Schema;

enum ColumnType
{
    case BoolType;
    case TinyInt;
    case SmallInt;
    case RegInt;
    case BigInt;
    case Real;
    case Decimal;
    case Char;
    case Varchar;
    case Text;
    case Json;
    case Uuid;
    case Date;
    case Time;
    case Timestamp;
}
