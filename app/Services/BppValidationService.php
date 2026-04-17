<?php

namespace App\Services;

use App\Models\Bpp;

class BppValidationService
{
    public function validate(Bpp $bpp): array
    {
        $passed = [];
        $warnings = [];
        $blockingIssues = [];

        $this->validatePageOneBasics($bpp, $passed, $blockingIssues);
        $this->validateAppendixConsistency($bpp, $passed, $blockingIssues);
        $this->validateTotalConsistency($bpp, $passed, $blockingIssues);
        $this->validateSupplierConsistency($bpp, $passed, $warnings, $blockingIssues);
        $this->validateExtractionConsistency($bpp, $passed, $warnings, $blockingIssues);

        $state = $this->readinessState($warnings, $blockingIssues);

        return [
            'state' => $state,
            'passed' => $passed,
            'warnings' => $warnings,
            'blocking_issues' => $blockingIssues,
            'counts' => [
                'passed' => count($passed),
                'warnings' => count($warnings),
                'blocking_issues' => count($blockingIssues),
            ],
        ];
    }

    private function validatePageOneBasics(Bpp $bpp, array &$passed, array &$blockingIssues): void
    {
        $requiredFields = [
            'b1_tajuk_perolehan' => 'B1. Tajuk Perolehan is filled in.',
            'b2_kategori_perolehan' => 'B2. Kategori Perolehan is filled in.',
            'b6_justifikasi_keperluan' => 'B6. Justifikasi Keperluan is filled in.',
        ];

        foreach ($requiredFields as $field => $message) {
            if (filled($bpp->{$field})) {
                $passed[] = $this->result($field, $message, 'passed');

                continue;
            }

            $blockingIssues[] = $this->result(
                $field,
                str_replace('is filled in.', 'is still missing.', $message),
                'blocking'
            );
        }
    }

    private function validateAppendixConsistency(Bpp $bpp, array &$passed, array &$blockingIssues): void
    {
        $activeAppendixType = $bpp->activeAppendixType();
        $activeAppendixLabel = $bpp->activeAppendixLabel();

        if (filled($bpp->b2_kategori_perolehan) && $activeAppendixType !== null) {
            $passed[] = $this->result(
                'appendix_category_match',
                'B2 category maps correctly to '.$activeAppendixLabel.'.',
                'passed'
            );
        } elseif (filled($bpp->b2_kategori_perolehan)) {
            $blockingIssues[] = $this->result(
                'appendix_category_match',
                'B2. Kategori Perolehan must be Bekalan, Perkhidmatan, or Kerja to open the correct appendix editor.',
                'blocking'
            );
        }

        if ($activeAppendixType === null) {
            return;
        }

        $matchingAppendixRowsCount = $bpp->appendixRows()
            ->where('appendix_type', $activeAppendixType)
            ->count();

        if ($matchingAppendixRowsCount > 0) {
            $passed[] = $this->result(
                'appendix_rows_present',
                $activeAppendixLabel.' contains '.$matchingAppendixRowsCount.' saved row(s).',
                'passed'
            );
        } else {
            $blockingIssues[] = $this->result(
                'appendix_rows_present',
                $activeAppendixLabel.' still needs at least one appendix row before the draft is ready for review.',
                'blocking'
            );
        }
    }

    private function validateTotalConsistency(Bpp $bpp, array &$passed, array &$blockingIssues): void
    {
        $activeAppendixType = $bpp->activeAppendixType();

        if ($activeAppendixType === null) {
            return;
        }

        $grandTotal = round(
            (float) $bpp->appendixRows()->where('appendix_type', $activeAppendixType)->sum('jumlah_harga'),
            2
        );

        if ($grandTotal === round((float) $bpp->b3_nilai_tawaran_perolehan, 2)) {
            $passed[] = $this->result(
                'appendix_total_match',
                'B3. Nilai Tawaran Perolehan matches the active appendix grand total.',
                'passed'
            );

            return;
        }

        $blockingIssues[] = $this->result(
            'appendix_total_match',
            'B3. Nilai Tawaran Perolehan does not match the active appendix grand total.',
            'blocking'
        );
    }

