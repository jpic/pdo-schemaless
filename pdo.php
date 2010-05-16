<?php

class madPdo extends PDO {
   
    public $handle = null;   
    public $querysections = array('alter', 'create', 'drop', 'select', 'delete', 'insert', 'update','from','where','limit','order');
    public $operators = array('=', '<>', '<', '<=', '>', '>=', 'like', 'clike', 'slike', 'not', 'is', 'in', 'between');
    public $types = array('character', 'char', 'varchar', 'nchar', 'bit', 'numeric', 'decimal', 'dec', 'integer', 'int', 'smallint', 'float', 'real', 'double', 'date', 'datetime', 'time', 'timestamp', 'interval', 'bool', 'boolean', 'set', 'enum', 'text');
    public $conjuctions = array('by', 'as', 'on', 'into', 'from', 'where', 'with');
    public $funcitons = array('avg', 'count', 'max', 'min', 'sum', 'nextval', 'currval', 'concat');
    public $reserved = array('absolute', 'action', 'add', 'all', 'allocate', 'and', 'any', 'are', 'asc', 'ascending', 'assertion', 'at', 'authorization', 'begin', 'bit_length', 'both', 'cascade', 'cascaded', 'case', 'cast', 'catalog', 'char_length', 'character_length', 'check', 'close', 'coalesce', 'collate', 'collation', 'column', 'commit', 'connect', 'connection', 'constraint', 'constraints', 'continue', 'convert', 'corresponding', 'cross', 'current', 'current_date', 'current_time', 'current_timestamp', 'current_user', 'cursor', 'day', 'deallocate', 'declare', 'default', 'deferrable', 'deferred', 'desc', 'descending', 'describe', 'descriptor', 'diagnostics', 'disconnect', 'distinct', 'domain', 'else', 'end', 'end-exec', 'escape', 'except', 'exception', 'exec', 'execute', 'exists', 'external', 'extract', 'false', 'fetch', 'first', 'for', 'foreign', 'found', 'full', 'get', 'global', 'go', 'goto', 'grant', 'group', 'having', 'hour', 'identity', 'immediate', 'indicator', 'initially', 'inner', 'input', 'insensitive', 'intersect', 'isolation', 'join', 'key', 'language', 'last', 'leading', 'left', 'level', 'limit', 'local', 'lower', 'match', 'minute', 'module', 'month', 'names', 'national', 'natural', 'next', 'no', 'null', 'nullif', 'octet_length', 'of', 'only', 'open', 'option', 'or', 'order', 'outer', 'output', 'overlaps', 'pad', 'partial', 'position', 'precision', 'prepare', 'preserve', 'primary', 'prior', 'privileges', 'procedure', 'public', 'read', 'references', 'relative', 'restrict', 'revoke', 'right', 'rollback', 'rows', 'schema', 'scroll', 'second', 'section', 'session', 'session_user', 'size', 'some', 'space', 'sql', 'sqlcode', 'sqlerror', 'sqlstate', 'substring', 'system_user', 'table', 'temporary', 'then', 'timezone_hour', 'timezone_minute', 'to', 'trailing', 'transaction', 'translate', 'translation', 'trim', 'true', 'union', 'unique', 'unknown', 'upper', 'usage', 'user', 'using', 'value', 'values', 'varying', 'view', 'when', 'whenever', 'work', 'write', 'year', 'zone', 'eoc');
    public $startparens = array('{', '(');
    public $endparens = array('}', ')');
    public $tokens = array(',', ' ');
    public $query = '';

    public $schemalessTables = array(  );

    public function __construct( $name_host, $username='', $password='', $driverOptions=array() ) {
        parent::__construct( $name_host, $username, $password, $driverOptions );
        $this->setAttribute( PDO::ATTR_STATEMENT_CLASS, array( 'madPDOStatement' ) );
        
        if ( $cache = apc_fetch( 'mad schmaless tables' ) ) {
            $this->schemalessTables = $cache;
        } else {
            $tables = parent::query('show tables', PDO::FETCH_COLUMN, 0)->fetchAll();

            // find index tables
            foreach( $tables as $table ) {
                $fields = parent::query( "describe $table" )->fetchAll(  );

                if ( count( $fields ) == 1 && $fields[0]['Field'] == 'id' ) {
                    $this->schemalessTables[$table] = array();
                }
            }
            
            // find attribute tables
            foreach( $tables as $table ) {
                $fields = parent::query( "describe $table" )->fetchAll(  );

                if ( count( $fields ) == 2 && $fields[0]['Field'] == 'id' && $fields[1]['Field'] == 'value' ) {
                    foreach( array_keys( $this->schemalessTables ) as $schemalessTable ) {
                        if ( substr( $table, 0, strlen( $schemalessTable ) ) == $schemalessTable ) {
                            $this->schemalessTables[$schemalessTable][] = substr( $table, strlen( $schemalessTable ) + 1 );
                        }
                    }
                }
            }
        }
    }

