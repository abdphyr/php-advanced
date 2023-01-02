<?php

class CsvToJson
{
  public static function csvToJson($csv, $delimetr = ',')
  {
    $columns = explode($delimetr, substr($csv, 0, strpos($csv, "\n")));
    $rows = explode("\n", substr($csv, strpos($csv, "\n") + 1));
    $csvJson = [];
    foreach ($rows as $row) {
      $csvJsonItem = self::csvRowToJsonItem(explode($delimetr, $row), $columns);
      $csvJson[] = $csvJsonItem;
    }
    return json_encode($csvJson);
  }

  public static function csvToJsonFile($csv, $jsonFilePath, $delimetr = ',')
  {
    $jsonFile = fopen($jsonFilePath, 'w');
    $columns = explode($delimetr, substr($csv, 0, strpos($csv, "\n")));
    $rows = explode("\n", substr($csv, strpos($csv, "\n") + 1));
    $csvJson = [];
    foreach ($rows as $row) {
      $csvJsonItem = self::csvRowToJsonItem(explode($delimetr, $row), $columns);
      $csvJson[] = $csvJsonItem;
    }
    fwrite($jsonFile, json_encode($csvJson));
    fclose($jsonFile);
  }

  public static function csvFileToJsonFile($csvFilePath, $jsonFilePath)
  {
    $csvFile = fopen($csvFilePath, 'r');
    $jsonFile = fopen($jsonFilePath, 'w');
    $csvJson = [];
    $columns = fgetcsv($csvFile);
    while (!feof($csvFile)) {
      $singleCsvRow = fgetcsv($csvFile);
      $csvJsonItem = self::csvRowToJsonItem($singleCsvRow, $columns);
      $csvJson[] = $csvJsonItem;
    }
    fwrite($jsonFile, json_encode($csvJson));
    fclose($csvFile);
    fclose($jsonFile);
  }

  private static function csvRowToJsonItem($singleCsvRow, $columns)
  {
    $csvJsonItem = [];
    foreach ($columns as $i => $column) {
      $csvJsonItem = self::setCsvJsonItem($csvJsonItem, $column, $singleCsvRow[$i]);
    }
    return $csvJsonItem;
  }

  private static function setCsvJsonItem($csvJsonItem, $column, $value)
  {
    $index = strpos($column, '/');
    $property = substr($column, 0, $index);
    $newColumn = substr($column, $index + 1);
    if (!$index) {
      $csvJsonItem[$column] = $value;
      return $csvJsonItem;
    }
    if (isset($csvJsonItem[$property])) {
      $csvJsonItem[$property] = self::setCsvJsonItem($csvJsonItem[$property], $newColumn, $value);
    }
    if (!isset($csvJsonItem[$property])) {
      $csvJsonItem[$property] = self::setCsvJsonItem([], $newColumn, $value);
    }
    return $csvJsonItem;
  }
}