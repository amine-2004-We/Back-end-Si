<h3>Mise à jour de votre demande de congé</h3>

{{-- <p>Bonjour {{ $recipient->first_name }} {{ $recipient->last_name }},</p> --}}

<p>Le statut de la demande de congé de {{ $item->collaborator->first_name }} {{ $item->collaborator->last_name }} a été mis à jour.</p>

<ul>
    <li>Nouveau statut : <strong>{{ $item->status }}</strong></li>
    <li>Validateur : {{ $validator->first_name }} {{ $validator->last_name }}</li>
</ul>

<p>Cordialement,<br>Application SI</p>
