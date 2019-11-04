<?php

namespace App\Waypoint;

use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
Use Excel;
use App\Waypoint\Models\Spreadsheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

/**
 * Class Collection
 * @package App\Waypoint
 */
class SpreadsheetCollection extends Collection
{
    /**
     * @param $filename
     * @param bool $headingGeneration
     */
    public function createECMProjectSpreadsheet($filename, $headingGeneration = true)
    {
        /** @var LaravelExcelWriter $LaravelExcelWriterObj */
        $LaravelExcelWriterObj = Excel::create(
            $filename,
            function ($excel) use ($headingGeneration)
            {
                $excel->sheet(
                    'ECM Projects',
                    function (LaravelExcelWorksheet $sheet) use ($headingGeneration)
                    {
                        $sheet->setColumnFormat(['E' => '"$"#,##0.00']);
                        $sheet->setColumnFormat(['F' => '"$"#,##0.00']);
                        $sheet->setColumnFormat(['G' => '"$"#,##0.00']);
                        $sheet->fromArray($this->toArray(), null, 'A1', false, $headingGeneration);
                    }
                );
            }
        );
        $LaravelExcelWriterObj->download('xls');
    }

    /**
     * Get the collection of items as a plain array.
     *
     * NOTE - Only scalar values of $item->toArray() are inserted
     *
     * See http://www.maatwebsite.nl/laravel-excel/docs
     *
     * @param $filename
     * @param bool $headingGeneration
     */
    public function toCSVReport($filename, $headingGeneration = true)
    {
        (new Spreadsheet())->toCSVReport($filename, $headingGeneration);
    }

    /**
     * @param $filename
     * @param bool $headingGeneration
     *
     * NOTE NOTE NOTE
     * use this of the collection's (AKA $this) items are not models
     */
    public function toCSVReportGeneric($filename, $headingGeneration = true, $strictNullComparison = false)
    {
        /** @var LaravelExcelWriter $LaravelExcelWriterObj */
        $LaravelExcelWriterObj = Excel::create(
            $filename,
            function (LaravelExcelWriter $excel) use ($headingGeneration, $strictNullComparison)
            {
                $excel->sheet(
                    'CSVReport',
                    function (LaravelExcelWorksheet $sheet) use ($headingGeneration, $strictNullComparison)
                    {
                        /**
                         * @todo check method signature of $this->toArray(). Too many and out of order params
                         */
                        $sheet->fromArray($this->toArray(), null, 'A1', true, $headingGeneration);
                    }
                );
            }
        );
        $LaravelExcelWriterObj->download('csv');
    }

    /**
     * Get the collection of items as a plain array.
     *
     * NOTE - Only scalar values of $item->toArray() are inserted
     *
     * See http://www.maatwebsite.nl/laravel-excel/docs
     *
     * @param $filename
     * @param bool $headingGeneration
     */
    public function toCSVFile($filename, $headingGeneration = true)
    {
        /** @var LaravelExcelWriter $LaravelExcelWriterObj */
        $LaravelExcelWriterObj = Excel::create(
            $filename,
            function (LaravelExcelWriter $excel) use ($headingGeneration)
            {
                $excel->sheet(
                    'CSVReport',
                    function (LaravelExcelWorksheet $sheet) use ($headingGeneration)
                    {
                        $sheet->fromArray($this->toArray(), null, 'A1', false, $headingGeneration);
                    }
                );
            }
        );
        $LaravelExcelWriterObj->store('csv');
    }

    /**
     * Run a map over each of the items.
     *
     * @param callable $callback
     * @return \Illuminate\Support\Collection|static
     */
    public function map(callable $callback)
    {
        return collect_waypoint(parent::map($callback));
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param int $depth
     * @return \Illuminate\Support\Collection
     */
    public function flatten($depth = INF)
    {
        return collect_waypoint(parent::flatten($depth));
    }

    /**
     * Run a filter over each of the items.
     *
     * @param callable|null $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        return collect_waypoint(parent::filter($callback));
    }

    /**
     * Merge the collection with the given items.
     *
     * @param \ArrayAccess|array $items
     * @return static
     */
    public function merge($items)
    {
        return collect_waypoint(parent::merge($items));
    }

    /**
     * Return only unique items from the collection.
     *
     * @param string|callable|null $key
     * @param bool $strict
     * @return static|\Illuminate\Support\Collection
     */
    public function unique($key = null, $strict = false)
    {
        return collect_waypoint(parent::unique($key, $strict));
    }

    /**
     * Get an array with the values of a given key.
     *
     * @param string $value
     * @param string|null $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($value, $key = null)
    {
        return collect_waypoint(parent::pluck($value, $key));
    }

    /**
     * Get the keys of the collection items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function keys()
    {
        return collect_waypoint(parent::keys());
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * @param mixed ...$items
     * @return \Illuminate\Support\Collection
     */
    public function zip($items)
    {
        return collect_waypoint(parent::zip($items));
    }

    /**
     * Collapse the collection of items into a single array.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collapse()
    {
        return collect_waypoint(parent::collapse());
    }

    /**
     * Flip the items in the collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function flip()
    {
        return collect_waypoint(parent::flip());
    }
}
