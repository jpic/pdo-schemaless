Flawed query rewriter test implementation. Inspired by: http://www.igvita.com/2010/03/01/schema-free-mysql-vs-nosql/

Flaws:
* the query should be parsed better ( http://search.cpan.org/~rehsack/SQL-Statement-1.27/lib/SQL/Statement/Structure.pod )
* the query should be rewritten in a PDO method, without dependency on the arguments it is executed with 
* rewriten query should be cached in APC
* database should be reversed and prepare() should know if it needs to rewrite or not (result should be apc cached)
* APC should help the "system" to improve itself any time the database must be reversed (ie. the show tables in madPDO constructor)

We already have a (more or less useful) schemaless model layer so i'm doing this on my free time ... Any help is welcome!
