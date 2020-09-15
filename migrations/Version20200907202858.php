<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200907202858 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE routes_routes (routes_source INTEGER NOT NULL, routes_target INTEGER NOT NULL, PRIMARY KEY(routes_source, routes_target))');
        $this->addSql('CREATE INDEX IDX_9D6C0FB1F033B271 ON routes_routes (routes_source)');
        $this->addSql('CREATE INDEX IDX_9D6C0FB1E9D6E2FE ON routes_routes (routes_target)');
        $this->addSql('DROP TABLE base_routes');
        $this->addSql('DROP INDEX IDX_32D5C2B37E07171E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__routes AS SELECT id, route, state, times_visited, created_on FROM routes');
        $this->addSql('DROP TABLE routes');
        $this->addSql('CREATE TABLE routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, route VARCHAR(255) NOT NULL COLLATE BINARY, state VARCHAR(255) NOT NULL COLLATE BINARY, times_visited INTEGER NOT NULL, created_on DATETIME NOT NULL)');
        $this->addSql('INSERT INTO routes (id, route, state, times_visited, created_on) SELECT id, route, state, times_visited, created_on FROM __temp__routes');
        $this->addSql('DROP TABLE __temp__routes');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE base_routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, base_route VARCHAR(255) NOT NULL COLLATE BINARY)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5AB55ADABFEFB1AB ON base_routes (base_route)');
        $this->addSql('DROP TABLE routes_routes');
        $this->addSql('CREATE TEMPORARY TABLE __temp__routes AS SELECT id, route, state, times_visited, created_on FROM routes');
        $this->addSql('DROP TABLE routes');
        $this->addSql('CREATE TABLE routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, route VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, times_visited INTEGER NOT NULL, created_on DATETIME NOT NULL, base_route_id INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO routes (id, route, state, times_visited, created_on) SELECT id, route, state, times_visited, created_on FROM __temp__routes');
        $this->addSql('DROP TABLE __temp__routes');
        $this->addSql('CREATE INDEX IDX_32D5C2B37E07171E ON routes (base_route_id)');
    }
}
