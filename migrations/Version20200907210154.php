<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200907210154 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE routes ADD COLUMN http_status INTEGER DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_9D6C0FB1E9D6E2FE');
        $this->addSql('DROP INDEX IDX_9D6C0FB1F033B271');
        $this->addSql('CREATE TEMPORARY TABLE __temp__routes_routes AS SELECT routes_source, routes_target FROM routes_routes');
        $this->addSql('DROP TABLE routes_routes');
        $this->addSql('CREATE TABLE routes_routes (routes_source INTEGER NOT NULL, routes_target INTEGER NOT NULL, PRIMARY KEY(routes_source, routes_target), CONSTRAINT FK_9D6C0FB1F033B271 FOREIGN KEY (routes_source) REFERENCES routes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9D6C0FB1E9D6E2FE FOREIGN KEY (routes_target) REFERENCES routes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO routes_routes (routes_source, routes_target) SELECT routes_source, routes_target FROM __temp__routes_routes');
        $this->addSql('DROP TABLE __temp__routes_routes');
        $this->addSql('CREATE INDEX IDX_9D6C0FB1E9D6E2FE ON routes_routes (routes_target)');
        $this->addSql('CREATE INDEX IDX_9D6C0FB1F033B271 ON routes_routes (routes_source)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__routes AS SELECT id, route, state, times_visited, created_on FROM routes');
        $this->addSql('DROP TABLE routes');
        $this->addSql('CREATE TABLE routes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, route VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, times_visited INTEGER NOT NULL, created_on DATETIME NOT NULL)');
        $this->addSql('INSERT INTO routes (id, route, state, times_visited, created_on) SELECT id, route, state, times_visited, created_on FROM __temp__routes');
        $this->addSql('DROP TABLE __temp__routes');
        $this->addSql('DROP INDEX IDX_9D6C0FB1F033B271');
        $this->addSql('DROP INDEX IDX_9D6C0FB1E9D6E2FE');
        $this->addSql('CREATE TEMPORARY TABLE __temp__routes_routes AS SELECT routes_source, routes_target FROM routes_routes');
        $this->addSql('DROP TABLE routes_routes');
        $this->addSql('CREATE TABLE routes_routes (routes_source INTEGER NOT NULL, routes_target INTEGER NOT NULL, PRIMARY KEY(routes_source, routes_target))');
        $this->addSql('INSERT INTO routes_routes (routes_source, routes_target) SELECT routes_source, routes_target FROM __temp__routes_routes');
        $this->addSql('DROP TABLE __temp__routes_routes');
        $this->addSql('CREATE INDEX IDX_9D6C0FB1F033B271 ON routes_routes (routes_source)');
        $this->addSql('CREATE INDEX IDX_9D6C0FB1E9D6E2FE ON routes_routes (routes_target)');
    }
}
