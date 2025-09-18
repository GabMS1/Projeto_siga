CREATE DATABASE IF NOT EXISTS projeto_siga;

USE `projeto_siga` DROP TABLE IF EXISTS `relatorio`, `programada`, `prof_subs`, `prof_ausente`, `turma`, `disciplina`, `admin`, `professor`;

-- Tabela de professores
CREATE TABLE `professor` (
    `siape_prof` VARCHAR(20) NOT NULL PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `senha` VARCHAR(255) NOT NULL
);

-- Tabela de administradores
CREATE TABLE `admin` (
    `id_adm` INT AUTO_INCREMENT PRIMARY KEY,
    `siape_login` VARCHAR(20) NOT NULL UNIQUE,
    `nome` VARCHAR(255) NOT NULL,
    `senha_adm` VARCHAR(255) NOT NULL,
    `cargo` ENUM('Coordenador', 'Diretor', 'Secretário') NOT NULL
);

-- Tabela de disciplinas
CREATE TABLE `disciplina` (
    `id_disciplina` INT AUTO_INCREMENT PRIMARY KEY,
    `nome_disciplina` VARCHAR(255) NOT NULL,
    `ch` INT NOT NULL,
    `siape_prof` VARCHAR(20),
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`)
);

-- Tabela de turmas
CREATE TABLE `turma` (
    `id_turma` INT PRIMARY KEY,
    `curso` VARCHAR(255) NOT NULL,
    `serie` ENUM('1º', '2º', '3º', '4º') NOT NULL,
    `id_disciplina` INT,
    FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina`(`id_disciplina`)
);

-- Tabela para registros de professores ausentes
CREATE TABLE `prof_ausente` (
    `id_ass_ausente` INT AUTO_INCREMENT PRIMARY KEY,
    `ass_ausente` VARCHAR(255) NOT NULL,
    `siape_prof` VARCHAR(20),
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`)
);

-- Tabela para registros de professores substitutos
CREATE TABLE `prof_subs` (
    `id_ass_subs` INT AUTO_INCREMENT PRIMARY KEY,
    `ass_subs` VARCHAR(255) NOT NULL,
    `siape_prof` VARCHAR(20),
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`)
);

-- Tabela para reposições
CREATE TABLE `programada` (
    `id_progra` INT AUTO_INCREMENT PRIMARY KEY,
    `dia` DATE NOT NULL,
    `horario` TIME NOT NULL,
    `autor_gov` VARCHAR(255),
    `id_turma` INT,
    `id_disciplina` INT,
    `id_ass_subs` INT,
    `id_ass_ausente` INT,
    FOREIGN KEY (`id_turma`) REFERENCES `turma`(`id_turma`),
    FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina`(`id_disciplina`),
    FOREIGN KEY (`id_ass_subs`) REFERENCES `prof_subs`(`id_ass_subs`),
    FOREIGN KEY (`id_ass_ausente`) REFERENCES `prof_ausente`(`id_ass_ausente`)
);

-- Tabela de relatórios
CREATE TABLE `relatorio` (
    `id_relatorio` INT AUTO_INCREMENT PRIMARY KEY,
    `aulas_substituidas` VARCHAR(255),
    `aulas_cedidas` VARCHAR(255),
    `siape_prof` VARCHAR(20),
    `id_progra` INT,
    `id_adm` INT,
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`),
    FOREIGN KEY (`id_progra`) REFERENCES `programada`(`id_progra`),
    FOREIGN KEY (`id_adm`) REFERENCES `admin`(`id_adm`)
);

-- Adicionando índices para melhor performance
CREATE INDEX idx_disciplina_professor ON `disciplina`(`siape_prof`);
CREATE INDEX idx_turma_disciplina ON `turma`(`id_disciplina`);
CREATE INDEX idx_relatorio_professor ON `relatorio`(`siape_prof`);
CREATE INDEX idx_prof_ausente_siape ON `prof_ausente`(`siape_prof`);
CREATE INDEX idx_programada_turma ON `programada`(`id_turma`);
CREATE INDEX idx_programada_disciplina ON `programada`(`id_disciplina`);

ALTER TABLE `disciplina` ADD `aulas_semanais` INT NOT NULL AFTER `ch`;

ALTER TABLE `turma`
CHANGE `curso` `curso` ENUM('Agropecuária', 'Alimentos', 'Informática') NOT NULL,
CHANGE `serie` `serie` ENUM('1', '2', '3') NOT NULL;
ALTER TABLE `disciplina` CHANGE `siape_prof` `siape_prof` VARCHAR(20) NULL DEFAULT NULL;