    public function prepare( $statement, array $driver_options = array() ) {
        if ( preg_match( '/insert( into)? `?([^.]+\.)?(?P<table>[^\s`]+)`? set/i', $statement, $matches ) ) {
            return $this->prepareInsertSet( $statement, $matches['table'] );
        } elseif ( strtolower( substr( $statement, 0, 6 ) ) == 'select' ) {
            $key = $statement;
            $cachedStatement = apc_fetch( $key );

            if ( $cachedStatement ) {
                $statement = $cachedStatement;
            } else {
                $statement = $this->rewriteSelect( $statement );
                apc_store( $key, $statement );
            }
            var_dump( $statement );
            
            return parent::prepare( $statement );
        } elseif ( strtolower( substr( $statement, 0, 6 ) ) == 'delete' ) {
        
        } elseif ( strtolower( substr( $statement, 0, 6 ) ) == 'update' ) {

        }
    }

    /**
     * Simple SQL Tokenizer
     *
     * @author Justin Carlson <justin.carlson@gmail.com>
     * @license GPL
     * @param string $sql
     * @return token array
     */
    public function tokenize($sql,$cleanWhitespace = true) {
       
        /**
         * Strip extra whitespace from the query
         */
        if($cleanWhitespace) {
         $sql = ltrim(preg_replace('/[\\s]{2,}/',' ',$sql));
        }
               
        /**
         * Regular expression based on SQL::Tokenizer's Tokenizer.pm by Igor Sutton Lopes
         **/
        $regex = '('; # begin group
        $regex .= '(?:--|\\#)[\\ \\t\\S]*'; # inline comments
        $regex .= '|(?:<>|<=>|>=|<=|==|=|!=|!|<<|>>|<|>|\\|\\||\\||&&|&|-|\\+|\\*(?!\/)|\/(?!\\*)|\\%|~|\\^|\\?)'; # logical operators
        $regex .= '|[\\[\\]\\(\\),;]|\\\'\\\'(?!\\\')|\\"\\"(?!\\"")'; # empty single/double quotes
        $regex .= '|".*?(?:(?:""){1,}"|(?<!["\\\\])"(?!")|\\\\"{2})|\'.*?(?:(?:\'\'){1,}\'|(?<![\'\\\\])\'(?!\')|\\\\\'{2})'; # quoted strings
        $regex .= '|\/\\*[\\ \\t\\n\\S]*?\\*\/'; # c style comments
        $regex .= '|(?:`?[\\w:@]+`?(?:\\.(?:`?\\w+`?|\\*)?)*)'; # words, placeholders, database.table.column strings
        $regex .= '|[\t\ ]+';
        $regex .= '|[\.]'; #period
        $regex .= '|[\s]'; #whitespace
       
        $regex .= ')'; # end group
       
        // get global match
        preg_match_all( '/' . $regex . '/smx', $sql, $result );
       
        // return tokens
        return $result[0];
   
    }
    public function rewriteSelect($sql,$cleanWhitespace = true) {
        // copy and cut the query
        $tokens = $this->tokenize( $sql, $cleanWhitespace );
       
        $join = array();
        $backticks = '';
        $selectAll = false;
        
        // rewrite columns
        foreach ( $tokens as $key => $token ) {
            if ( in_array( $token, $this->querysections ) ) {
                $querySection = $token;
            }

            if ( $querySection == 'order' ) {
                // no need to rewrite in order sections
                continue;
            }

            if ( $querySection == 'select' && $token == '*' ) {
                // figure the table, rewrite the *
                foreach( $tokens as $subKey => $subToken ) {
                    if ( in_array( $subToken, $this->querysections ) ) {
                        $querySection = $subToken;
                    }

                    if ( $querySection != 'from' ) {
                        continue;
                    }

                    if ( !in_array( $subToken, array_keys( $this->schemalessTables ) ) ) {
                        continue;
                    }

                    $tokens[$key] = '`' . implode( '`, `', $this->schemalessTables[$subToken] ) . '`';
                    return $this->rewriteSelect( implode( '', $tokens ) );
                }
            }

            foreach( $this->schemalessTables as $schemalessTable => $columns ) {

                if ( preg_match( "/`?$schemalessTable`?\\.\\*/", $token ) ) {
                    $selects = array(  );
                    foreach( $columns as $column ) {
                        $selects[] = "`{$schemalessTable}_{$column}`.value AS `$column`";

                        if ( !isset( $join[$schemalessTable] ) ) {
                            $join[$schemalessTable] = array(  );
                        }

                        if ( !in_array( $column, $join[$schemalessTable] ) ) {
                            $join[$schemalessTable][] = $column;
                        }
                    }
                    
                    $tokens[$key] = implode( ', ', $selects );

                    continue;
                }

                foreach( $columns as $column ) {
                    $rewrite = false;

                    if ( in_array( $token, array( $column, "`$column`" ) ) ) {
                        $rewrite = true;
                    }

                    if ( preg_match( "/`?$schemalessTable`?\\.`?$column`?/", $token ) ) {
                        $rewrite = true;
                    }

                    if ( $rewrite ) {
                        $backticks = strpos( $token, '`' ) === false ? '' : '`';
                        $tokens[$key] = "{$backticks}{$schemalessTable}_{$column}{$backticks}.{$backticks}value{$backticks}";

                        if ( $querySection == 'select' ) {
                            $alias = $column;

                            // possible next tokens: ' ', 'AS'
                            $nextTokenKey = $key + 2;
                            if ( strtolower( $tokens[$nextTokenKey] ) == 'as' ) {
                                $tokens[$nextTokenKey] = '';
                                $nextTokenKey += 2;
                                $alias = $tokens[$nextTokenKey];
                                $tokens[$nextTokenKey] = '';
                            }


                            $tokens[$key] .= " AS {$backticks}{$alias}{$backticks}";
                            $backticks = '';
                        }

                        if ( !isset( $join[$schemalessTable] ) ) {
                            $join[$schemalessTable] = array(  );
                        }

                        if ( !in_array( $column, $join[$schemalessTable] ) ) {
                            $join[$schemalessTable][] = $column;
                        }
                    }
                }
            }
        }

        // rewrite FROM
        foreach ( $tokens as $key => $token ) {
            if ( in_array( $token, $this->querysections ) ) {
                $querySection = $token;
            }

            if ( $querySection == 'from' ) {
                if ( in_array( $token, array_keys( $join ) ) ) {
                    foreach( $join[$token] as $column ) {
                        $tokens[$key] .= " LEFT OUTER JOIN {$token}_{$column} ON {$token}_{$column}.id = $token.id";
                    }
                }
            }
        }

        return implode( '', $tokens );
    }

