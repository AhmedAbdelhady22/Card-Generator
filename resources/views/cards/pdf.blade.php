<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://kit.fontawesome.com/be2e784e8c.js" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $card->name }} Business Card</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f8f9fa;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: #0d6efd;
            color: white;
            text-align: center;
            padding: 25px;
        }

        .card-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .card-body {
            padding: 40px;
        }

        .main-content {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }

        .left-section {
            flex: 2;
        }

        .right-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .profile-section {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
        }

        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e9ecef;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .logo-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #e9ecef;
            font-size: 32px;
            color: #6c757d;
        }

        .name-info h2 {
            color: #0d6efd;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .name-info h5 {
            color: #6c757d;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .name-info p {
            color: #6c757d;
            font-style: italic;
            margin: 0;
        }

        .contact-section h5 {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            gap: 15px;
        }

        .contact-item.full-width {
            grid-column: 1 / -1;
        }

        .icon-wrapper {
            font-size: 16px;
            width: 25px;
            height: 25px;
            text-align: center;
            font-weight: bold;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-wrapper.email { 
            background: #0d6efd; 
            color: white;
        }
        .icon-wrapper.phone { 
            background: #198754; 
            color: white;
        }
        .icon-wrapper.mobile { 
            background: #17a2b8; 
            color: white;
        }
        .icon-wrapper.address { 
            background: #dc3545; 
            color: white;
        }

        .contact-details small {
            display: block;
            color: #6c757d;
            font-size: 12px;
            margin-bottom: 2px;
        }

        .contact-details strong {
            color: #333;
            font-size: 14px;
        }

        .qr-section {
            text-align: center;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .qr-header {
            background: #0d6efd;
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .qr-header h6 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .qr-code {
            width: 180px;
            height: 180px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .qr-text {
            color: #6c757d;
            font-size: 12px;
        }

        /* Additional styles for optional fields */
        .additional-info {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
        }

        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 120px;
        }

        .info-value {
            color: #333;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .card {
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #dee2e6;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3> Business Card Details</h3>
            </div>
            <div class="card-body">
                <div class="main-content">
                    <div class="left-section">
                        <!-- Profile Section -->
                        <div class="profile-section">
                            @if($card->logo && file_exists(public_path('storage/' . $card->logo)))
                                @php
                                    $logoPath = public_path('storage/' . $card->logo);
                                    $logoData = base64_encode(file_get_contents($logoPath));
                                    $logoMime = mime_content_type($logoPath);
                                @endphp
                                <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo" class="logo">
                            @else
                                <div class="logo-placeholder">
                                    [LOGO]
                                </div>
                            @endif
                            <div class="name-info">
                                <h2>{{ $card->name }}</h2>
                                <h5>{{ $card->company }}</h5>
                                @if($card->position)
                                    <p>{{ $card->position }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="contact-section">
                            <h5> Contact Information</h5>
                            
                            <div class="contact-grid">
                                <div class="contact-item">
                                    <i class="fa-solid fa-envelope"></i>
                                    <div class="contact-details">
                                        <small>Email</small>
                                        <strong>{{ $card->email }}</strong>
                                    </div>
                                </div>

                                @if($card->phone)
                                    <div class="contact-item">
                                        <i class="fa-solid fa-phone"></i>
                                        <div class="contact-details">
                                            <small>Phone</small>
                                            <strong>{{ $card->phone }}</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($card->mobile)
                                    <div class="contact-item">
                                        <i class="fa-solid fa-mobile-phone"></i>
                                        <div class="contact-details">
                                            <small>Mobile</small>
                                            <strong>{{ $card->mobile }}</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($card->address)
                                    <div class="contact-item full-width">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <div class="contact-details">
                                            <small>Address</small>
                                            <strong>{{ $card->address }}</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($card->company_address)
                                    <div class="contact-item full-width">
                                        <i class="fa-solid fa-building"></i>
                                        <div class="contact-details">
                                            <small>Company Address</small>
                                            <strong>{{ $card->company_address }}</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="right-section">
                        @if($card->qr_code && file_exists(public_path('storage/' . $card->qr_code)))
                            <div class="qr-section">
                                <div class="qr-header">
                                    <h6> QR Code</h6>
                                </div>
                                @php
                                    $qrPath = public_path('storage/' . $card->qr_code);
                                    $qrData = base64_encode(file_get_contents($qrPath));
                                    $qrMime = mime_content_type($qrPath);
                                @endphp
                                <img src="data:{{ $qrMime }};base64,{{ $qrData }}" alt="QR Code" class="qr-code">
                                <div class="qr-text">
                                    Scan to view this card
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
