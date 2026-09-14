<?php
abstract class Model
{
    protected string $table = '';
    protected string $pk    = 'id';
    protected Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function find(int $id): array|false
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->table}` WHERE `{$this->pk}` = ? LIMIT 1", [$id]);
    }

    public function findBy(array $cond): array|false
    {
        [$w, $p] = $this->where_clause($cond);
        return $this->db->fetchOne("SELECT * FROM `{$this->table}` WHERE $w LIMIT 1", $p);
    }

    public function where(array $cond, string $order = '', int $limit = 0, int $offset = 0): array
    {
        [$w, $p] = $this->where_clause($cond);
        $sql = "SELECT * FROM `{$this->table}` WHERE $w";
        if ($order)  $sql .= " ORDER BY $order";
        if ($limit)  $sql .= " LIMIT $limit";
        if ($offset) $sql .= " OFFSET $offset";
        return $this->db->fetchAll($sql, $p);
    }

    public function all(string $order = ''): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($order) $sql .= " ORDER BY $order";
        return $this->db->fetchAll($sql);
    }

    public function count(array $cond = []): int
    {
        if (empty($cond)) return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}`");
        [$w, $p] = $this->where_clause($cond);
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM `{$this->table}` WHERE $w", $p);
    }

    public function create(array $data): int      { return $this->db->insert($this->table, $data); }
    public function update(int $id, array $data): int { return $this->db->update($this->table, $data, "`{$this->pk}` = ?", [$id]); }
    public function delete(int $id): int          { return $this->db->delete($this->table, "`{$this->pk}` = ?", [$id]); }
    public function exists(array $cond): bool     { return $this->count($cond) > 0; }

    public function paginate(string $sql, array $params, int $page, int $perPage = 10): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $total  = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM ($sql) AS _c", $params);
        return [
            'data'         => $this->db->fetchAll("$sql LIMIT $perPage OFFSET $offset", $params),
            'total'        => $total,
            'pages'        => (int) ceil($total / $perPage),
            'current_page' => $page,
            'per_page'     => $perPage,
        ];
    }

    public function raw(string $sql, array $p = []): PDOStatement { return $this->db->query($sql, $p); }

    protected function where_clause(array $cond): array
    {
        $parts = []; $params = [];
        foreach ($cond as $col => $val) {
            if (is_null($val)) { $parts[] = "`$col` IS NULL"; }
            else { $parts[] = "`$col` = ?"; $params[] = $val; }
        }
        return [implode(' AND ', $parts), $params];
    }
}
