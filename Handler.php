<?php

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Class Handler.
 */
class Handler
{
    /**
     * @var PDO
     */
    private $conn;

    /**
     * @var CamelCaseToSnakeCaseNameConverter
     */
    private $nameConverter;

    /** @var array */
    private $parameters;

    /**
     * Handler constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;

        /** @var string $dsn */
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s',
            $this->parameters['database_host'],
            $this->parameters['database_name']
        );

        $this->conn = new PDO($dsn, $this->parameters['database_user'], $this->parameters['database_password']);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();
    }

    /**
     * @return PDO
     */
    public function getConn(): PDO
    {
        return $this->conn;
    }

    /**
     * @param PDO $conn
     */
    public function setConn(PDO $conn): void
    {
        $this->conn = $conn;
    }

    /**
     * @return CamelCaseToSnakeCaseNameConverter
     */
    public function getNameConverter(): CamelCaseToSnakeCaseNameConverter
    {
        return $this->nameConverter;
    }

    /**
     * @param CamelCaseToSnakeCaseNameConverter $nameConverter
     */
    public function setNameConverter(CamelCaseToSnakeCaseNameConverter $nameConverter): void
    {
        $this->nameConverter = $nameConverter;
    }

    /**
     * @param string $tableName
     *
     * @return mixed
     */
    public function tableExists(string $tableName)
    {
        /** @var PDOStatement $stmt */
        $stmt = $this->conn
            ->prepare(
                Constants::getCheckTableExistsStatementString(Constants::SCHEMA_NAME, $tableName)
            )
        ;

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * @param string $tableName
     * @param array  $columnNames
     *
     * @return bool
     */
    public function createTable(string $tableName, array $columnNames): bool
    {
        /** @var array $definitions */
        $definitions = [];

        /** @var string $columnName */
        foreach ($columnNames as $columnName) {
            /** @var string $columnName */
            $columnName = str_replace('-', '_', $columnName);
            $columnName = $this->nameConverter->normalize($columnName);

            $columnType = Constants::DEFAULT_COLUMN_TYPE;

            if (preg_match('/time/ims', $columnName)) {
                $columnType = Constants::TIMESTAMP_COLUMN_TYPE;
            }

            $definitions[] = sprintf(
                Constants::getColumnDefinitionString($columnName, $columnType)
            );
        }

        /** @var string $createString */
        $createString = sprintf(
            Constants::getCreateStatementString($tableName, $definitions)
        );

        $stmt = $this->conn->prepare($createString);

        return $stmt->execute();
    }

    /**
     * @param string $tableName
     * @param array  $columnNames
     *
     * @return bool
     */
    public function createTableIfNotExists(string $tableName, array $columnNames): bool
    {
        if (false === $this->tableExists($tableName)) {
            return $this->createTable($tableName, $columnNames);
        }

        return false;
    }

    /**
     * @param string $tableName
     * @param array  $recordData
     *
     * @return bool
     */
    public function insertRecord(string $tableName, array $recordData): bool
    {
        /** @var array $standardData */
        $standardData = $recordData;

        array_walk($standardData, function (&$item, $key) {
            $item = empty($item) ? 0 : trim($item);
        });

        /** @var string $checkSum */
        $checkSum = md5(implode(',', $standardData));

        array_unshift($recordData, $checkSum);

        array_walk($recordData, function (&$item, $key) {
            $item = empty($item) ? 'NULL' : $this->conn->quote(trim($item));
        });

        /** @var bool $recordExists */
        $recordExists = $this->recordExists($tableName, $checkSum);

        if (false === $recordExists) {
            /** @var PDOStatement $stmt */
            $stmt = $this->conn->prepare(Constants::getInsertStatementString($tableName, $recordData));

            return $stmt->execute();
        }

        return false;
    }

    /**
     * @param string $tableName
     * @param string $checksum
     *
     * @return bool
     */
    public function recordExists(string $tableName, string $checksum): bool
    {
        $stmt = $this->conn->prepare(Constants::getCheckRecordStatementString($tableName, $checksum));
        $stmt->execute();

        return count($stmt->fetchAll()) > 0;
    }
}
