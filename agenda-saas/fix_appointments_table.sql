-- Correção para a tabela 'appointments'

-- Passo 1: Remover a restrição de chave estrangeira.
-- O nome `appointments_ibfk_3` foi extraído diretamente da sua mensagem de erro, então este comando deve funcionar.
ALTER TABLE `appointments` DROP FOREIGN KEY `appointments_ibfk_3`;

-- Passo 2: Remover a coluna 'service_id' que não é mais necessária.
-- Esta é a causa raiz do erro atual.
ALTER TABLE `appointments` DROP COLUMN `service_id`;
