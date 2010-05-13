Flawed query rewriter test implementation.

Flaws:
- the query should be parsed better ( http://search.cpan.org/~rehsack/SQL-Statement-1.27/lib/SQL/Statement/Structure.pod )
- the query should be rewritten in a PDO method, without dependency on the arguments it is executed with 
- rewriten query should be cached in APC

<?php

class madPDO extends PDO {
    public $tables = array(  );
    public function __construct( $name_host, $username='', $password='', $driverOptions=array() ) {
        parent::__construct($name_host, $username, $password, $driverOptions);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('madPDOStatement', array($this)));        

        $this->tables = parent::query('show tables', PDO::FETCH_COLUMN, 0)->fetchAll();
    }
}

class madPDOStatement extends PDOStatement {
    public $rewrite = false;

    protected function __construct($db) {
        $this->pdo = $db;
    }

    public function rewrite( $bool ) {
        $this->rewrite = $bool;
        return $this;
    }

    public function execute( array $array = array() ) {
        if ( !$this->rewrite ) {
            return parent::execute( $array );
        }

        $parts = explode( ' ', $this->queryString );

        switch( strtolower( $parts[0] ) ) {
            case 'insert':
                if ( !isset( $table ) ) {
                    $table = $parts[2];
                }

                if ( !in_array( $table, $this->pdo->tables ) ) {
                    $this->pdo->query( "CREATE TABLE $table (id varchar(255), UNIQUE(id))" );
                }

                foreach( $array as $key => $value ) {
                    if ( $key == 'id' ) {
                        continue;
                    }

                    if ( !in_array( "{$table}_{$key}", $this->pdo->tables ) ) {
                        $this->pdo->query( "CREATE TABLE {$table}_{$key} (id varchar(44), value text, UNIQUE(id))" );
                    }

                    $attributeSql = "INSERT INTO {$table}_{$key} VALUES (:id, :value) ON DUPLICATE KEY UPDATE value = :value";

                    $attributeStmt = $this->pdo->prepare( $attributeSql );
                    
                    if ( !$attributeStmt ) {
                        $error = $this->errorInfo(  );
                        trigger_error( $error[2], E_USER_ERROR );
                    }

                    $attributeStmt->execute( array( 
                        'id' => $array['id'],
                        'value' => $value,
                    ) );
                }

                $stmt = $this->pdo->prepare( implode( ' ', array_merge( array_slice( $parts, 0, 3 ), array( "VALUES (:id)" ) ) ) );
                
                if ( !$stmt ) {
                    $error = $this->errorInfo(  );
                    trigger_error( $error[2], E_USER_ERROR );
                }

                $stmt->execute( array( 'id' => $array['id'] ) );
                
                return $stmt;
            case 'select':
                $regexp = '/select(?P<select>.*?)from\s(?P<table>[^\s]+)( where(?<where>.*?))?( (?P<postWhere>(group by.*)|(having.*)|(order by.*)|(limit.*)|(procedure.*)|(into outfile.*)|(for update.*)|(lock in share.*))*)?$/i';
                if ( !preg_match( $regexp, $this->queryString, $matches ) ) {
                    trigger_error( "Unparsable query " . $this->queryString, E_USER_ERROR );
                }

                $table = $matches['table'];

                $newSelect = array(  );
                $newSelect[] = "`$table`.`id` AS `id`";

                $newJoin = array(  );
                $newJoin[] = "FROM `$table`";

                $rewrite = array();
                foreach( explode( ',', $matches['select'] ) as $key => $value ) {
                    $column = trim( $value );
                    if ( $column == 'id' ) {
                        continue;
                    }

                    $rewrite["/ $column /"] = " `{$table}_{$column}`.`value` ";
                    $newSelect[] = "`{$table}_{$column}`.`value` AS `$column`";
                    $newJoin[] = "LEFT OUTER JOIN `{$table}_{$column}` ON `{$table}_{$column}`.`id` = `$table`.`id`";
                }

                $query = "SELECT " . implode( ', ', $newSelect );
                $query.= " " . implode( ' ', $newJoin );
                
                if ( isset( $matches['where'] ) && $matches['where'] ) {
                    $query.= " WHERE " . preg_replace( array_keys( $rewrite ), array_values( $rewrite ), $matches['where'] );
                }

                if ( isset( $matches['postWhere'] ) && $matches['postWhere'] ) {
                    $query.= " " . $matches['postWhere'];
                }
                
                $stmt = $this->pdo->prepare( $query );

                if ( !$stmt ) {
                    $error = $this->errorInfo(  );
                    trigger_error( $error[2], E_USER_ERROR );
                }
                $stmt->execute( $array );

                return $stmt;
            case 'delete':
                $regexp = '/delete from\s(?P<table>[^\s]+)( where(?<where>.*?))?$/i';
                if ( !preg_match( $regexp, $this->queryString, $matches ) ) {
                    trigger_error( "Unparsable query " . $this->queryString, E_USER_ERROR );
                }

                $fields = implode( ', ', array_keys( $array ) );
                $rows = $this->pdo->prepare( "select id, $fields from {$matches['table']} where{$matches['where']}" )->rewrite( true )->execute( $array )->fetchAll(  );
                if ( !$rows ) {
                    return false;
                }

                $ids = array(  );
                foreach( $rows as $row ) {
                    $ids[] = $row['id'];
                }
                $ids = implode( ', ', $ids );
            
                foreach( $this->pdo->tables as $table ) {
                    if ( strpos( $table, $matches['table'] ) !== 0 ) {
                        continue;
                    }

                    $this->pdo->query( "DELETE FROM $table WHERE id IN ( $ids ) " );
                }
                return false;
            default:
                trigger_error( $parts[0] . ' not recognized sql command' );
        }
    }

}

$pdo = new madPDO( 'mysql:dbname=testdb;host=localhost', 'root' );

$pdo->prepare( 'insert into posts (id, author, body) values ( :id, :author, :body )' )
    ->rewrite( true )
    ->execute( array( 'id' => 1, 'author' => 'jpic', 'body' => 'see mom, no structure' ) );

$pdo->prepare( 'insert into posts (id, author, body) values ( :id, :author, :body )' )
    ->rewrite( true )
    ->execute( array( 'id' => 2, 'author' => 'mom', 'body' => 'good kiddo' ) );

$pdo->prepare( 'insert into posts (id, author, body) values ( :id, :author, :body )' )
    ->rewrite( true )
    ->execute( array( 'id' => 3, 'author' => 'dad', 'body' => 'you idiot' ) );

echo "Selecting 2 posts ordered by author name\n";
var_dump( $pdo->prepare( 'select author, body from posts order by author limit 0,2' )->rewrite( true )->execute()->fetchAll() );

echo "Deleting posts by dad\n";
$pdo->prepare( 'delete from posts where author = :author' )->rewrite( true )->execute( array( 'author' => 'dad' ) );

echo "Selecting all authors\n";
var_dump( $pdo->prepare( 'select author, body from posts' )->rewrite( true )->execute()->fetchAll() );
?>
