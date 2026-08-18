<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class MaterialRowsImport implements ToArray
{
    public function array(array $array): void
    {
        // El controlador consume el arreglo retornado por Excel::toArray().
    }
}
