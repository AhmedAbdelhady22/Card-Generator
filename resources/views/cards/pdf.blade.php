<!DOCTYPE html>
<html lang="en">
<head>
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
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: #f0f0f0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Business card standard size: 3.5" x 2" (89mm x 51mm) */
        .business-card {
            width: 350px;  /* 3.5 inches at 100dpi */
            height: 200px; /* 2 inches at 100dpi */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Front side of card */
        .card-front {
            width: 100%;
            height: 100%;
            padding: 20px;
            display: flex;
            position: relative;
            color: white;
        }

        /* Left side content */
        .card-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Right side with logo/QR */
        .card-right {
            width: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between  ;
            gap: 10px;
        }

        /* Company logo */
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Name and title section */
        .name-section {
            margin-bottom: 15px;
        }

        .name {
            font-size: 18px;
            font-weight: bold;
            color: white;
            margin-bottom: 2px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .position {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.9);
            font-style: italic;
            margin-bottom: 3px;
        }

        .company {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
        }

        /* Contact information */
        .contact-info {
            font-size: 9px;
            line-height: 1.4;
            padding: 5px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
            color: rgba(255, 255, 255, 0.9);
        }

        .contact-icon {
            width: 12px;
            margin-right: 6px;
            font-size: 8px;
            text-align: center;
        }

        /* QR Code styling */
        .qr-code {
            margin-left: 50px;
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 6px;
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .qr-code svg {
            width: 100%;
            height: 100%;
        }

        /* Decorative elements */
        .card-front::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .card-front::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 65px;
            height: 65px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        /* Alternative color schemes */
        .card-theme-blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-theme-green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .card-theme-orange {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }

        .card-theme-dark {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }

        /* Print optimization */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .business-card {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }

        /* Back side of card (optional) */
        .card-back {
            width: 350px;
            height: 200px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-top: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .qr-section-back {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .qr-code-large {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 8px;
            padding: 5px;
            border: 2px solid #667eea;
        }

        .qr-code-large img,
        .qr-code-large svg {
            width: 100%;
            height: 100%;
        }

        .scan-text {
            color: #667eea;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .website-url {
            color: #666;
            font-size: 8px;
            margin-top: 5px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <!-- Front of Business Card -->
    <div class="business-card card-theme-blue">
        <div class="card-front">
            <div class="card-left">
                <!-- Name and Title -->
                <div class="name-section">
                    <div class="name">{{ $card->name }}</div>
                    @if($card->position)
                        <div class="position">{{ $card->position }}</div>
                    @endif
                    <div class="company">{{ $card->company }}</div>
                </div>

                <!-- Contact Information -->
                <div class="contact-info">
                    <div class="contact-item">
                        <span class="contact-icon">📧</span>
                        <span>{{ $card->email }}</span>
                    </div>
                    
                    @if($card->phone)
                        <div class="contact-item">
                            <span class="contact-icon">📞</span>
                            <span>{{ $card->phone }}</span>
                        </div>
                    @endif

                    @if($card->mobile)
                        <div class="contact-item">
                            <span class="contact-icon">📱</span>
                            <span>{{ $card->mobile }}</span>
                        </div>
                    @endif

                    @if($card->address)
                        <div class="contact-item">
                            <span class="contact-icon">📍</span>
                            <span>{{ $card->address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-right">
                <!-- Company Logo -->
                @if($card->logo && file_exists(public_path('storage/' . $card->logo)))
                    @php
                        $logoPath = public_path('storage/' . $card->logo);
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath);
                    @endphp
                    <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Logo" class="logo">
                @else
                    <div class="logo-placeholder">LOGO</div>
                @endif

                <!-- QR Code -->
                <div class="qr-code ">
                    @if($card->qr_code && file_exists(public_path('storage/' . $card->qr_code)))
                        @php
                            $qrPath = public_path('storage/' . $card->qr_code);
                            $qrData = base64_encode(file_get_contents($qrPath));
                            $qrMime = mime_content_type($qrPath);
                        @endphp
                        <img src="data:{{ $qrMime }};base64,{{ $qrData }}" alt="QR Code">
                    @else
                        @php
                            $cardUrl = url("/api/public/card/{$card->slug}");
                            $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                ->size(50)
                                ->margin(0)
                                ->generate($cardUrl);
                        @endphp
                        {!! $qrCodeSvg !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
