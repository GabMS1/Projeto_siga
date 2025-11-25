CREATE DATABASE IF NOT EXISTS projeto_siga;
USE projeto_siga;

-- Remover tabelas na ordem correta (devido às FKs)
DROP TABLE IF EXISTS `relatorio`;
DROP TABLE IF EXISTS `programada`;
DROP TABLE IF EXISTS `prof_subs`;
DROP TABLE IF EXISTS `prof_ausente`;
DROP TABLE IF EXISTS `turma_disciplinas`;
DROP TABLE IF EXISTS `turmas`;
DROP TABLE IF EXISTS `disciplina`;
DROP TABLE IF EXISTS `admin`;
DROP TABLE IF EXISTS `professor`;

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
    `aulas_semanais` INT NOT NULL,
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Tabela de turmas
CREATE TABLE `turmas` (
    `id_turma` INT NOT NULL,
    `curso` ENUM('Agropecuária', 'Alimentos', 'Informática') NOT NULL,
    `serie` ENUM('1', '2', '3') NOT NULL,
    PRIMARY KEY (`id_turma`)
);

-- Tabela de associação entre turmas e disciplinas
CREATE TABLE `turma_disciplinas` (
    `id_turma` INT NOT NULL,
    `id_disciplina` INT NOT NULL,
    PRIMARY KEY (`id_turma`, `id_disciplina`),
    FOREIGN KEY (`id_turma`) REFERENCES `turmas`(`id_turma`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina`(`id_disciplina`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabela para registros de professores ausentes
CREATE TABLE `prof_ausente` (
    `id_ass_ausente` INT AUTO_INCREMENT PRIMARY KEY,
    `ass_ausente` VARCHAR(255) NOT NULL,
    `siape_prof` VARCHAR(20),
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabela para registros de professores substitutos
CREATE TABLE `prof_subs` (
    `id_ass_subs` INT AUTO_INCREMENT PRIMARY KEY,
    `ass_subs` VARCHAR(255) NOT NULL,
    `siape_prof` VARCHAR(20),
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabela para reposições - CORRIGIDA
CREATE TABLE `programada` (
    `id_progra` INT AUTO_INCREMENT PRIMARY KEY,
    `dia` DATE NOT NULL,
    `horario` TIME NOT NULL,
    `autor_gov` VARCHAR(255),
    `id_turma` INT,
    `id_disciplina` INT,
    `id_ass_subs` INT,
    `id_ass_ausente` INT,
    FOREIGN KEY (`id_turma`) REFERENCES `turmas`(`id_turma`) ON DELETE CASCADE ON UPDATE CASCADE,  -- ✅ CORRIGIDO
    FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina`(`id_disciplina`) ON DELETE CASCADE ON UPDATE CASCADE,  -- ✅ CORRIGIDO
    FOREIGN KEY (`id_ass_subs`) REFERENCES `prof_subs`(`id_ass_subs`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`id_ass_ausente`) REFERENCES `prof_ausente`(`id_ass_ausente`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabela de relatórios
CREATE TABLE `relatorio` (
    `id_relatorio` INT AUTO_INCREMENT PRIMARY KEY,
    `aulas_substituidas` VARCHAR(255),
    `aulas_cedidas` VARCHAR(255),
    `siape_prof` VARCHAR(20),
    `id_progra` INT,
    `id_adm` INT,
    FOREIGN KEY (`siape_prof`) REFERENCES `professor`(`siape_prof`) ON DELETE CASCADE ON UPDATE CASCADE,  -- ✅ CORRIGIDO
    FOREIGN KEY (`id_progra`) REFERENCES `programada`(`id_progra`) ON DELETE CASCADE ON UPDATE CASCADE,  -- ✅ CORRIGIDO
    FOREIGN KEY (`id_adm`) REFERENCES `admin`(`id_adm`) ON DELETE CASCADE ON UPDATE CASCADE  -- ✅ CORRIGIDO
);

-- INSERÇÕES SEGURAS - CORRIGIDAS
INSERT IGNORE INTO `professor` (`siape_prof`, `nome`, `senha`) 
VALUES 
('1234567', 'João Silva', 'senha123'),
('2345678', 'Maria Santos', 'senha456'),
('3456789', 'Pedro Costa', 'senha789');

INSERT IGNORE INTO `admin` (`siape_login`, `nome`, `senha_adm`, `cargo`) 
VALUES 
('7654321', 'Carlos Oliveira', 'admin123', 'Coordenador'),
('8765432', 'Ana Pereira', 'admin456', 'Diretor');

INSERT IGNORE INTO `turmas` (`id_turma`, `curso`, `serie`) 
VALUES 
(1, 'Informática', '1'),
(2, 'Informática', '2'),
(3, 'Agropecuária', '1');

-- Adicionando índices para melhor performance
CREATE INDEX `idx_disciplina_professor` ON `disciplina`(`siape_prof`);
CREATE INDEX `idx_relatorio_professor` ON `relatorio`(`siape_prof`);
CREATE INDEX `idx_prof_ausente_siape` ON `prof_ausente`(`siape_prof`);
CREATE INDEX `idx_programada_turma_disciplina` ON `programada`(`id_turma`, `id_disciplina`);