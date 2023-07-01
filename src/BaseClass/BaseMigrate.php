<?php

namespace Kaadon\ThinkBase\BaseClass;

use Exception;
use think\migration\db\Column;
use think\migration\Migrator;

class BaseMigrate extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public ?string $table = null;

    /**
     * @var string|null
     */
    public ?string $tableName = null;


    /**
     * @var array|string[]
     */
    public array $type = [
        "boolean"         => "boolean", //
        "tinyInteger"     => "tinyInteger", //
        "smallInteger"    => "smallInteger", //
        "mediumInteger"   => "mediumInteger", //
        "bigInteger"      => "bigInteger", //
        "unsignedInteger" => "unsignedInteger", //
        "decimal"         => "decimal", //
        "binary"          => "binary", //
        "char"            => "char", //
        "date"            => "date", //
        "dateTime"        => "dateTime", //
        "enum"            => "enum", //
        "float"           => "float", //
        "integer"         => "integer", //
        "json"            => "json", //
        "jsonb"           => "jsonb", //
        "longText"        => "longText", //
        "mediumText"      => "mediumText", //
        "string"          => "string", //
        "text"            => "text", //
        "time"            => "time", //
        "timestamp"       => "timestamp", //
        "uuid"            => "uuid", //
    ];


    /**
     * 默认的时间戳
     * @var array|array[]
     */
    public array $defaultTime = [
        "create_time" => [
            "column"  => "integer",
            "limit"   => 11,
            "index"   => true,
            "comment" => "创建时间",
            "default" => 0,
            "isnull" => true,
        ],
        "update_time" => [
            "column"  => "integer",
            "limit"   => 11,
            "comment" => "更新时间",
            "default" => 0,
            "isnull" => true,
        ],
        "delete_time" => [
            "column"  => "integer",
            "limit"   => 11,
            "comment" => "删除时间",
            "default" => 0,
            "isnull" => true,
        ]
    ];


    /**
     * 字段
     * @return array
     *
     *  "create_time" => [
     *                    "column"  => "integer",
     *                    "limit"   => 11,
     *                    "index"   => true,
     *                    "comment" => "创建时间"
     *                    "default" => 0,
     *                    "isnull" => true,
     *                  ]
    ],
     */
    public function schema(): array
    {
        return [];
    }


    /**
     * @return void
     * @throws Exception
     */
    public function change(): void
    {
        $schemas = $this->schema();
        if (empty($schemas)) {
            var_dump("字段为空!");
            return;
        }
        $schemas = array_merge($schemas, $this->defaultTime);
        $Columns = [];
        $Index   = [];
        $unique  = null;
        foreach ($schemas as $key => $schema) {
            $schemaData = null;
            if (array_key_exists('column', $schema) && method_exists(Column::class, $schema['column'])) {
                $method = $schema['column'];
                if ($method == "decimal") {
                    $schemaData = Column::$method($key, $schema['limit'] ?? 30, $schema['limitFloat'] ?? 12);
                } else {
                    $schemaData = Column::$method($key);
                }
            } else {
                continue;
            }
            if (array_key_exists('limit', $schema)
                &&
                $schema['limit'] > 0
                &&
                $schema['column'] !== "decimal"
            ) {
                $schemaData = $schemaData->setLimit($schema['limit']);
            }
            if (array_key_exists('isnull', $schema) && $schema['isnull']) {
                $schemaData = $schemaData->setNull($schema['isnull']);
            }
            if (array_key_exists('default', $schema)) {
                $schemaData = $schemaData->setDefault($schema['default']);
            }
            if (array_key_exists('unique', $schema) && $schema['unique']) {
                $schemaData = $schemaData->setUnique();
            }
            if (array_key_exists('signed', $schema) && $schema['signed']) {
                $schemaData = $schemaData->setSigned();
            }
            if (array_key_exists('comment', $schema) && $schema['comment'] > 0) {
                $schemaData = $schemaData->setComment($schema['comment']);
            }

            if (array_key_exists('index', $schema) && $schema['index']) {
                $Index[] = $key;
            }

            if (array_key_exists('uniqueIndex', $schema) && $schema['uniqueIndex']) {
                if (empty($unique)) {
                    $unique = $key;
                } else {
                    throw new \think\Exception("太多UNIQUE索引");
                }
            }
            $Columns[] = $schemaData;
        }
        if (count($Columns) == 0) {
            return;
        }
        $create = $this->table($this->table);
        foreach ($Columns as $Column) {
            $create = $create->addColumn($Column);
        }
        if (count($Index) > 0) $create = $create->addIndex($Index);
        if (!is_null($unique)) {
            $create = $create->addIndex('unique', ["unique" => true]);
        } else {
            $create = $create->addIndex('id', ["unique" => true]);
        }
        if (!empty($this->tableName)) $create = $create->setComment($this->tableName);
        $create->create();
    }

}