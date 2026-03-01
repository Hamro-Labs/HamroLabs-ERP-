<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReportController
{
    /**
     * Generate and download report
     */
    public function generate(Request $request)
    {
        $type = $request->input('type', 'revenue');
        $format = $request->input('format', 'excel');
        $start = $request->input('start');
        $end = $request->input('end');

        // Ensure temporary storage directory exists
        $reportsDir = storage_path('app/public/reports');
        if (!file_exists($reportsDir)) {
            mkdir($reportsDir, 0777, true);
        }

        $filename = "report_{$type}_" . date('Ymd_His') . ($format == 'excel' ? '.xlsx' : '.pdf');
        $outputPath = $reportsDir . DIRECTORY_SEPARATOR . $filename;
        $publicUrl = asset('storage/reports/' . $filename);

        // Path to python script and virtualenv
        $pythonScript = base_path('python/reports/super_admin_reports.py');
        $pythonExe = base_path('venv/Scripts/python.exe');

        // Construct command
        $cmd = "\"$pythonExe\" \"$pythonScript\" --type $type --format $format --output \"$outputPath\"";
        if ($start) $cmd .= " --start $start";
        if ($end) $cmd .= " --end $end";

        // Execute command
        $output = [];
        $returnVar = 0;
        
        exec($cmd . " 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error("Report generation failed: " . implode("\n", $output));
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report.',
                'error' => implode("\n", $output)
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully.',
            'url' => $publicUrl,
            'filename' => $filename
        ]);
    }
}
