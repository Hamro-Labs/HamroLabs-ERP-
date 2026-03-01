<?php
include('config/dbcon.php');
require_once('tcpdf/tcpdf.php');

$id = $_GET['id'];

$issue = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM issue_slips WHERE id=$id"));
$items = mysqli_query($con, "SELECT * FROM issue_items WHERE issue_id=$id");

// $pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);

$pdf->Cell(0, 10, 'Birgunj Institute of Technology', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 6, 'Issue Slip', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(95, 6, "Purpose: " . $issue['purpose'], 0, 0);
$pdf->Cell(95, 6, "Date: " . $issue['issue_date'], 0, 1);
$pdf->Cell(95, 6, "Faculty: " . $issue['faculty'], 0, 0);
$pdf->Cell(95, 6, "Year/Part: " . $issue['year_part'], 0, 1);
$pdf->Ln(4);

// Table
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(10, 8, 'SN', 1);
$pdf->Cell(50, 8, 'Item', 1);
$pdf->Cell(40, 8, 'Specification', 1);
$pdf->Cell(20, 8, 'Qty', 1);
$pdf->Cell(60, 8, 'Remarks', 1);
$pdf->Ln();

$pdf->SetFont('helvetica', '', 10);
$sn = 1;
while ($row = mysqli_fetch_assoc($items)) {
    $pdf->Cell(10, 8, $sn++, 1);
    $pdf->Cell(50, 8, $row['item_name'], 1);
    $pdf->Cell(40, 8, $row['specification'], 1);
    $pdf->Cell(20, 8, $row['quantity'], 1);
    $pdf->Cell(60, 8, $row['remarks'], 1);
    $pdf->Ln();
}

$pdf->Ln(10);
$pdf->Cell(95, 6, "Receiver: " . $issue['receiver_name'], 0, 0);
$pdf->Cell(95, 6, "Instructor: " . $issue['instructor_name'], 0, 1);

$pdf->Output("issue_slip_$id.pdf", "I");
?>