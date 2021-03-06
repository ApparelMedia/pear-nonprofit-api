<?php namespace App\Processors;

class FileDatabaseProcessor
{
    protected $offset;
    protected $limit;
    protected $pdo;
    protected $path;

    function __construct($path, \PDO $pdo) {
        $this->path = $path;
        $this->pdo = $pdo;
    }

    protected function getPdo() {
        return $this->pdo;
    }

    public function execute(\Closure $afterEach = null) {
        $pdo = $this->getPdo();
        $file = fopen($this->path, 'r');
        $i = 0;
        while ( !feof($file) ) {
            $i++;
            $row = fgetcsv($file, 2000, '|');

            if (is_null($row[0])) continue;

            $rowValues = array_map(function ($value) use ($pdo) {
                return $pdo->quote($value);
            }, $row);

            $processedSql = 'INSERT INTO nonprofits_staging (ein, name, city, state, country, deductibility_status_code) VALUES ('
                . $rowValues[0] . ','
                . $rowValues[1] . ','
                . $rowValues[2] . ','
                . $rowValues[3] . ','
                . $rowValues[4] . ','
                . $rowValues[5] . ')';

            $pdo->exec($processedSql);

            if (is_callable($afterEach)) {
                $afterEach($i);
            }
        }
        return true;
    }
}
