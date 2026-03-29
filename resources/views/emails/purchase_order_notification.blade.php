<!DOCTYPE html>
<html>
<head>
    <title>Notification Bon de commande</title>
</head>
<body>
<p>{{ $message }}</p>

<p>Détails du bon de commande :</p>
<ul>
    <li>ID : {{ $purchaseOrder->id }}</li>
    <li>Fournisseur : {{ $purchaseOrder->supplier->trade_name ?? '-' }}</li>
    <li>Statut : {{ $purchaseOrder->status }}</li>
</ul>
</body>
</html>
