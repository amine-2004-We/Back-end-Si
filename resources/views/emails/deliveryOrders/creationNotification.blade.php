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
                Votre <strong>ordre de livraison</strong> {{ $deliveryOrder->order_id }} est en cours de validation.
            </p>

         

            <p style="margin-top: 25px; font-size: 13px; color: #888;">
                — L’équipe technique Fondation Zakoura
            </p>
        </td>
    </tr>
</table>
</body>
</html>
