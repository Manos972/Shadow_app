<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportService
{
    private array $keywordMap = [
        // Income keywords
        'PAIE' => ['type' => 'income', 'category' => 'Salaire'],
        'SALAIRE' => ['type' => 'income', 'category' => 'Salaire'],
        'PAYROLL' => ['type' => 'income', 'category' => 'Salaire'],
        'DEPOSIT' => ['type' => 'income', 'category' => 'Autres revenus'],
        'DIVIDENDE' => ['type' => 'income', 'category' => 'Dividendes'],
        'DIVIDEND' => ['type' => 'income', 'category' => 'Dividendes'],
        'INTÉRÊT' => ['type' => 'income', 'category' => 'Dividendes'],
        'INTEREST' => ['type' => 'income', 'category' => 'Dividendes'],
        'REMBOURSEMENT' => ['type' => 'income', 'category' => 'Remboursements'],
        'REFUND' => ['type' => 'income', 'category' => 'Remboursements'],
        // Expense keywords - Food
        'METRO' => ['type' => 'expense', 'category' => 'Alimentation'],
        'IGA' => ['type' => 'expense', 'category' => 'Alimentation'],
        'MAXI' => ['type' => 'expense', 'category' => 'Alimentation'],
        'PROVIGO' => ['type' => 'expense', 'category' => 'Alimentation'],
        'COSTCO' => ['type' => 'expense', 'category' => 'Alimentation'],
        'WALMART' => ['type' => 'expense', 'category' => 'Alimentation'],
        'LOBLAWS' => ['type' => 'expense', 'category' => 'Alimentation'],
        'SOBEYS' => ['type' => 'expense', 'category' => 'Alimentation'],
        'SUPERMARCHÉ' => ['type' => 'expense', 'category' => 'Alimentation'],
        // Restaurants
        'RESTAURANT' => ['type' => 'expense', 'category' => 'Restaurants'],
        'TIM HORTONS' => ['type' => 'expense', 'category' => 'Restaurants'],
        'MCDONALDS' => ['type' => 'expense', 'category' => 'Restaurants'],
        'STARBUCKS' => ['type' => 'expense', 'category' => 'Restaurants'],
        'DOORDASH' => ['type' => 'expense', 'category' => 'Restaurants'],
        'SKIP' => ['type' => 'expense', 'category' => 'Restaurants'],
        'UBEREATS' => ['type' => 'expense', 'category' => 'Restaurants'],
        // Transport
        'ESSO' => ['type' => 'expense', 'category' => 'Transports'],
        'PETRO' => ['type' => 'expense', 'category' => 'Transports'],
        'SHELL' => ['type' => 'expense', 'category' => 'Transports'],
        'ESSENCE' => ['type' => 'expense', 'category' => 'Transports'],
        'STM' => ['type' => 'expense', 'category' => 'Transports'],
        'OC TRANSPO' => ['type' => 'expense', 'category' => 'Transports'],
        'PRESTO' => ['type' => 'expense', 'category' => 'Transports'],
        'UBER' => ['type' => 'expense', 'category' => 'Transports'],
        // Housing
        'LOYER' => ['type' => 'expense', 'category' => 'Logement'],
        'RENT' => ['type' => 'expense', 'category' => 'Logement'],
        'HYDRO' => ['type' => 'expense', 'category' => 'Logement'],
        'BELL' => ['type' => 'expense', 'category' => 'Abonnements'],
        'ROGERS' => ['type' => 'expense', 'category' => 'Abonnements'],
        'VIDEOTRON' => ['type' => 'expense', 'category' => 'Abonnements'],
        'TELUS' => ['type' => 'expense', 'category' => 'Abonnements'],
        'NETFLIX' => ['type' => 'expense', 'category' => 'Abonnements'],
        'SPOTIFY' => ['type' => 'expense', 'category' => 'Abonnements'],
        // Health
        'PHARMACIE' => ['type' => 'expense', 'category' => 'Santé'],
        'PHARMACY' => ['type' => 'expense', 'category' => 'Santé'],
        'JEAN COUTU' => ['type' => 'expense', 'category' => 'Santé'],
        'SHOPPERS' => ['type' => 'expense', 'category' => 'Santé'],
        'CLINIQUE' => ['type' => 'expense', 'category' => 'Santé'],
        // Investments
        'QUESTRADE' => ['type' => 'expense', 'category' => 'Investissements'],
        'WEALTHSIMPLE' => ['type' => 'expense', 'category' => 'Investissements'],
        'DISNAT' => ['type' => 'expense', 'category' => 'Investissements'],
    ];

    public function parseCsv(string $content): array
    {
        $lines = array_filter(explode("\n", trim($content)));
        if (empty($lines)) return ['headers' => [], 'rows' => []];

        $headers = str_getcsv(array_shift($lines));
        $rows = [];
        foreach (array_slice($lines, 0, 5) as $line) {
            if (trim($line)) $rows[] = str_getcsv($line);
        }

        return ['headers' => $headers, 'rows' => $rows, 'total' => count($lines)];
    }

    public function parseOFX(string $content): array
    {
        $transactions = [];
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/s', $content, $matches);

        foreach ($matches[1] as $trn) {
            $transaction = [];
            foreach (['DTPOSTED', 'TRNAMT', 'NAME', 'MEMO', 'FITID'] as $field) {
                if (preg_match("/<{$field}>(.*?)(<|\n)/", $trn, $m)) {
                    $transaction[strtolower($field)] = trim($m[1]);
                }
            }
            if (isset($transaction['dtposted']) && isset($transaction['trnamt'])) {
                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }

    public function detectBrokerFormat(string $content, string $filename): string
    {
        $lower = strtolower($content . $filename);
        if (str_contains($lower, 'questrade')) return 'questrade';
        if (str_contains($lower, 'wealthsimple')) return 'wealthsimple';
        if (str_contains($lower, 'td direct') || str_contains($lower, 'webbroker')) return 'td_webbroker';
        if (str_contains($lower, 'rbc direct') || str_contains($lower, 'royal bank')) return 'rbc_direct';
        if (str_contains($lower, '<ofx>') || str_contains($lower, '<stmttrn>')) return 'ofx';
        return 'bank_csv';
    }

    public function autoCategorizeLine(string $description, float $amount): array
    {
        $upper = strtoupper($description);
        foreach ($this->keywordMap as $keyword => $category) {
            if (str_contains($upper, $keyword)) {
                return $category;
            }
        }
        return ['type' => $amount >= 0 ? 'income' : 'expense', 'category' => null];
    }

    public function importRows(array $rows, array $mapping, int $accountId, int $userId, ?int $teamId): array
    {
        $account = Account::findOrFail($accountId);
        $categories = Category::where('user_id', $userId)->get()->keyBy('name');

        $imported = 0;
        $duplicates = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            try {
                $date = $this->parseDate($row[$mapping['date']] ?? '');
                $amount = $this->parseAmount($row[$mapping['amount']] ?? '0');
                $description = trim($row[$mapping['description']] ?? 'Import');

                if (!$date || $amount == 0) { $skipped++; continue; }

                // Duplicate detection
                $exists = Transaction::where('user_id', $userId)
                    ->where('account_id', $accountId)
                    ->where('date', $date)
                    ->where('amount', abs($amount))
                    ->where('description', $description)
                    ->exists();

                if ($exists) { $duplicates++; continue; }

                $autocat = $this->autoCategorizeLine($description, $amount);
                $type = isset($mapping['type']) ? ($row[$mapping['type']] ?? $autocat['type']) : ($amount >= 0 ? 'income' : 'expense');
                $categoryId = null;
                if ($autocat['category'] && $categories->has($autocat['category'])) {
                    $categoryId = $categories[$autocat['category']]->id;
                }

                Transaction::create([
                    'user_id' => $userId,
                    'team_id' => $teamId,
                    'account_id' => $accountId,
                    'category_id' => $categoryId,
                    'type' => in_array($type, ['income', 'expense', 'transfer']) ? $type : ($amount >= 0 ? 'income' : 'expense'),
                    'amount' => abs($amount),
                    'description' => $description,
                    'date' => $date,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        return compact('imported', 'duplicates', 'skipped');
    }

    private function parseDate(string $raw): ?string
    {
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y', 'Ymd', 'd M Y', 'M d, Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, trim($raw))->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        try { return Carbon::parse(trim($raw))->format('Y-m-d'); } catch (\Exception $e) {}
        return null;
    }

    private function parseAmount(string $raw): float
    {
        $clean = preg_replace('/[^\d.,-]/', '', $raw);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }
}
