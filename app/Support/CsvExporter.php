<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

final class CsvExporter
{
    /**
     * Stream CSV file as a download response.
     *
     * @param string $fileName
     * @param callable $writer function(\SplFileObject $output): void
     */
    public static function stream(string $fileName, callable $writer): StreamedResponse
    {
        return response()->streamDownload(
            static function () use ($writer): void {
                $output = new \SplFileObject('php://output', 'w');
                $writer($output);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }
}
