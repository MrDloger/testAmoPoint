<?

class Db
{
	protected static \PDO $pdo;
	protected static \PDOStatement $stmt;
	private static $instance = null;
	private function __construct()
	{
		self::$pdo = new \PDO(
			'mysql:dbname=testAmo;host=MySQL-8.2', 
			'root',
			'',
			[
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    			\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
    			\PDO::ATTR_EMULATE_PREPARES => false
			]
		);
	}
	private function __clone(){
		return self::$instance; 
	}

	public static function insert(string $table, array $value):false|int
	{
		$fields = [];
		foreach ($value as $key => $v){
			$fields[] = strtolower($key);
		}
		$stmt = self::$pdo->prepare("INSERT INTO {$table}(" . implode(',', $fields). ") VALUES (:" . implode(',:', $fields) . ")");
		$stmt->execute( $value );
		return self::$pdo->lastInsertId();
	}
	public static function query($query)
	{
		$stmt = self::$pdo->prepare($query);
		$stmt->execute();
		return $stmt;
	}
	public static function select(string $table, string $fields, string $filter, array $values):\PDOStatement
	{
		$stmt = self::$pdo->prepare("SELECT {$fields} FROM {$table} " . self::prepareFilter($filter));
		$stmt->execute($values);
		return $stmt;
	}
	public static function find(string $table, string $fields, string $filter = null, array $values = null):\PDOStatement
	{
		$fields = strtolower($fields);
		$stmt = self::$pdo->prepare("SELECT {$fields} FROM {$table} " . self::prepareFilter($filter));
		$stmt->execute($values);
		return $stmt;
	}
	public static function update($table, $filter, $values):bool
	{
		$prepareValue = [];
		foreach($values as $key =>$value)
		{
			$prepareValues[] = strtolower($key) . '=' . " ?";
			$pValues[] = $value;
		}
		$stmt = self::$pdo->prepare("UPDATE {$table} SET " . implode(',', $prepareValues) . self::prepareFilter($filter));
		return $stmt->execute($pValues);
	}
	protected static function prepareFilter(string $filter): string
	{
		return empty($filter) ? '' : 'WHERE ' . $filter;
	}
	public static function getInstance():static
	{
		if (self::$instance === null){
			self::$instance = new Db();
		}
		return self::$instance;
	}
	public function executeQuery(string $query, array $values = null):\PDOStatement
	{
		$stmt = self::$pdo->prepare($query);
		$stmt->execute($values ?? null);
		return $stmt;
	}
}