    public function prepareInsertSet( $sql, $table, $cleanWhitespace = true ) {
        $statement = new madPDOStatementWrapper( $this );
        
        // copy and cut the query
        $tokens = $this->tokenize( $sql, $cleanWhitespace );
        
        // parse data
        $style = false;
        $data = array(  );

        foreach ( $tokens as $key => $token ) {
            if ( $token != '=' ) {
                continue;
            }

            $previousTokenKey = $key - 1;
            while ( in_array( $tokens[$previousTokenKey], array( '`', ' ' ) ) ) {
                $previousTokenKey--;
            }

            $nextTokenKey = $key + 1;
            while ( in_array( $tokens[$nextTokenKey], array( '`', ' ' ) ) ) {
                $nextTokenKey++;
            }

            $data[$tokens[$previousTokenKey]] = $tokens[$nextTokenKey];
        }

        if ( !isset( $this->schemalessTables[$table] ) ) {
            $createStatement = parent::prepare( "CREATE TABLE $table (id INT(12) PRIMARY KEY AUTO_INCREMENT) ENGINE=InnoDb" );
            $createStatement->createTable = $table;
            $statement->objects[] = $createStatement;
        }
   
        $insertStatement = parent::prepare( "INSERT INTO $table VALUES()" );
        $insertStatement->insertsSchemalessRow = true;
        $statement->objects[] = $insertStatement;

        foreach( $data as $column => $value ) {
            if ( !isset( $this->schemalessTables[$table] ) || !in_array( $table, $this->schemalessTables[$table] ) ) {
                $createStatement = parent::prepare( "CREATE TABLE {$table}_{$column} (id INT(12), value TEXT, UNIQUE(id)) ENGINE=InnoDb" );
                $createStatement->createColumn = array( $table, $column );
                $statement->objects[] = $createStatement;
            }

            $insertStatement = parent::prepare( "INSERT INTO {$table}_{$column} VALUES( :id, $value ) ON DUPLICATE KEY UPDATE value = $value" );
            $insertStatement->insertAttribute = $column;
            $statement->objects[] = $insertStatement;
        }

        return $statement;
    }
}

