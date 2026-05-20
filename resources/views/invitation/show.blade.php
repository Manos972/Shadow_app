<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation — Shadow Finance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">S</div>
            <h1 class="text-2xl font-bold text-white">Shadow Finance</h1>
        </div>
        <div class="glass-card text-center">
            <div class="text-4xl mb-4">🎉</div>
            <h2 class="text-xl font-bold text-white mb-2">Vous êtes invité(e) !</h2>
            <p class="text-slate-400 mb-1">
                <strong class="text-white">{{ $invitation->inviter->name }}</strong> vous invite à rejoindre
            </p>
            <p class="text-blue-400 font-bold text-lg mb-1">{{ $invitation->team->name }}</p>
            <p class="text-xs text-slate-500 mb-6">Rôle : {{ ucfirst($invitation->role) }} · Expire {{ $invitation->expires_at->diffForHumans() }}</p>

            @auth
            <form method="POST" action="/invitation/{{ $invitation->token }}/accept">
                @csrf
                <button type="submit" class="btn-primary w-full py-3 text-base">Rejoindre l'espace</button>
            </form>
            @else
            <a href="/login?invitation={{ $invitation->token }}" class="btn-primary w-full py-3 text-base block mb-3">Se connecter pour accepter</a>
            <a href="/register?invitation={{ $invitation->token }}" class="btn-secondary w-full py-3 text-base block">Créer un compte</a>
            @endauth
        </div>
    </div>
</body>
</html>