    private function validateSupplierConsistency(Bpp $bpp, array &$passed, array &$warnings, array &$blockingIssues): void
    {
        $supplierQuotes = $bpp->supplierQuotes()->get();

        if ($supplierQuotes->isEmpty()) {
            $warnings[] = $this->result(
                'supplier_quotes_missing',
                'No C1 supplier comparison entries have been added yet.',
                'warning'
            );

            return;
        }

        $passed[] = $this->result(
            'supplier_quotes_present',
            'C1 supplier comparison entries are saved in this draft.',
            'passed'
        );

        $selectedQuotes = $supplierQuotes->where('is_selected', true);

        if ($selectedQuotes->count() > 1) {
            $blockingIssues[] = $this->result(
                'supplier_selection_count',
                'Only one supplier may be selected as the recommended supplier.',
                'blocking'
            );

            return;
        }

        if ($selectedQuotes->count() === 0) {
            $warnings[] = $this->result(
                'supplier_selection_missing',
                'Supplier quotes exist, but no recommended supplier has been selected yet.',
                'warning'
            );

            return;
        }

        $selectedQuote = $selectedQuotes->first();

        if ((int) $selectedQuote->bpp_id !== (int) $bpp->id) {
            $blockingIssues[] = $this->result(
                'supplier_selection_scope',
                'The selected supplier does not belong to this draft.',
                'blocking'
            );

            return;
        }

        $passed[] = $this->result(
            'supplier_selection_count',
            'Exactly one recommended supplier is selected in C1.',
            'passed'
        );

        if (filled($bpp->d_nama_pembekal) && $bpp->d_nama_pembekal !== $selectedQuote->supplier_name) {
            $blockingIssues[] = $this->result(
                'supplier_d_section_match',
                'D. Nama Pembekal does not match the selected supplier in C1.',
                'blocking'
            );

            return;
        }

        $passed[] = $this->result(
            'supplier_d_section_match',
            'D. Nama Pembekal matches the selected supplier in C1.',
            'passed'
        );
    }

    private function validateExtractionConsistency(Bpp $bpp, array &$passed, array &$warnings, array &$blockingIssues): void
    {
        $review = $bpp->quotation_extraction_review;

        if (! is_array($review)) {
            $passed[] = $this->result(
                'extraction_review_state',
                'No pending quotation extraction review is waiting to be applied.',
                'passed'
            );

            return;
        }

        if (($review['valid'] ?? false) === true) {
            $warnings[] = $this->result(
                'extraction_review_pending',
                'A parsed quotation extraction review is still pending apply.',
                'warning'
            );

            return;
        }

        $errors = is_array($review['errors'] ?? null) ? $review['errors'] : [];

        if ($errors !== []) {
            $warnings[] = $this->result(
                'extraction_review_errors',
                'The latest quotation extraction review still contains parse issues: '.$errors[0],
                'warning'
            );

            return;
        }

        $blockingIssues[] = $this->result(
            'extraction_review_state',
            'Quotation extraction review data exists but is not in a valid reviewable state.',
            'blocking'
        );
    }

    private function readinessState(array $warnings, array $blockingIssues): array
    {
        if ($blockingIssues !== []) {
            return [
                'key' => 'needs_attention',
                'label' => 'Needs Attention',
                'tone' => 'rose',
                'message' => 'Blocking issues need to be fixed before this draft is ready for final review.',
            ];
        }

        if ($warnings !== []) {
            return [
                'key' => 'in_progress',
                'label' => 'In Progress',
                'tone' => 'amber',
                'message' => 'The draft is progressing well, but there are still warnings worth reviewing.',
            ];
        }

        return [
            'key' => 'ready_for_review',
            'label' => 'Ready for Review',
            'tone' => 'emerald',
            'message' => 'No blocking issues or warnings were found in the current draft data.',
        ];
    }

    private function result(string $code, string $message, string $severity): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'severity' => $severity,
        ];
    }
}
