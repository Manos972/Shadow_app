<?php
namespace App\Livewire\Import;

use App\Models\Account;
use App\Models\Import;
use App\Services\ImportService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public $file = null;
    public string $detectedType = '';
    public array $headers = [];
    public array $previewRows = [];
    public int $totalRows = 0;
    public string $accountId = '';
    public array $mapping = [
        'date' => '',
        'amount' => '',
        'description' => '',
    ];
    public ?Import $importRecord = null;
    public array $result = [];
    public bool $loading = false;

    protected $rules = [
        'file' => 'required|file|max:10240|mimes:csv,txt,ofx,qfx',
        'accountId' => 'required|exists:accounts,id',
        'mapping.date' => 'required',
        'mapping.amount' => 'required',
        'mapping.description' => 'required',
    ];

    public function updatedFile(): void
    {
        $this->validate(['file' => 'required|file|max:10240']);
        $content = file_get_contents($this->file->getRealPath());
        $service = app(ImportService::class);

        $this->detectedType = $service->detectBrokerFormat($content, $this->file->getClientOriginalName());

        if (str_contains($this->detectedType, 'ofx') || str_contains($this->detectedType, 'qfx') || str_contains($content, '<OFX>')) {
            $this->headers = ['date', 'amount', 'description'];
            $rows = $service->parseOFX($content);
            $this->previewRows = array_slice($rows, 0, 5);
            $this->totalRows = count($rows);
            $this->mapping = ['date' => 'dtposted', 'amount' => 'trnamt', 'description' => 'name'];
        } else {
            $parsed = $service->parseCsv($content);
            $this->headers = $parsed['headers'];
            $this->previewRows = $parsed['rows'];
            $this->totalRows = $parsed['total'];
            $this->autoDetectMapping();
        }

        $this->step = 2;
    }

    private function autoDetectMapping(): void
    {
        foreach ($this->headers as $header) {
            $lower = strtolower(trim($header));
            if (in_array($lower, ['date', 'date transaction', 'date comptable', 'posting date', 'transaction date'])) {
                $this->mapping['date'] = $header;
            }
            if (in_array($lower, ['montant', 'amount', 'débit/crédit', 'debit/credit', 'transaction amount'])) {
                $this->mapping['amount'] = $header;
            }
            if (in_array($lower, ['description', 'libellé', 'memo', 'narration', 'transaction description'])) {
                $this->mapping['description'] = $header;
            }
        }
    }

    public function goToStep3(): void
    {
        $this->validate([
            'accountId' => 'required|exists:accounts,id',
            'mapping.date' => 'required',
            'mapping.amount' => 'required',
            'mapping.description' => 'required',
        ]);
        $this->step = 3;
    }

    public function processImport(): void
    {
        $this->loading = true;
        $content = file_get_contents($this->file->getRealPath());
        $service = app(ImportService::class);

        if (str_contains($this->detectedType, 'ofx')) {
            $rows = $service->parseOFX($content);
        } else {
            $lines = array_filter(explode("\n", trim($content)));
            array_shift($lines);
            $headers = null;
            $parsedHeaders = str_getcsv(reset($lines));
            $rows = [];
            foreach ($lines as $line) {
                if (trim($line)) {
                    $vals = str_getcsv($line);
                    if (!$headers) { $headers = array_keys($parsedHeaders); }
                    $rows[] = array_combine($parsedHeaders, array_pad($vals, count($parsedHeaders), ''));
                }
            }
        }

        $this->result = $service->importRows(
            $rows,
            $this->mapping,
            (int) $this->accountId,
            auth()->id(),
            auth()->user()->current_team_id
        );

        $this->step = 4;
        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.import.import-wizard', [
            'accounts' => auth()->user()->accounts()->where('is_active', true)->get(),
        ]);
    }
}
