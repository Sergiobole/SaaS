<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'src/csrf.php'; // Incluído para o formulário de exclusão
require_once 'templates/header.php';

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-people"></i> Meus Clientes</h2>
    <a href="add_client.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Adicionar Novo Cliente</a>
</div>

<?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
<?php endif; ?>

<?php if (empty($clients)): ?>
    <div class="text-center p-5 mb-4 bg-light rounded-3">
        <i class="bi bi-people" style="font-size: 4rem;"></i>
        <h2 class="mt-3">Nenhum cliente cadastrado</h2>
        <p class="lead">Comece a construir sua lista de clientes para gerenciar seus agendamentos.</p>
        <a href="add_client.php" class="btn btn-primary btn-lg mt-3"><i class="bi bi-plus-circle"></i> Adicionar seu primeiro cliente</a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['name']); ?></td>
                            <td><?php echo htmlspecialchars($client['phone']); ?></td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#emailModal" 
                                            data-client-email="<?php echo htmlspecialchars($client['email']); ?>" 
                                            data-client-name="<?php echo htmlspecialchars($client['name']); ?>" 
                                            title="Enviar Email">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#whatsappModal" 
                                            data-client-phone="<?php echo htmlspecialchars($client['phone']); ?>" 
                                            data-client-name="<?php echo htmlspecialchars($client['name']); ?>" 
                                            title="Chamar no WhatsApp" <?php echo empty($client['phone']) ? 'disabled' : ''; ?> >
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                    <a href="edit_client.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="delete_client.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita.');">
                                        <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Modal de Email -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailModalLabel">Enviar Email</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="handle_send_email.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <input type="hidden" name="client_name" id="modalClientName">
            <div class="mb-3">
                <label for="modalClientEmail" class="form-label">Para:</label>
                <input type="email" class="form-control" id="modalClientEmail" name="client_email" readonly>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Assunto:</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="body" class="form-label">Mensagem:</label>
                <textarea class="form-control" id="body" name="body" rows="10"></textarea>
            </div>
            <div class="mb-3">
                <label for="attachment" class="form-label">Anexo:</label>
                <input class="form-control" type="file" id="attachment" name="attachment">
            </div>
            <div class="mb-3">
                <button type="button" class="btn btn-secondary btn-sm" id="insertSignatureBtn">Inserir Assinatura Eletrônica</button>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Enviar Email</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de WhatsApp -->
<div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="whatsappModalLabel">Enviar WhatsApp para <strong id="whatsappClientName"></strong></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="whatsappMessage" class="form-label">Mensagem:</label>
            <textarea class="form-control" id="whatsappMessage" rows="8"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="sendWhatsappBtn"><i class="bi bi-whatsapp"></i> Abrir e Enviar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica para Modal de Email ---
    const emailModal = document.getElementById('emailModal');
    if (emailModal) {
        const signatureButton = document.getElementById('insertSignatureBtn');
        const emailBody = document.getElementById('body');

        emailModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const clientEmail = button.getAttribute('data-client-email');
            const clientName = button.getAttribute('data-client-name');
            emailModal.querySelector('#modalClientEmail').value = clientEmail;
            emailModal.querySelector('#modalClientName').value = clientName;
        });

        signatureButton.addEventListener('click', function() {
            const businessName = "<?php echo htmlspecialchars($_SESSION['business_name'], ENT_QUOTES); ?>";
            const userName = "<?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES); ?>";
            const now = new Date();
            const date = now.toLocaleDateString('pt-BR');
            const time = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            const signature = `\n\n-- \n${businessName} - ${userName}\n${date} / ${time}`;
            emailBody.value += signature;
            emailBody.focus();
        });
    }

    // --- Lógica para Modal de WhatsApp ---
    const whatsappModal = document.getElementById('whatsappModal');
    if (whatsappModal) {
        const sendWhatsappBtn = document.getElementById('sendWhatsappBtn');

        whatsappModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const clientName = button.getAttribute('data-client-name');
            const clientPhone = button.getAttribute('data-client-phone');
            
            sendWhatsappBtn.setAttribute('data-phone-number', clientPhone.replace(/\D/g, ''));
            whatsappModal.querySelector('#whatsappClientName').textContent = clientName;
            whatsappModal.querySelector('#whatsappMessage').value = '';
        });

        sendWhatsappBtn.addEventListener('click', function() {
            const phoneNumber = sendWhatsappBtn.getAttribute('data-phone-number');
            const message = document.getElementById('whatsappMessage').value;

            if (phoneNumber) {
                const encodedMessage = encodeURIComponent(message);
                const url = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
                window.open(url, '_blank');
            } else {
                alert('Número de telefone não encontrado.');
            }
        });
    }
});
</script>

<?php
require_once 'templates/footer.php';
?>
