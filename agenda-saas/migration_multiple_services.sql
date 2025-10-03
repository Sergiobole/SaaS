-- SQL Migration for Multiple Services per Appointment

-- !! IMPORTANTE !!
-- Antes de executar, faça um backup do seu banco de dados.

-- Passo 1: Remover a coluna 'service_id' da tabela 'appointments'.
-- A linha para remover a chave estrangeira (FOREIGN KEY) pode falhar se o nome da sua constraint for diferente.
-- Se a primeira linha 'ALTER TABLE' falhar com um erro sobre a constraint não existir, 
-- você pode ignorar essa linha e executar apenas a segunda (DROP COLUMN). 
-- Se ainda assim der erro, você precisará encontrar o nome da constraint manualmente no seu banco de dados.

-- Tenta remover uma constraint com nome padrão.
ALTER TABLE `appointments` DROP FOREIGN KEY `appointments_ibfk_3`;

-- Remove a coluna que só permitia um serviço.
ALTER TABLE `appointments` DROP COLUMN `service_id`;


-- Passo 2: Criar a nova tabela 'appointment_services'
-- Esta tabela irá conectar os agendamentos aos seus múltiplos serviços.
CREATE TABLE `appointment_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_appointment_service` (`appointment_id`, `service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
