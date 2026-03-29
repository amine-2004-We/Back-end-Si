<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject ?? 'Notification' }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 30px;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: white; margin: auto; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <tr>
        <td style="padding: 20px 30px;">
            <h2 style="color: #2c3e50;">{{ $title ?? 'Notification' }}</h2>

            <p style="font-size: 15px; color: #333;">
                Bonjour,
                <br><br>
                Une demande d’achat N°{{ $purchaseRequest->code }} requiert votre validation.
                <br><br>
                Merci de cliquer sur le lien suivant pour effectuer la validation :
                <br><br>
                <a href="{{ $validationLink }}" style="display:inline-block;padding:10px 16px;background:#2c3e50;color:#fff;text-decoration:none;border-radius:4px;">
    Accéder à la demande d’achat
</a>


            </p>

         

            <p style="margin-top: 25px; font-size: 13px; color: #888;">
                — L’équipe technique Fondation Zakoura
            </p>
        </td>
    </tr>
</table>
</body>
</html>
