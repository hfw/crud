<?php

use Helix\DB\MigrationInterface;
use Helix\DB\Schema;

/** 2021-06-27T01:40:04.186Z_AuthorsToBooks */
return new class implements MigrationInterface {

    /**
     * @param Schema $schema
     */
    public function up($schema)
    {
        $schema->createTable('authors_to_books', [
            'author' => Schema::T_INT,
            'book' => Schema::T_INT
        ], [
            Schema::TABLE_PRIMARY => ['author', 'book'],
            Schema::TABLE_FOREIGN => [
                'author' => $schema['authors']['id'],
                'book' => $schema['books']['id']
            ]
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function down($schema)
    {
        $schema->dropTable('authors_to_books');
    }

};