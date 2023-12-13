<?php

namespace App\Imports;

use App\Models\aps;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class apsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    private $rows = 0;

    public function model(array $row)
    {
        ++$this->rows;

        return new aps([
            'rut' => $row['rut']
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
