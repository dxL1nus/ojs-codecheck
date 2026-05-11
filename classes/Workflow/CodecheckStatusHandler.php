<?php

namespace APP\plugins\generic\codecheck\classes\Workflow;

use Illuminate\Support\Facades\DB;

class CodecheckStatusHandler {
    public static function getCurrentStatusData(int $submissionId) {
        return DB::table('codecheck_status')
            ->where('submission_id', $submissionId)
            ->orderBy('timestamp', 'desc')
            ->orderBy('status_id', 'desc')
            ->first();
    }

    public static function getStatusDataHistory(int $submissionId) {
        return DB::table('codecheck_status')
            ->where('submission_id', $submissionId)
            ->orderByRaw('timestamp DESC NULLS LAST')
            ->orderBy('status_id', 'desc');
    }

    public static function updateStatus(int $submissionId, string $status, string $user) {
        $newRecord = [
            'submission_id' => $submissionId,
            'status' => $status,
            'timestamp' => now(),
            'user' => $user
        ];

        DB::table('codecheck_status')->insert($newRecord);
    }
}