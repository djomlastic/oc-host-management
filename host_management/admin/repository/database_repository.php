<?php

namespace Opencart\Extension\HostManagement\Admin\Repository;

use Opencart\System\Engine\Model;


/**
 * Host management extension DB repository.
 */
class DatabaseRepository extends Model
{
    /**
     * DB table name.
     *
     * @var string
     */
    protected static $table_name = 'host_management';


    /**
     * Creates DB table.
     *
     * @return void
     */
    public function install(): void {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . static::$table_name . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `protocol` varchar(5) NOT NULL,
                `hostname` varchar(255) NOT NULL,
                `default` tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        $this->db->query($query);
    }

    /**
     * Drops DB table.
     *
     * @return void
     */
    public function uninstall(): void
    {
        $query = "DROP TABLE IF EXISTS `" . DB_PREFIX . static::$table_name . "`";

        $this->db->query($query);
    }

    /**
     * Inserts host data into DB.
     *
     * @param array $data
     * @return void
     */
    public function insert(array $data): void
    {
        $data['default'] ??= false;

        $query = "INSERT INTO `" . DB_PREFIX . static::$table_name . "` SET
                `protocol` = '" . $this->db->escape($data['protocol']) . "',
                `hostname` = '" . $this->db->escape($data['hostname']) . "',
                `default` = '" . (bool)$data['default'] . "'";

        $this->db->query($query);
    }

    /**
     * Inserts multiple host data into DB.
     *
     * @param array $data
     * @return void
     */
    public function insertMany(array $data): void
    {
        usort($data, function($a, $b) {
            return (bool)$a['default'] ? -1 : ((bool)$b['default'] ? 1 : 0);
        });

        $query = "INSERT INTO `" . DB_PREFIX . static::$table_name . "`
                (`protocol`, `hostname`, `default`) VALUES";

        for ($i = 0; $i < count($data); $i++) {
            $query .= $i === 0 ? ' (' : ', (';
            $query .= "'" . $this->db->escape($data[$i]['protocol']) . "'";
            $query .= ", '" . $this->db->escape($data[$i]['hostname']) . "'";
            $query .= ", '" . (bool)$data[$i]['default'] . "')";
        }

        $this->db->query($query);
    }

    /**
     * Gets all hosts from DB.
     *
     * @return array
     */
    public function getAll(): array
    {
        $query = "SELECT * FROM `" . DB_PREFIX . static::$table_name . "`";

        $result = $this->db->query($query);

        return is_object($result) ? $result->rows : [];
    }

    /**
     * Gets default host from DB.
     *
     * @return array|null
     */
    public function getDefault(): ?array
    {
        $query = "SELECT * FROM `" . DB_PREFIX . static::$table_name . "`";
        $query .= " WHERE `default` = 1";

        $result = $this->db->query($query);

        return is_object($result) ? $result->row : null;
    }

    /**
     * Updates default host data.
     *
     * @return bool
     */
    public function updateDefault(array $data): bool
    {
        $query = "UPDATE `" . DB_PREFIX . static::$table_name . "` SET
                `protocol` = '" . $this->db->escape($data['protocol']) . "',
                `hostname` = '" . $this->db->escape($data['hostname']) . "'
                WHERE `default` = 1";

        return $this->db->query($query);
    }

    /**
     * Truncates DB table.
     *
     * @return void
     */
    public function truncate():void
    {
        $query = "TRUNCATE `" . DB_PREFIX . static::$table_name . "`";

        $this->db->query($query);
    }
}