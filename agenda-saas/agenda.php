<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'src/csrf.php';
require_once 'templates/header.php';

// Buscar clientes e serviços para os dropdowns do modal
$pdo = getDbConnection();
$user_id = $_SESSION['user_id'];

// Clientes
$stmt_clients = $pdo->prepare("SELECT id, name FROM clientes WHERE user_id = ? ORDER BY name");
$stmt_clients->execute([$user_id]);
$clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

// Serviços
$stmt_services = $pdo->prepare("SELECT id, name, duration FROM services WHERE user_id = ? ORDER BY name");
$stmt_services->execute([$user_id]);
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

?>

<style>
    /* Ajustes para o FullCalendar */
    .fc-event {
        cursor: pointer;
    }
</style>

<h2><i class="bi bi-calendar-week"></i> Agenda</h2>
<div id='calendar'></div>

<!-- Modal para Adicionar/Editar Agendamento -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Novo Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="appointmentForm">
                    <input type="hidden" name="id" id="appointmentId">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="mb-3">
                        <label for="clientId" class="form-label">Cliente</label>
                        <select class="form-select" id="clientId" name="client_id" required>
                            <option value="">Selecione um cliente...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="serviceId" class="form-label">Serviço</label>
                        <select class="form-select" id="serviceId" name="service_id" required>
                            <option value="">Selecione um serviço...</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" data-duration="<?php echo $service['duration']; ?>">
                                    <?php echo htmlspecialchars($service['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="startDate" class="form-label">Data de Início</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="startTime" class="form-label">Hora de Início</label>
                            <input type="time" class="form-control" id="startTime" name="start_time" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="deleteAppointmentBtn" class="btn btn-danger me-auto" style="display: none;">Excluir</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="saveAppointmentBtn" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Inclui o FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const appointmentModalEl = document.getElementById('appointmentModal');
    const appointmentModal = new bootstrap.Modal(appointmentModalEl);
    const appointmentForm = document.getElementById('appointmentForm');
    const saveBtn = document.getElementById('saveAppointmentBtn');
    const deleteBtn = document.getElementById('deleteAppointmentBtn');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'pt-br',
        buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia' },
        allDaySlot: false,
        slotMinTime: '08:00:00',
        slotMaxTime: '20:00:00',
        events: 'api_get_appointments.php',
        editable: true,
        selectable: true,

        dateClick: function(info) {
            appointmentForm.reset();
            document.getElementById('appointmentId').value = '';
            document.getElementById('appointmentModalLabel').textContent = 'Novo Agendamento';
            deleteBtn.style.display = 'none';

            const startDate = new Date(info.dateStr);
            document.getElementById('startDate').value = startDate.toISOString().slice(0, 10);
            document.getElementById('startTime').value = startDate.toTimeString().slice(0, 5);

            appointmentModal.show();
        },

        eventClick: function(info) {
            appointmentForm.reset();
            const event = info.event;
            const props = event.extendedProps;

            document.getElementById('appointmentId').value = event.id;
            document.getElementById('appointmentModalLabel').textContent = 'Editar Agendamento';
            deleteBtn.style.display = 'block';

            document.getElementById('clientId').value = props.client_id;
            document.getElementById('serviceId').value = props.service_id;
            document.getElementById('notes').value = props.notes;

            const startDate = new Date(event.startStr);
            document.getElementById('startDate').value = startDate.toISOString().slice(0, 10);
            document.getElementById('startTime').value = startDate.toTimeString().slice(0, 5);

            appointmentModal.show();
        },

        eventDrop: function(info) {
            const data = {
                id: info.event.id,
                start_datetime: info.event.start.toISOString(),
                end_datetime: info.event.end.toISOString(),
                csrf_token: csrfToken
            };

            fetch('handle_edit_appointment.php', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Agendamento reagendado.', timer: 2000, showConfirmButton: false });
                } else {
                    throw new Error(result.message);
                }
            })
            .catch(error => {
                info.revert(); // Desfaz a alteração visual no calendário
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Erro ao reagendar: ' + error.message });
            });
        }
    });

    calendar.render();

    // Lógica para Salvar (Criar ou Editar)
    saveBtn.addEventListener('click', function() {
        const formData = new FormData(appointmentForm);
        const data = Object.fromEntries(formData.entries());
        const url = data.id ? 'handle_edit_appointment.php' : 'handle_add_appointment.php';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                appointmentModal.hide();
                calendar.refetchEvents();
                Swal.fire({ icon: 'success', title: 'Sucesso!', text: result.message, timer: 2000, showConfirmButton: false });
            } else {
                throw new Error(result.message);
            }
        })
        .catch(error => {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Erro ao salvar: ' + error.message });
        });
    });

    // Lógica para Excluir
    deleteBtn.addEventListener('click', function() {
        const appointmentId = document.getElementById('appointmentId').value;
        if (!appointmentId) return;

        Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const data = { id: appointmentId, csrf_token: csrfToken };
                fetch('handle_delete_appointment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        appointmentModal.hide();
                        calendar.refetchEvents();
                        Swal.fire({ icon: 'success', title: 'Excluído!', text: result.message, timer: 2000, showConfirmButton: false });
                    } else {
                        throw new Error(result.message);
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Erro ao excluir: ' + error.message });
                });
            }
        });
    });
});
</script>

<?php
require_once 'templates/footer.php';
?>
