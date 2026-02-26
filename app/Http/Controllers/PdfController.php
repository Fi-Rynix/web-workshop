<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function generatePdf()
    {
        $pdf = Pdf::loadView('pdf.generate-pdf');
        $pdf->setPaper('A4');
        return $pdf->download('document.pdf');
    }
}


