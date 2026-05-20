<x-guest-layout>
    <div class="card">
        <h2 class="text-lg font-bold text-white mb-6 text-center">Créer un compte</h2>
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label">Nom</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus class="input" placeholder="Votre nom">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Adresse email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="input" placeholder="vous@exemple.com">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Mot de passe</label>
                <input type="password" name="password" required class="input" placeholder="8 caractères minimum">
                @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" required class="input" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary w-full py-3 mt-2">Créer mon compte</button>
        </form>
        <p class="text-center text-sm text-slate-500 mt-4">
            Déjà inscrit ? <a href="{{ route('login') }}" class="text-blue-400 hover:underline">Se connecter</a>
        </p>
    </div>
</x-guest-layout>
