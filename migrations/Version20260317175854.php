<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260317175854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE advice (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE month_advice (id INT AUTO_INCREMENT NOT NULL, number_in_year INT NOT NULL, advice_id INT DEFAULT NULL, INDEX IDX_6FB6E21312998205 (advice_id), UNIQUE INDEX UNIQ_IDENTIFIER_MONTH_ADVICE (number_in_year, advice_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, city VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE month_advice ADD CONSTRAINT FK_6FB6E21312998205 FOREIGN KEY (advice_id) REFERENCES advice (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE month_advice DROP FOREIGN KEY FK_6FB6E21312998205');
        $this->addSql('DROP TABLE advice');
        $this->addSql('DROP TABLE month_advice');
        $this->addSql('DROP TABLE `user`');
    }
}