class madPDOStatement extends PDOStatement {
    public $createTable = null;
    public $createColumn = null;
    public $insertsSchemalessRow = false;
    public $insertAttribute = false;
}

class madPDOStatementWrapper {
    public $objects = array();
    public $pdo = null;

    public function __construct( $pdo ) {
        $this->pdo = $pdo;
    }

    public function execute( $input_parameters = array(  ) ) {
        $success = true;

        foreach( $this->objects as $object ) {
            if ( $object->insertAttribute ) {
                // work around: Invalid parameter number: number of bound 
                // variables does not match number of tokens
                $result = $object->execute( array(
                    $object->insertAttribute => $input_parameters[$object->insertAttribute],
                    'id' => $input_parameters['id'],
                ) );
            } else {
                $result = $object->execute( $input_parameters );
            }
            
            if ( !$result ) {
                $success = false;
            }
            
            if ( $object->insertsSchemalessRow && !isset( $input_parameters['id'] ) ) {
                $input_parameters['id'] = $this->pdo->lastInsertId(  );
            }

            if ( $object->createTable && !in_array( $object->createTable, $this->pdo->schemalessTables ) ) {
                $this->pdo->schemalessTables[$object->createTable] = array();
            }
    
            if ( $object->createColumn && !in_array( $object->createColumn[1], $this->pdo->schemalessTables[$object->createColumn[0]] ) ) {
                $this->pdo->schemalessTables[$object->createColumn[0]][] = $object->createColumn[1];
            }
        }

        return $success;
    }

    public function __call( $method, $arguments ) {
        foreach( $this->objects as $object ) {
            call_user_func_array( array( $object, $method ), $arguments );
        }
    }
}

$pdo = new madPDO( 'mysql:dbname=testdb;host=localhost', 'root' );

$pdo->prepare( 'insert into posts set title = :title, body = :body, author = :author' )
    ->execute( array( 
        'title'  => 'Poor man schemaless with MySQL', 
        'body'   => 'See mom, no structure!',
        'author' => 1,
) );

$pdo->prepare( 'insert into posts set title = :title, body = :body, author = :author' )
    ->execute( array( 
        'title'  => 'Re: Poor man schemaless with MySQL', 
        'body'   => 'You idiot!',
        'author' => 2,
) );

$pdo->prepare( 'insert into authors set name = :name' )
    ->execute( array( 
        'name' => 'kiddo',
) );

$pdo->prepare( 'insert into authors set name = :name' )
    ->execute( array( 
        'name' => 'dad',
) );

echo "Selecting 2 posts body order by title\n";
$select = $pdo->prepare( 'select * from posts order by title limit 0, 2' );
$select->execute(  );
var_dump( $select->fetchAll(  ) );

echo "Selecting 2 posts body order by title with authors \n";
$select = $pdo->prepare( 'select `posts`.title, authors.* from posts LEFT JOIN authors ON authors.id = posts.author order by title limit 0, 2' );
$select->execute(  );
var_dump( $select->fetchAll(  ) );

echo "Selecting posts with authors\n";
//$select = $pdo->prepare( 'select `authors`.`name` as author_name, `posts`.`title`, posts.body from posts left join authors on authors.id = posts.id' );
//$select->execute(  );
//var_dump( $select->fetchAll(  ) );
//var_dump( $pdo->prepare( 'select title, body from posts' )->execute()->fetchAll() );

//echo "Deleting posts by dad\n";
//$pdo->prepare( 'delete from posts where author = :author' )->rewrite( true )->execute( array( 'author' => 'dad' ) );

//echo "Selecting all authors\n";
//var_dump( $pdo->prepare( 'select author, body from posts' )->rewrite( true )->execute()->fetchAll() );
?>
