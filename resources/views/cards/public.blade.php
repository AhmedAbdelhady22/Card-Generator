<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $card->name }} - Business Card</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 1rem;
        }
        .business-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .business-card:hover {
            transform: translateY(-5px);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-container img {
            max-width: 120px;
            max-height: 120px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-name {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .card-position {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 0.3rem;
        }
        .card-company {
            font-size: 1.1rem;
            color: #3498db;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .contact-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        .contact-item:hover {
            background: #e3f2fd;
        }
        .contact-item:last-child {
            margin-bottom: 0;
        }
        .contact-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }
        .contact-text {
            flex: 1;
        }
        .powered-by {
            text-align: center;
            margin-top: 2rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card-container">
            <div class="business-card">
                @if($card->logo)
                    <div class="logo-container">
                        <img src="{{ $card->logo_url }}" alt="{{ $card->company }} Logo" class="img-fluid">
                    </div>
                @endif
                
                <div class="text-center">
                    <h1 class="card-name">{{ $card->name }}</h1>
                    @if($card->position)
                        <p class="card-position">{{ $card->position }}</p>
                    @endif
                    @if($card->company)
                        <p class="card-company">{{ $card->company }}</p>
                    @endif
                </div>

                <div class="contact-info">
                    @if($card->email)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Email:</strong><br>
                                <a href="mailto:{{ $card->email }}" class="text-decoration-none">{{ $card->email }}</a>
                            </div>
                        </div>
                    @endif

                    @if($card->phone)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Phone:</strong><br>
                                <a href="tel:{{ $card->phone }}" class="text-decoration-none">{{ $card->phone }}</a>
                            </div>
                        </div>
                    @endif

                    @if($card->mobile)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Mobile:</strong><br>
                                <a href="tel:{{ $card->mobile }}" class="text-decoration-none">{{ $card->mobile }}</a>
                            </div>
                        </div>
                    @endif

                    @if($card->address)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Address:</strong><br>
                                {{ $card->address }}
                            </div>
                        </div>
                    @endif

                    @if($card->company_address)
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Company Address:</strong><br>
                                {{ $card->company_address }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="text-center mt-4">
                    <p class="mb-0 text-muted">
                        <i class="fas fa-qrcode me-2"></i>
                        Scan QR code to view this card
                    </p>
                </div>
            </div>

            <div class="powered-by">
                <i class="fas fa-id-card me-2"></i>
                Powered by Card Generator
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
