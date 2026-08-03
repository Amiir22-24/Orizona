<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenue sur Orizona</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f5; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #f53003; margin-top: 0;">Bienvenue sur Orizona, {{ $user->full_name }} !</h2>
        <p style="color: #1b1b18; font-size: 16px; line-height: 1.5;">
            Votre compte <strong>{{ ucfirst($user->user_type) }}</strong> a été créé avec succès par l'administration.
        </p>
        <div style="background-color: #fafafa; border: 1px solid #e4e4e7; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0;"><strong>Matricule :</strong> {{ $user->matricule }}</p>
            <p style="margin: 0 0 10px 0;"><strong>Email :</strong> {{ $user->email }}</p>
            <p style="margin: 0;"><strong>Mot de passe :</strong> {{ $plainPassword }}</p>
        </div>
        <p style="color: #706f6c; font-size: 14px;">
            Nous vous recommandons de changer votre mot de passe dès votre première connexion.
        </p>
        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ url('/login') }}" style="background-color: #1b1b18; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">Se connecter</a>
        </div>
        <p style="color: #A1A09A; font-size: 12px; margin-top: 30px; text-align: center;">
            L'équipe Orizona
        </p>
    </div>
</body>
</html>
