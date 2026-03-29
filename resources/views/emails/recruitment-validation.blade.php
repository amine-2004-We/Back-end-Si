<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Demande de recrutement validée' }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 30px; margin: 0;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: white; margin: auto; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #27ae60;">
    <tr>
        <td style="padding: 30px;">
            <!-- En-tête -->
            <h2 style="color: #27ae60; margin: 0 0 20px 0; font-size: 20px;">
                 {{ $title ?? 'Demande de recrutement validée' }}
            </h2>

            <!-- Salutation -->
            <p style="font-size: 15px; color: #333; margin: 0 0 15px 0;">
                Bonjour,
            </p>

            <p style="font-size: 14px; color: #555; margin: 0 0 20px 0;">
                Nous vous informons que la <strong>demande de recrutement</strong> a été validée avec succès.
            </p>

            <!-- Détails de la demande -->
            <div style="background-color: #f9f9f9; border-left: 3px solid #27ae60; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="color: #2c3e50; margin: 0 0 12px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Détails de la demande</h3>

                <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 13px; color: #333;">
                    <tr>
                        <td style="font-weight: bold; color: #2c3e50; width: 35%;">N° Demande :</td>
                        <td style="color: #27ae60;"><strong>{{ $request_id }}</strong></td>
                    </tr>
                    <tr style="background-color: white;">
                        <td style="font-weight: bold; color: #2c3e50;">Poste :</td>
                        <td>{{ $position_title }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2c3e50;">Département :</td>
                        <td>{{ $department_name }}</td>
                    </tr>
                    <tr style="background-color: white;">
                        <td style="font-weight: bold; color: #2c3e50;">Nombre de postes :</td>
                        <td>{{ $number_of_positions }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2c3e50;">Date de démarrage :</td>
                        <td>{{ $desired_start_date }}</td>
                    </tr>
                </table>
            </div>

            <!-- Raison de recrutement -->
            @if($recruitment_reason && $recruitment_reason !== 'Non spécifié')
            <div style="margin: 15px 0; font-size: 13px; color: #555;">
                <strong style="color: #2c3e50;">Raison du recrutement :</strong>
                <p style="margin: 5px 0; color: #666;">{{ $recruitment_reason }}</p>
            </div>
            @endif

            <!-- Message de suivi -->
            <p style="margin-top: 25px; font-size: 13px; color: #666; line-height: 1.6;">
                La demande est maintenant en cours de traitement. Vous serez informé(e) des prochaines étapes du processus de recrutement.
            </p>

            <!-- Pied de page -->
            <p style="margin-top: 30px; font-size: 12px; color: #999; border-top: 1px solid #e0e0e0; padding-top: 15px;">
                — L'équipe Ressources Humaines<br>
                Fondation Zakoura
            </p>
        </td>
    </tr>
</table>
</body>
</html>
