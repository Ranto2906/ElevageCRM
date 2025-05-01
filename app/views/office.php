<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BarberShop Office</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #f8fafc 0%, #e0e7ef 100%);
            min-height: 100vh;
        }
        .office-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            gap: 2rem;
        }
        .office-card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
            width: 260px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .office-card:hover {
            transform: translateY(-10px) scale(1.04) rotate(-1deg);
            box-shadow: 0 16px 40px 0 rgba(31, 38, 135, 0.22);
        }
        .office-card .icon {
            font-size: 3.5rem;
            color: #3578e5;
            margin-bottom: 1.2rem;
            transition: color 0.3s;
        }
        .office-card:hover .icon {
            color: #ff9800;
            animation: bounce 0.7s;
        }
        @keyframes bounce {
            0% { transform: scale(1); }
            30% { transform: scale(1.2); }
            50% { transform: scale(0.95); }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .office-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.7rem;
            color: #222;
        }
        .office-card p {
            color: #666;
            font-size: 1rem;
            margin-bottom: 0;
        }
        @media (max-width: 900px) {
            .office-container { flex-direction: column; gap: 2.5rem; }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5" style="font-weight:700; letter-spacing:1px; color:#222;">BarberShop Office</h1>
        <div class="office-container">
            <a href="client" class="office-card" style="text-decoration:none;">
                <div class="icon"><i class="fa-solid fa-users"></i></div>
                <h3>Clients</h3>
                <p>Gérez et analysez vos clients, leur fidélité et leurs préférences.</p>
            </a>
            <a href="action-client" class="office-card" style="text-decoration:none;">
                <div class="icon"><i class="fa-solid fa-scissors"></i></div>
                <h3>Actions</h3>
                <p>Suivez les actions réalisées, leur fréquence et leur impact.</p>
            </a>
            <a href="reaction-client" class="office-card" style="text-decoration:none;">
                <div class="icon"><i class="fa-solid fa-bolt"></i></div>
                <h3>Réactions</h3>
                <p>Analysez les réactions, leur efficacité et leur influence sur la clientèle.</p>
            </a>
        </div>
    </div>
</body>
</html>
