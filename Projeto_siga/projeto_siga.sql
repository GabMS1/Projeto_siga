CREATE DATABASE projeto_siga;

-- Tabela de professores
CREATE TABLE professor (
    siape_prof INT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de disciplinas
CREATE TABLE disciplina (
    id_disciplina INT AUTO_INCREMENT PRIMARY KEY,
    nome_disciplina VARCHAR(50) NOT NULL,
    ch INT NOT NULL, -- carga horária em horas
    siape_prof INT,
    FOREIGN KEY (siape_prof) REFERENCES professor(siape_prof)
);

-- Tabela de turmas
CREATE TABLE turma (
    id_turma INT PRIMARY KEY,
    curso VARCHAR(30) NOT NULL,
    serie ENUM('1º', '2º', '3º', '4º') NOT NULL,
    id_disciplina INT,
    siape_prof INT,
    FOREIGN KEY (id_disciplina) REFERENCES disciplina(id_disciplina),
    FOREIGN KEY (siape_prof) REFERENCES professor(siape_prof)
);

CREATE TABLE reposicao (
    id_repos INT AUTO_INCREMENT PRIMARY KEY,
    data_ DATETIME,
    nao_programada VARCHAR(60)
);

CREATE TABLE prof_subs (
    id_ass_subs INT AUTO_INCREMENT PRIMARY KEY,
    ass_subs VARCHAR(50),
    siape_prof INT,
    FOREIGN KEY (siape_prof) REFERENCES professor(siape_prof)
);

CREATE TABLE prof_ausente (
    id_ass_ausente INT AUTO_INCREMENT PRIMARY KEY,
    ass_ausente VARCHAR(50),
    siape_prof INT,
    FOREIGN KEY (siape_prof) REFERENCES professor(siape_prof)
);

CREATE TABLE programada (
    id_progra INT AUTO_INCREMENT PRIMARY KEY,
    dia DATE,
    horario TIME,
    autor_gov VARCHAR(30),
    id_turma INT,
    id_disciplina INT,
    id_ass_subs INT,
    id_ass_ausente INT,
    FOREIGN KEY (id_turma) REFERENCES turma(id_turma),
    FOREIGN KEY (id_disciplina) REFERENCES disciplina(id_disciplina),
    FOREIGN KEY (id_ass_subs) REFERENCES prof_subs(id_ass_subs),
    FOREIGN KEY (id_ass_ausente) REFERENCES prof_ausente(id_ass_ausente)
);

CREATE TABLE admin (
    id_adm INT AUTO_INCREMENT PRIMARY KEY,
    senha_adm VARCHAR(255) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    cargo ENUM('Coordenador', 'Diretor', 'Secretário') NOT NULL
);

CREATE TABLE relatorio (
    id_relatorio INT AUTO_INCREMENT PRIMARY KEY,
    aulas_substituidas VARCHAR(50),
    aulas_cedidas VARCHAR(50),
    siape_prof INT,
    id_repos INT,
    id_progra INT,
    id_adm INT,
    FOREIGN KEY (siape_prof) REFERENCES professor(siape_prof),
    FOREIGN KEY (id_repos) REFERENCES reposicao(id_repos),
    FOREIGN KEY (id_progra) REFERENCES programada(id_progra),
    FOREIGN KEY (id_adm) REFERENCES admin(id_adm)
);
-- Índices para performance
CREATE INDEX idx_disciplina_professor ON disciplina(siape_prof);
CREATE INDEX idx_turma_professor ON turma(siape_prof);
CREATE INDEX idx_relatorio_professor ON relatorio(siape_prof);

ALTER TABLE professor ADD senha VARCHAR(20);


ALTER TABLE admin
ADD COLUMN siape_login VARCHAR(20) UNIQUE NOT NULL AFTER id_adm;