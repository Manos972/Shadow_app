<x-guest-layout>
    <div class="card">
        <h2 class="text-lg font-bold text-white mb-6 text-center">Connexion</h2>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label">Adresse email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="input" placeholder="vous@exemple.com">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Mot de passe</label>
                <input type="password" name="password" required class="input" placeholder="••••••••">
                @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded bg-dark-700 border-dark-600">
                <label for="remember" class="text-sm text-slate-400 cursor-pointer">Se souvenir de moi</label>
            </div>
            <button type="submit" class="btn-primary w-full py-3 mt-2">Se connecter</button>
        </form>
        <p class="text-center text-sm text-slate-500 mt-4">
            Pas de compte ? <a href="{{ route('register') }}" class="text-blue-400 hover:underline">Créer un compte</a>
        </p>
        <div class="mt-4 p-3 bg-dark-900 rounded-xl text-xs text-slate-500 text-center">
            Démo : <span class="text-slate-300">demo@shadow.app</span> / <span class="text-slate-300">password</span>
        </div>
    </div>
</x-guest-layout>
