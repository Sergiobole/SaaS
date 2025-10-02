CREATE TABLE IF NOT EXISTS `pix_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mp_payment_id` varchar(255) NOT NULL,
  `email_cliente` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
