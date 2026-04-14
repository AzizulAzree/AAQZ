<?php

namespace App\Support\DatabaseInspector;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DatabaseInspector
{
    public function __construct(
        private readonly int $previewPerPage = 10,
    ) {
    }

    public function overview(): array
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => (string) config('database.connections.sqlite.database'),
                'tables' => $this->sqliteTables(),
            ],
            'mysql', 'mariadb' => [
                'driver' => $driver,
                'database' => (string) DB::connection()->getDatabaseName(),
                'tables' => $this->mariaDbTables(),
            ],
            default => throw new NotFoundHttpException("Unsupported database driver [{$driver}] for inspection."),
        };
    }

    public function table(string $table, int $page = 1): array
    {
        $overview = $this->overview();
        $tableOverview = collect($overview['tables'])->firstWhere('name', $table);

        if (! $tableOverview) {
            throw new NotFoundHttpException("Table [{$table}] was not found.");
        }

        $driver = $overview['driver'];

        $details = match ($driver) {
            'sqlite' => $this->sqliteTableDetails($table),
            'mysql', 'mariadb' => $this->mariaDbTableDetails($table),
            default => throw new NotFoundHttpException("Unsupported database driver [{$driver}] for inspection."),
        };

        return [
            'driver' => $driver,
            'database' => $overview['database'],
            'name' => $tableOverview['name'],
            'row_count' => $tableOverview['row_count'],
            'metadata' => $tableOverview['metadata'],
            'columns' => $details['columns'],
            'preview' => $this->previewRows($table, $details['primary_key'], $page),
        ];
    }

    private function sqliteTables(): array
    {
        $tables = collect(DB::select(<<<'SQL'
            SELECT name
            FROM sqlite_master
            WHERE type = 'table'
              AND name NOT LIKE 'sqlite_%'
            ORDER BY name
        SQL));

        return $tables
            ->map(function (object $table): array {
                $name = (string) $table->name;

                return [
                    'name' => $name,
                    'row_count' => (int) DB::table($name)->count(),
                    'metadata' => [
                        'type' => 'table',
                    ],
                ];
            })
            ->all();
    }

    private function mariaDbTables(): array
    {
        $schema = DB::connection()->getDatabaseName();

        return collect(DB::table('information_schema.tables')
            ->select([
                'TABLE_NAME as name',
                'TABLE_ROWS as row_count',
                'ENGINE as engine',
                'TABLE_COLLATION as collation',
                'CREATE_TIME as created_at',
                'UPDATE_TIME as updated_at',
            ])
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_TYPE', 'BASE TABLE')
            ->orderBy('TABLE_NAME')
            ->get())
            ->map(fn (object $table): array => [
                'name' => (string) $table->name,
                'row_count' => $table->row_count !== null ? (int) $table->row_count : null,
                'metadata' => [
                    'engine' => $table->engine,
                    'collation' => $table->collation,
                    'created_at' => $table->created_at,
                    'updated_at' => $table->updated_at,
                ],
            ])
            ->all();
    }

    private function sqliteTableDetails(string $table): array
    {
        $columns = collect(DB::select("PRAGMA table_info('".str_replace("'", "''", $table)."')"));
        $indexMap = $this->sqliteIndexMap($table);
        $primaryKey = $columns
            ->sortBy(fn (object $column) => (int) $column->pk)
            ->first(fn (object $column) => (int) $column->pk === 1);

        return [
            'primary_key' => $primaryKey?->name,
            'columns' => $columns->map(function (object $column) use ($indexMap): array {
                return [
                    'name' => (string) $column->name,
                    'type' => (string) $column->type,
                    'nullable' => (int) $column->notnull === 0,
                    'default' => $column->dflt_value,
                    'key' => (int) $column->pk > 0 ? 'PRIMARY' : ($indexMap[$column->name] ?? null),
                    'extra' => (int) $column->pk > 0 ? 'rowid-backed primary key' : null,
                ];
            })->all(),
        ];
    }

    private function mariaDbTableDetails(string $table): array
    {
        $schema = DB::connection()->getDatabaseName();
        $indexMap = $this->mariaDbIndexMap($table);

        $columns = collect(DB::table('information_schema.columns')
            ->select([
                'COLUMN_NAME as name',
                'COLUMN_TYPE as column_type',
                'IS_NULLABLE as is_nullable',
                'COLUMN_DEFAULT as column_default',
                'COLUMN_KEY as column_key',
                'EXTRA as extra',
            ])
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', $table)
            ->orderBy('ORDINAL_POSITION')
            ->get());

        $primaryKey = $columns->first(fn (object $column) => $column->column_key === 'PRI');

        return [
            'primary_key' => $primaryKey?->name,
            'columns' => $columns->map(function (object $column) use ($indexMap): array {
                $key = match ($column->column_key) {
                    'PRI' => 'PRIMARY',
                    'UNI' => 'UNIQUE',
                    'MUL' => $indexMap[$column->name] ?? 'INDEX',
                    default => null,
                };

                return [
                    'name' => (string) $column->name,
                    'type' => (string) $column->column_type,
                    'nullable' => $column->is_nullable === 'YES',
                    'default' => $column->column_default,
                    'key' => $key,
                    'extra' => $column->extra ?: null,
                ];
            })->all(),
        ];
    }

    private function sqliteIndexMap(string $table): array
    {
        $indexes = collect(DB::select("PRAGMA index_list('".str_replace("'", "''", $table)."')"));
        $map = [];

        foreach ($indexes as $index) {
            $indexColumns = collect(DB::select("PRAGMA index_info('".str_replace("'", "''", $index->name)."')"));
            foreach ($indexColumns as $indexColumn) {
                $map[$indexColumn->name] = (int) $index->unique === 1 ? 'UNIQUE' : 'INDEX';
            }
        }

        return $map;
    }

    private function mariaDbIndexMap(string $table): array
    {
        $schema = DB::connection()->getDatabaseName();

        return collect(DB::table('information_schema.statistics')
            ->select([
                'COLUMN_NAME as column_name',
                'INDEX_NAME as index_name',
                'NON_UNIQUE as non_unique',
            ])
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', $table)
            ->get())
            ->reduce(function (array $carry, object $index): array {
                if ($index->index_name === 'PRIMARY') {
                    return $carry;
                }

                $carry[$index->column_name] = (int) $index->non_unique === 0 ? 'UNIQUE' : 'INDEX';

                return $carry;
            }, []);
    }

    private function previewRows(string $table, ?string $primaryKey, int $page): LengthAwarePaginator
    {
        $query = DB::table($table);

        if ($primaryKey !== null) {
            $query->orderByDesc($primaryKey);
        }

        $total = (clone $query)->count();
        $rows = $query
            ->forPage($page, $this->previewPerPage)
            ->get()
            ->map(fn (object $row): array => collect((array) $row)
                ->map(fn ($value) => $this->normalizeValue($value))
                ->all());

        return new LengthAwarePaginator(
            items: $rows,
            total: $total,
            perPage: $this->previewPerPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    private function normalizeValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return Str::limit((string) $value, 80);
        }

        return Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[unprintable]', 80);
    }
}
