<?php

/**
 * Class Constants.
 */
final class Constants
{
    const SCHEMA_NAME = 'pmta';
    const DEFAULT_COLUMN_TYPE = 'VARCHAR(255)';
    const TIMESTAMP_COLUMN_TYPE = 'TIMESTAMP';
    const ADD_INDEX_STATEMENT_FORMAT = 'ADD INDEX `%s` (`%s`);';

    /**
     * @return string
     */
    public static function getDefaultColumnTypeStatement(): string
    {
        return self::DEFAULT_COLUMN_TYPE;
    }

    /**
     * @return string
     */
    public static function getTimestampColumnTypeStatement(): string
    {
        return self::TIMESTAMP_COLUMN_TYPE;
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    public static function getAddIndexStatementString(string $columnName): string
    {
        $indexName = sprintf('IDX_%s', $columnName);

        return sprintf(self::ADD_INDEX_STATEMENT_FORMAT, $indexName, $columnName);
    }

    /**
     * @param string $schemaName
     * @param string $tableName
     *
     * @return string
     */
    public static function getCheckTableExistsStatementString(string $schemaName, string $tableName)
    {
        return sprintf(
            "SELECT TABLE_NAME FROM information_schema.`TABLES` T WHERE T.TABLE_SCHEMA = '%s' AND T.TABLE_NAME = '%s'",
            $schemaName,
            $tableName
        );
    }

    /**
     * @param string $columnName
     * @param string $columnType
     *
     * @return string
     */
    public static function getColumnDefinitionString(string $columnName, string $columnType = self::DEFAULT_COLUMN_TYPE): string
    {
        return sprintf(
            '`%s` %s DEFAULT NULL',
            $columnName,
            $columnType
        );
    }

    /**
     * @param string $tableName
     * @param array  $columnDefinitions
     *
     * @return string
     */
    public static function getCreateStatementString(string $tableName, array $columnDefinitions): string
    {
        return sprintf(
            "CREATE TABLE `%s` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `parse_code` TINYINT UNSIGNED NOT NULL DEFAULT 0,`checksum` VARCHAR (32) NULL DEFAULT NULL, %s, PRIMARY KEY (`id`)) COLLATE='utf8_general_ci'",
            $tableName,
            implode(',', $columnDefinitions)
        );
    }

    /**
     * @param string $tableName
     * @param array  $recordData
     *
     * @return string
     */
    public static function getInsertStatementString(string $tableName, array $recordData): string
    {
        /** @var string $values */
        $values = implode(',', $recordData);

        return sprintf(
            'INSERT INTO `%s` VALUES (NULL, 0, %s)',
            $tableName,
            $values
        );
    }

    /**
     * @param string $tableName
     * @param string $checksum
     *
     * @return string
     */
    public static function getCheckRecordStatementString(string $tableName, string $checksum): string
    {
        return sprintf("SELECT id FROM %s WHERE `checksum` = '%s'", $tableName, $checksum);
    }
}
