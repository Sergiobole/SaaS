<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'src/lib/fpdf/fpdf.php';

// Converte strings para o formato que o FPDF espera (ISO-8859-1)
function to_iso($string) {
    return utf8_decode($string);
}

class PDF extends FPDF
{
    // Cabeçalho do PDF
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, to_iso('Relatório Mensal de Agendamentos'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, to_iso('Mês de ' . date('F Y')), 0, 1, 'C');
        $this->Ln(10);
    }

    // Rodapé do PDF
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, to_iso('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Tabela de agendamentos
    function AppointmentTable($header, $data)
    {
        // Cores, largura da linha e fonte em negrito
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 128, 128);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');

        // Cabeçalho da tabela
        $w = array(35, 50, 60, 25, 15);
        for($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, to_iso($header[$i]), 1, 0, 'C', true);
        $this->Ln();

        // Restauração da fonte e cores
        $this->SetFont('', '');
        $this->SetFillColor(255);
        $this->SetTextColor(0);

        // Dados
        foreach($data as $row)
        {
            $this->Cell($w[0], 6, date('d/m/Y H:i', strtotime($row['start_datetime'])), 'LR', 0, 'L');
            $this->Cell($w[1], 6, to_iso($row['client_name']), 'LR', 0, 'L');
            $this->Cell($w[2], 6, to_iso($row['service_names']), 'LR', 0, 'L');
            $this->Cell($w[3], 6, 'R$ ' . number_format($row['total_price'], 2, ',', '.'), 'LR', 0, 'R');
            
            // Status com emoji
            $status_icon = ($row['status'] == 'concluido') ? "\x52" : "\x58"; // Usando códigos de ZapfDingbats para check/cross
            $this->SetFont('ZapfDingbats', '', 10);
            $this->Cell($w[4], 6, $status_icon, 'LR', 0, 'C');
            $this->SetFont('Arial', '', 10);

            $this->Ln();
        }
        // Linha de fechamento
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// --- Lógica Principal ---
$pdo = getDbConnection();
$user_id = $_SESSION['user_id'];

// Busca os dados do mês atual
$current_month_start = date('Y-m-01 00:00:00');
$current_month_end = date('Y-m-t 23:59:59');

$stmt = $pdo->prepare(
    "SELECT 
        a.id, a.start_datetime, a.status,
        c.name as client_name,
        GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
        SUM(s.price) as total_price
    FROM appointments a
    JOIN clientes c ON a.client_id = c.id
    LEFT JOIN appointment_services as_ ON a.id = as_.appointment_id
    LEFT JOIN services s ON as_.service_id = s.id
    WHERE a.user_id = ? AND a.start_datetime BETWEEN ? AND ?
    GROUP BY a.id
    ORDER BY a.start_datetime"
);
$stmt->execute([$user_id, $current_month_start, $current_month_end]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cria o PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$header = array('Data/Hora', 'Cliente', 'Serviços', 'Valor Total', 'Status');
$pdf->AppointmentTable($header, $appointments);

$pdf->Output('I', 'relatorio_mensal_' . date('m-Y') . '.pdf');

?>