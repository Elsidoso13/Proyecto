<?php
// database.php - Configuración de conexión PostgreSQL para PHP

class Database {
    private $host = 'hopper.proxy.rlwy.net';
    private $port = 56268;
    private $database = 'railway';
    private $username = 'postgres';
    private $password = 'SRfzaicqBcKrtNNzNWStBqshucFiNFxW';
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database};sslmode=require";
            
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            echo "✅ Conexión exitosa a PostgreSQL\n";
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta: " . $e->getMessage());
        }
    }
    
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Error en ejecución: " . $e->getMessage());
        }
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($data);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error en inserción: " . $e->getMessage());
        }
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_merge($data, $whereParams));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Error en actualización: " . $e->getMessage());
        }
    }
    
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($whereParams);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Error en eliminación: " . $e->getMessage());
        }
    }
    
    public function testConnection() {
        try {
            $result = $this->query("SELECT version()");
            echo "✅ Prueba de conexión exitosa\n";
            echo "Versión PostgreSQL: " . $result[0]['version'] . "\n";
            return true;
        } catch (Exception $e) {
            echo "❌ Error en prueba de conexión: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function getTables() {
        $sql = "SELECT tablename FROM pg_tables WHERE schemaname = 'public'";
        return $this->query($sql);
    }
    
    public function getTableInfo($tableName) {
        $sql = "SELECT column_name, data_type, is_nullable 
                FROM information_schema.columns 
                WHERE table_name = :table_name";
        return $this->query($sql, ['table_name' => $tableName]);
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function close() {
        $this->connection = null;
        echo "Conexión cerrada\n";
    }
}

// Ejemplo de uso
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    // Solo ejecutar si el archivo se ejecuta directamente
    try {
        $db = new Database();
        
        // Probar conexión
        $db->testConnection();
        
        // Listar tablas
        $tables = $db->getTables();
        echo "\nTablas disponibles:\n";
        foreach ($tables as $table) {
            echo "- " . $table['tablename'] . "\n";
        }
        
        // Ejemplo de consulta
        // $usuarios = $db->query("SELECT * FROM usuarios LIMIT 5");
        // var_dump($usuarios);
        
        // Ejemplo de inserción
        // $nuevoId = $db->insert('usuarios', [
        //     'nombre' => 'Juan',
        //     'email' => 'juan@example.com'
        // ]);
        
        // Ejemplo de actualización
        // $filasActualizadas = $db->update('usuarios', 
        //     ['nombre' => 'Juan Carlos'], 
        //     'id = :id', 
        //     ['id' => 1]
        // );
        
        // Ejemplo de eliminación
        // $filasEliminadas = $db->delete('usuarios', 'id = :id', ['id' => 1]);
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>