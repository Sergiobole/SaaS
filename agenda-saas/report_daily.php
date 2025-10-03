<?php
require_once 'src/auth_check.php';
require_once 'src/database.php';
require_once 'src/lib/fpdf/fpdf.php';

// Converte strings para o formato que o FPDF espera (ISO-8859-1)
function to_iso($string) {
    return utf8_decode($string);
}

// --- Lógica para determinar a data ---
$day = $_GET['day'] ?? 'today';
$report_date = date('Y-m-d');
$report_title_date = date('d/m/Y');

if ($day === 'tomorrow') {
    $report_date = date('Y-m-d', strtotime('+1 day'));
    $report_title_date = date('d/m/Y', strtotime('+1 day'));
}

$date_start = $report_date . ' 00:00:00';
$date_end = $report_date . ' 23:59:59';

class PDF extends FPDF
{
    private $report_title_date;

    function __construct($orientation='P', $unit='mm', $size='A4', $report_title_date = '') {
        parent::__construct($orientation, $unit, $size);
        $this->report_title_date = $report_title_date;
    }

    // Cabeçalho do PDF
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, to_iso('Relatório Diário de Agendamentos'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, to_iso('Dia: ' . $this->report_title_date), 0, 1, 'C');
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
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 128, 128);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');

        $w = array(35, 50, 60, 25, 15);
        for($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, to_iso($header[$i]), 1, 0, 'C', true);
        $this->Ln();

        $this->SetFont('', '');
        $this->SetFillColor(255);
        $this->SetTextColor(0);

        foreach($data as $row)
        {
            $this->Cell($w[0], 6, date('H:i', strtotime($row['start_datetime'])), 'LR', 0, 'L');
            $this->Cell($w[1], 6, to_iso($row['client_name']), 'LR', 0, 'L');
            $this->Cell($w[2], 6, to_iso($row['service_names']), 'LR', 0, 'L');
            $this->Cell($w[3], 6, 'R$ ' . number_format($row['total_price'], 2, ',', '.'), 'LR', 0, 'R');
            
            $status_icon = ($row['status'] == 'concluido') ? "\x52" : "\x58";
            $this->SetFont('ZapfDingbats', '', 10);
            $this->Cell($w[4], 6, $status_icon, 'LR', 0, 'C');
            $this->SetFont('Arial', '', 10);

            $this->Ln();
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// --- Lógica Principal ---
$pdo = getDbConnection();
$user_id = $_SESSION['user_id'];

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
$stmt->execute([$user_id, $date_start, $date_end]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cria o PDF
$pdf = new PDF('P', 'mm', 'A4', $report_title_date);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$header = array('Hora', 'Cliente', 'Serviços', 'Valor Total', 'Status');
$pdf->AppointmentTable($header, $appointments);

$pdf->Output('I', 'relatorio_diario_' . str_replace('/', '-', $report_title_date) . '.pdf');

?>