<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\calendario.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logado'])) {
    $_SESSION['login_error'] = "Acesso negado. Faça login para visualizar o calendário.";
    header("Location: login.php");
    exit();
}

$siape_logado = $_SESSION['usuario_logado'];
$nome_logado = $_SESSION['nome_usuario_logado'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário de Reposições</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f7f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            box-sizing: border-box;
            text-align: center;
        }
        h1 {
            color: #386641;
            margin-bottom: 25px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header button {
            background-color: #386641;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .header button:hover {
            background-color: #2a5133;
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .day-name {
            background-color: #e0e0e0;
            padding: 10px;
            font-weight: bold;
            text-align: center;
        }
        .day {
            min-height: 100px;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            position: relative;
        }
        .day.empty {
            background-color: #f9f9f9;
        }
        .day-number {
            font-weight: bold;
            font-size: 1.2em;
            color: #555;
            margin-bottom: 5px;
        }
        .event {
            padding: 5px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-top: 5px;
            cursor: pointer;
            word-wrap: break-word;
        }
        .event-reposicao {
            background-color: #2a9d8f;
            color: white;
        }
        .event-falta {
            background-color: #dc3545;
            color: white;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #2a9d8f;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #268074;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            text-align: left;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .empty-message {
            text-align: center;
            padding: 20px;
            color: #777;
            font-style: italic;
        }
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        .btn-pegar-reposicao {
            background-color: #386641;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-pegar-reposicao:hover {
            background-color: #2a5133;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Calendário de Reposições</h1>
    
    <div class="header">
        <button id="prev-month">Mês Anterior</button>
        <h2 id="current-month-year"></h2>
        <button id="next-month">Próximo Mês</button>
    </div>

    <div class="calendar" id="calendar">
        <div class="day-name">Dom</div>
        <div class="day-name">Seg</div>
        <div class="day-name">Ter</div>
        <div class="day-name">Qua</div>
        <div class="day-name">Qui</div>
        <div class="day-name">Sex</div>
        <div class="day-name">Sáb</div>
    </div>
    
    <div id="no-events-message" class="empty-message" style="display: none;">
        Não há eventos agendados para o mês atual.
    </div>

    <a href="principal.php" class="back-link">← Voltar ao Dashboard</a>
</div>

<div id="reposition-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Detalhes do Evento</h2>
        <p><strong>Tipo:</strong> <span id="modal-type"></span></p>
        <p><strong>Data:</strong> <span id="modal-date"></span></p>
        <p><strong>Horário:</strong> <span id="modal-time"></span></p>
        <p><strong>Professor Ausente:</strong> <span id="modal-absent-prof"></span></p>
        <p><strong>Professor Substituto:</strong> <span id="modal-sub-prof"></span></p>
        <p><strong>Disciplina:</strong> <span id="modal-subject"></span></p>
        <p><strong>Turma:</strong> <span id="modal-class"></span></p>
        <div class="modal-footer">
            <button id="btn-pegar-reposicao" class="btn-pegar-reposicao" style="display: none;">Pegar Reposição</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    const monthYearEl = document.getElementById('current-month-year');
    const prevBtn = document.getElementById('prev-month');
    const nextBtn = document.getElementById('next-month');
    const modal = document.getElementById('reposition-modal');
    const closeModal = document.querySelector('.close');
    const noEventsMessageEl = document.getElementById('no-events-message');
    const btnPegarReposicao = document.getElementById('btn-pegar-reposicao');

    const siapeLogado = '<?php echo $siape_logado; ?>';
    const nomeLogado = '<?php echo addslashes($nome_logado); ?>';

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let eventosData = [];

    const fetchEventos = () => {
        fetch('../../api/reposicoes.php')
            .then(response => {
                console.log('Resposta da API recebida:', response);
                if (!response.ok) {
                    throw new Error(`Erro de rede: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Dados da API (JSON):', data);
                if (data.success) {
                    eventosData = data.data;
                    renderCalendar();
                } else {
                    console.error('Erro ao carregar os dados de eventos:', data.message);
                }
            })
            .catch(error => {
                console.error('Erro na requisição da API:', error);
                noEventsMessageEl.textContent = "Ocorreu um erro ao carregar os eventos. Verifique o console.";
                noEventsMessageEl.style.display = 'block';
            });
    };

    const renderCalendar = () => {
        calendarEl.innerHTML = '';
        noEventsMessageEl.style.display = 'none';

        const daysOfWeek = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        daysOfWeek.forEach(day => {
            const dayNameEl = document.createElement('div');
            dayNameEl.classList.add('day-name');
            dayNameEl.textContent = day;
            calendarEl.appendChild(dayNameEl);
        });

        const date = new Date(currentYear, currentMonth, 1);
        const monthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        monthYearEl.textContent = `${monthNames[currentMonth]} ${currentYear}`;

        const firstDayIndex = date.getDay();
        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('day', 'empty');
            calendarEl.appendChild(emptyDay);
        }

        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        let hasEventsInMonth = false;

        for (let day = 1; day <= daysInMonth; day++) {
            const dayEl = document.createElement('div');
            dayEl.classList.add('day');

            const dayNumberEl = document.createElement('div');
            dayNumberEl.classList.add('day-number');
            dayNumberEl.textContent = day;
            dayEl.appendChild(dayNumberEl);

            const formattedDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

            const eventosDoDia = eventosData.filter(evento => evento.dia === formattedDate);
            if (eventosDoDia.length > 0) {
                hasEventsInMonth = true;
                eventosDoDia.forEach(evento => {
                    const eventEl = document.createElement('div');
                    eventEl.classList.add('event');

                    if (evento.nome_substituto === null) {
                        eventEl.classList.add('event-falta');
                        eventEl.textContent = `Falta programada: ${evento.horario.substring(0, 5)}`;
                    } else {
                        eventEl.classList.add('event-reposicao');
                        eventEl.textContent = `Reposição: ${evento.horario.substring(0, 5)}`;
                    }

                    eventEl.addEventListener('click', () => showModal(evento));
                    dayEl.appendChild(eventEl);
                });
            }
            calendarEl.appendChild(dayEl);
        }
        
        if (!hasEventsInMonth) {
            noEventsMessageEl.style.display = 'block';
            noEventsMessageEl.textContent = `Não há eventos agendados para ${monthNames[currentMonth]} de ${currentYear}.`;
        }
    };

    const showModal = (evento) => {
        document.getElementById('modal-type').textContent = evento.nome_substituto === null ? 'Falta Programada' : 'Reposição de Aula';
        document.getElementById('modal-date').textContent = evento.dia;
        document.getElementById('modal-time').textContent = evento.horario.substring(0, 5);
        
        // Exibir o nome do professor ausente e substituto
        document.getElementById('modal-absent-prof').textContent = evento.nome_ausente || 'N/A';
        document.getElementById('modal-sub-prof').textContent = evento.nome_substituto || 'Não há';
        
        document.getElementById('modal-subject').textContent = evento.nome_disciplina;
        document.getElementById('modal-class').textContent = `${evento.curso} - ${evento.serie}`;
        modal.style.display = 'block';
        
        // Lógica para mostrar/esconder o botão "Pegar Reposição"
        // O botão aparece apenas se for uma falta programada E o professor ausente não for o professor logado
        if (evento.nome_substituto === null && evento.siape_ausente !== siapeLogado) {
            btnPegarReposicao.style.display = 'block';
            btnPegarReposicao.onclick = () => pegarReposicao(evento.id_progra);
        } else {
            btnPegarReposicao.style.display = 'none';
        }
    };

    const pegarReposicao = (id_progra) => {
        if (!confirm('Tem certeza que deseja pegar esta reposição?')) {
            return;
        }

        const data = new URLSearchParams();
        data.append('id_progra', id_progra);
        data.append('siape_substituto', siapeLogado);
        data.append('nome_substituto', nomeLogado);

        fetch('../../api/pegar_reposicao.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Reposição pega com sucesso!');
                modal.style.display = 'none';
                fetchEventos(); // Atualiza o calendário
            } else {
                alert('Erro ao pegar reposição: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro na requisição. Verifique o console.');
        });
    };

    prevBtn.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    });

    nextBtn.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    });

    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    fetchEventos();
});
</script>

</body>
</html>