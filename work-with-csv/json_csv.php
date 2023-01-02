<?php

class JsonToCsv
{
  public static function jsonToCsv($json, $delimetr = ',')
  {
    $decodedJson = json_decode($json, true);
    $csv = "";
    $columns = self::getJsonAttributesAsCsvColumns($decodedJson);
    $csv = $csv . implode($delimetr, $columns);
    if (self::isAssociative($decodedJson)) {
      $singleRowValue = self::getAsSingleRow($columns, $decodedJson);
      return $csv . "\n" . implode($delimetr, $singleRowValue);
    }
    foreach ($decodedJson as $decodedJsonItem) {
      $singleRowValue = self::getAsSingleRow($columns, $decodedJsonItem);
      $csv = $csv . "\n" . implode($delimetr, $singleRowValue);
    }
    return $csv;
  }

  public static function jsonToCsvFile($json, $csvFilePath, $delimetr = ',')
  {
    $csvFile = fopen($csvFilePath, 'w');
    $decodedJson = json_decode($json, true);
    $columns = self::getJsonAttributesAsCsvColumns($decodedJson);
    fwrite($csvFile, implode($delimetr, $columns));
    if (self::isAssociative($decodedJson)) {
      $singleRowValue = self::getAsSingleRow($columns, $decodedJson);
      fwrite($csvFile, "\n" . implode($delimetr, $singleRowValue));
      return;
    }
    foreach ($decodedJson as $decodedJsonItem) {
      $singleRowValue = self::getAsSingleRow($columns, $decodedJsonItem);
      fwrite($csvFile, "\n" . implode($delimetr, $singleRowValue));
    }
    fclose($csvFile);
  }

  public static function jsonFileToCsvFile($jsonFilePath, $csvFilePath, $delimetr = ',')
  {
    $jsonFile = fopen($jsonFilePath, 'r');
    $csvFile = fopen($csvFilePath, 'w');
    $decodedJson = json_decode(fread($jsonFile, filesize($jsonFilePath)), true);
    $columns = self::getJsonAttributesAsCsvColumns($decodedJson);
    fwrite($csvFile, implode($delimetr, $columns));
    if (self::isAssociative($decodedJson)) {
      $singleRowValue = self::getAsSingleRow($columns, $decodedJson);
      fwrite($csvFile, "\n" . implode($delimetr, $singleRowValue));
      return;
    }
    foreach ($decodedJson as $decodedJsonItem) {
      $singleRowValue = self::getAsSingleRow($columns, $decodedJsonItem);
      fwrite($csvFile, "\n" . implode($delimetr, $singleRowValue));
    }
    fclose($csvFile);
    fclose($jsonFile);
  }

  private static function getAsSingleRow($columns, $decodedJsonItem)
  {
    $singleRowValue = [];
    foreach ($columns as $column) {
      $splitedField = explode('/', $column);
      $singleRowValueItem = $decodedJsonItem[$splitedField[0]];
      for ($i = 1; $i < count($splitedField); $i++) {
        $singleRowValueItem = $singleRowValueItem[$splitedField[$i]];
      }
      $singleRowValue[] = $singleRowValueItem;
    }
    return $singleRowValue;
  }

  private static function getJsonAttributesAsCsvColumns($decodedJson)
  {
    $singleItemOfDecodedJson = self::isAssociative($decodedJson) ? $decodedJson : $decodedJson[0];
    $columns = [];
    foreach ($singleItemOfDecodedJson as $key => $value) {
      foreach (self::checkColumn($key, $value) as $columnItem) {
        $columns[] = $columnItem;
      }
    };
    return $columns;
  }

  private static function checkColumn($key, $value)
  {
    $columns = [];
    if (is_array($value)) {
      foreach ($value as $k => $v) {
        foreach (self::checkColumn($k, $v) as $secondaryColumn) {
          $columns[] = $key . '/' . $secondaryColumn;
        }
      }
    } else {
      $columns[] = $key;
    }
    return $columns;
  }

  private static function isAssociative($decodedJson)
  {
    foreach ($decodedJson as $key => $value) {
      if (!is_numeric($key)) {
        return true;
      }
    }
    return false;
  }
}