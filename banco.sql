create table professor(
    siape_professor int primary key,
    nome varchar(20),
    id_materias varchar(10),
    id_turmas varchar(10),
    foreign key id_materias references materias (id_materias)
);

create table disciplina(
    
);