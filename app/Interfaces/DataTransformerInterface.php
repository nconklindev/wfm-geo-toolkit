<?php

namespace App\Interfaces;

interface DataTransformerInterface
{
    public function transformForView(array $data): array;

    /**
     * Transform data for CSV export
     *
     * This method should transform the raw data into a format suitable for CSV export.
     * It may be the same as transformForView() or require additional flattening.
     *
     * @param  array  $data  The raw data array to transform
     * @return array The transformed data array
     */
    public function transformForCsv(array $data): array;
}
