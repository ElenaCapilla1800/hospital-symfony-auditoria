<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225114357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access_log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(50) NOT NULL, ip_address VARCHAR(45) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, medical_record_id INT NOT NULL, INDEX IDX_EF7F3510A76ED395 (user_id), INDEX IDX_EF7F3510B88E2BB6 (medical_record_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE medical_record (id INT AUTO_INCREMENT NOT NULL, patient_name VARCHAR(100) NOT NULL, diagnosis LONGTEXT NOT NULL, treatment LONGTEXT NOT NULL, doctor_id INT NOT NULL, INDEX IDX_F06A283E87F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, speciality VARCHAR(50) DEFAULT NULL, is_active TINYINT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE access_log ADD CONSTRAINT FK_EF7F3510A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE access_log ADD CONSTRAINT FK_EF7F3510B88E2BB6 FOREIGN KEY (medical_record_id) REFERENCES medical_record (id)');
        $this->addSql('ALTER TABLE medical_record ADD CONSTRAINT FK_F06A283E87F4FB17 FOREIGN KEY (doctor_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE access_log DROP FOREIGN KEY FK_EF7F3510A76ED395');
        $this->addSql('ALTER TABLE access_log DROP FOREIGN KEY FK_EF7F3510B88E2BB6');
        $this->addSql('ALTER TABLE medical_record DROP FOREIGN KEY FK_F06A283E87F4FB17');
        $this->addSql('DROP TABLE access_log');
        $this->addSql('DROP TABLE medical_record');
        $this->addSql('DROP TABLE user');
    }
}
