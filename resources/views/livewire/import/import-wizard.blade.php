<div class="space-y-6">
    <!-- Progress bar -->
    <div class="glass-card">
        <div class="flex items-center gap-2 mb-2">
            @foreach([1 => '📁 Fichier', 2 => '🔧 Mappage', 3 => '👁️ Aperçu', 4 => '✅ Terminé'] as $n => $label)
            <div class="flex-1">
                <div class="h-1.5 rounded-full {{ $step >= $n ? 'bg-blue-500' : 'bg-white/10' }} transition-all duration-500"></div>
                <div class="text-xs mt-1 text-center {{ $step >= $n ? 'text-blue-400' : 'text-slate-600' }}">{{ $label }}</div>
            </div>
            @if($n < 4) <div class="w-2"></div> @endif
            @endforeach
        </div>
    </div>

    <!-- Step 1: Upload -->
    @if($step === 1)
    <div class="glass-card">
        <h2 class="text-lg font-bold text-white mb-2">Importer un relevé</h2>
        <p class="text-slate-400 text-sm mb-6">Formats supportés : CSV (RBC, TD, BMO, Scotiabank, CIBC, Desjardins, Questrade, Wealthsimple), OFX/QFX</p>
        <div class="border-2 border-dashed border-white/20 rounded-2xl p-10 text-center hover:border-blue-500/50 transition-all"
             x-data x-on:dragover.prevent x-on:drop.prevent="$wire.file = $event.dataTransfer.files[0]">
            <div class="text-4xl mb-3">📂</div>
            <p class="text-white font-medium mb-2">Glissez votre fichier ici</p>
            <p class="text-slate-500 text-sm mb-4">ou</p>
            <label class="btn-primary cursor-pointer">
                Parcourir les fichiers
                <input type="file" wire:model="file" accept=".csv,.txt,.ofx,.qfx" class="hidden">
            </label>
        </div>
        @error('file') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror

        <div wire:loading wire:target="file" class="mt-4 text-center text-blue-400 text-sm animate-pulse">Analyse du fichier...</div>

        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach(['🏦 RBC Direct', '🏦 TD WebBroker', '📈 Questrade', '💚 Wealthsimple'] as $broker)
            <div class="p-3 bg-white/5 rounded-xl text-center text-xs text-slate-400">{{ $broker }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Step 2: Mapping -->
    @if($step === 2)
    <div class="glass-card">
        <h2 class="text-lg font-bold text-white mb-1">Associer les colonnes</h2>
        <p class="text-slate-400 text-sm mb-5">Format détecté : <span class="text-blue-400 font-medium">{{ strtoupper($detectedType) }}</span> · {{ $totalRows }} lignes</p>

        <div class="space-y-4 mb-6">
            <div>
                <label class="label">Compte de destination</label>
                <select wire:model="accountId" class="input">
                    <option value="">Sélectionner un compte</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->type_icon }} {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label">Colonne Date *</label>
                    <select wire:model="mapping.date" class="input">
                        <option value="">Sélectionner...</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Colonne Montant *</label>
                    <select wire:model="mapping.amount" class="input">
                        <option value="">Sélectionner...</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Colonne Description *</label>
                    <select wire:model="mapping.description" class="input">
                        <option value="">Sélectionner...</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Preview table -->
        @if(!empty($previewRows))
        <div class="overflow-x-auto rounded-xl border border-white/10 mb-5">
            <table class="w-full text-xs">
                <thead class="bg-white/5">
                    <tr>
                        @foreach($headers as $h)
                        <th class="px-3 py-2 text-left text-slate-400">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewRows as $row)
                    <tr class="border-t border-white/5">
                        @foreach(is_array($row) ? $row : (array) $row as $cell)
                        <td class="px-3 py-2 text-slate-300">{{ $cell }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="flex gap-3">
            <button wire:click="goToStep3" class="btn-primary flex-1">Continuer →</button>
            <button wire:click="$set('step', 1)" class="btn-secondary">← Retour</button>
        </div>
    </div>
    @endif

    <!-- Step 3: Preview & Import -->
    @if($step === 3)
    <div class="glass-card">
        <h2 class="text-lg font-bold text-white mb-1">Prêt à importer</h2>
        <p class="text-slate-400 text-sm mb-5">
            <span class="text-white font-semibold">{{ $totalRows }}</span> transactions à importer.
            Les doublons seront automatiquement détectés.
        </p>
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center p-4 bg-white/5 rounded-xl">
                <div class="text-2xl font-bold text-white">{{ $totalRows }}</div>
                <div class="text-xs text-slate-400 mt-1">Total lignes</div>
            </div>
            <div class="text-center p-4 bg-emerald-500/10 rounded-xl">
                <div class="text-2xl font-bold text-emerald-400">~{{ $totalRows }}</div>
                <div class="text-xs text-slate-400 mt-1">À importer</div>
            </div>
            <div class="text-center p-4 bg-amber-500/10 rounded-xl">
                <div class="text-2xl font-bold text-amber-400">Auto</div>
                <div class="text-xs text-slate-400 mt-1">Catégorisation</div>
            </div>
        </div>
        <div class="flex gap-3">
            <button wire:click="processImport" class="btn-primary flex-1 py-3" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="processImport">🚀 Importer maintenant</span>
                <span wire:loading wire:target="processImport">⏳ Import en cours...</span>
            </button>
            <button wire:click="$set('step', 2)" class="btn-secondary">← Retour</button>
        </div>
    </div>
    @endif

    <!-- Step 4: Done -->
    @if($step === 4)
    <div class="glass-card text-center py-8">
        <div class="text-6xl mb-4">✅</div>
        <h2 class="text-2xl font-bold text-white mb-2">Import terminé !</h2>
        <div class="grid grid-cols-3 gap-4 max-w-sm mx-auto mt-6 mb-8">
            <div class="p-3 bg-emerald-500/10 rounded-xl">
                <div class="text-xl font-bold text-emerald-400">{{ $result['imported'] ?? 0 }}</div>
                <div class="text-xs text-slate-400">Importées</div>
            </div>
            <div class="p-3 bg-amber-500/10 rounded-xl">
                <div class="text-xl font-bold text-amber-400">{{ $result['duplicates'] ?? 0 }}</div>
                <div class="text-xs text-slate-400">Doublons</div>
            </div>
            <div class="p-3 bg-slate-500/10 rounded-xl">
                <div class="text-xl font-bold text-slate-400">{{ $result['skipped'] ?? 0 }}</div>
                <div class="text-xs text-slate-400">Ignorées</div>
            </div>
        </div>
        <div class="flex gap-3 justify-center">
            <a href="{{ route('budget.transactions') }}" class="btn-primary">Voir les transactions →</a>
            <button wire:click="$set('step', 1)" class="btn-secondary">Nouvel import</button>
        </div>
    </div>
    @endif
</div>
