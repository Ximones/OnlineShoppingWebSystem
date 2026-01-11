<?php $title = 'Scan QR Code - Staff Portal'; ?>

<!-- Include html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<section class="panel">
    <h2>Scan QR Code</h2>
    
    <p style="color: var(--color-text-muted); margin-bottom: 2rem;">
        Use your camera to scan the QR code or manually enter the token below
    </p>

    <!-- Camera Scanner Section -->
    <div id="scanner-container" style="display: none; margin-bottom: 2rem;">
        <div class="form-section">
            <h3 class="form-section-title">
                <i class="fas fa-camera" style="margin-right: 0.5rem; color: var(--color-accent);"></i>
                Camera Scanner
            </h3>
            <p style="color: var(--color-text-muted); margin-bottom: 1rem;">
                Point your camera at the QR code
            </p>
            <div id="qr-reader"></div>
            <div id="scanner-error" class="scanner-error" style="display: none;"></div>
            <div style="text-align: center; margin-top: 1rem;">
                <button type="button" id="stop-scanner-btn" class="btn secondary" style="display: none;">
                    <i class="fas fa-stop"></i> Stop Scanner
                </button>
            </div>
        </div>
    </div>

    <!-- Manual Input Form -->
    <form method="post" action="?module=pickup&action=lookup" id="token-form">
        <div class="form-section">
            <h3 class="form-section-title">QR Code Token</h3>
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <button type="button" id="start-scanner-btn" class="btn primary">
                    <i class="fas fa-camera"></i> Start Camera Scanner
                </button>
            </div>

            <label for="token">
                Token
                <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                    Scan QR code or enter token manually (e.g., ORD123_...)
                </small>
            </label>
            <input 
                type="text" 
                id="token" 
                name="token" 
                placeholder="Scan QR code or enter token" 
                required
                autocomplete="off"
                style="font-family: 'Courier New', monospace; letter-spacing: 0.05em;"
                onpaste="this.value = this.value.trim()"
            >
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn primary" style="flex: 1;">
                <i class="fas fa-search"></i>
                Lookup Order
            </button>
            <a href="?module=shop&action=home" class="btn secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </form>
</section>

<style>
#qr-reader {
    position: relative;
    min-height: 300px;
    background: #000;
    border-radius: var(--radius-xs);
    overflow: hidden;
}

#qr-reader__dashboard {
    display: none !important;
}

#qr-reader__scan_region {
    border-radius: var(--radius-xs);
    overflow: hidden;
    position: relative;
}

#qr-reader__scan_region video {
    width: 100% !important;
    height: auto !important;
    max-height: 500px;
    min-height: 250px;
    object-fit: cover;
    display: block;
    background: #000;
}

#qr-reader__scan_region canvas {
    width: 100% !important;
    height: auto !important;
    max-height: 500px;
    min-height: 250px;
    object-fit: cover;
    display: block;
}

#qr-reader__camera_selection {
    display: none;
}

/* Scanner overlay - scanning frame */
#qr-reader__scan_region::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 250px;
    height: 250px;
    border: 3px solid #0071e3;
    border-radius: var(--radius-xs);
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.6);
    pointer-events: none;
    z-index: 10;
    animation: scannerPulse 2s ease-in-out infinite;
}

@keyframes scannerPulse {
    0%, 100% {
        opacity: 1;
        border-color: #0071e3;
    }
    50% {
        opacity: 0.7;
        border-color: #34c759;
    }
}

/* Corner indicators */
#qr-reader__scan_region::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 250px;
    height: 250px;
    pointer-events: none;
    z-index: 11;
    border: 2px solid transparent;
    border-top-color: #0071e3;
    border-left-color: #0071e3;
    border-radius: var(--radius-xs);
}

@media (max-width: 640px) {
    #qr-reader {
        min-height: 250px;
    }
    
    #qr-reader__scan_region::before,
    #qr-reader__scan_region::after {
        width: 200px;
        height: 200px;
    }
    
    #qr-reader__scan_region video {
        max-height: 400px;
    }
}

/* Error message styling */
.scanner-error {
    background: rgba(255, 59, 48, 0.1);
    border: 1px solid #ff3b30;
    border-radius: var(--radius-xs);
    padding: 1rem;
    margin-top: 1rem;
    text-align: center;
    color: #ff3b30;
    font-size: 0.9rem;
}
</style>

<script>
(function() {
    let html5QrCode = null;
    let isScanning = false;
    const scannerContainer = document.getElementById('scanner-container');
    const startBtn = document.getElementById('start-scanner-btn');
    const stopBtn = document.getElementById('stop-scanner-btn');
    const tokenInput = document.getElementById('token');
    const tokenForm = document.getElementById('token-form');

    // Check if camera is available (including legacy support)
    function checkCameraSupport() {
        // Check for modern API
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            return true;
        }
        // Check for legacy API (older browsers)
        if (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia) {
            return true;
        }
        return false;
    }

    // Show error message
    function showScannerError(message) {
        const errorDiv = document.getElementById('scanner-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }

    // Hide error message
    function hideScannerError() {
        const errorDiv = document.getElementById('scanner-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    // Start scanner
    function startScanner() {
        // Check if HTTPS is required (camera access needs HTTPS on mobile except localhost)
        const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1' || location.hostname.startsWith('192.168.') || location.hostname.startsWith('10.');
        
        if (!checkCameraSupport()) {
            let errorMsg = 'Camera API is not available. ';
            if (!isSecureContext) {
                errorMsg += 'Camera access requires HTTPS on mobile browsers. Please access this page over HTTPS. ';
            }
            errorMsg += 'You can still use manual input.';
            alert(errorMsg);
            showScannerError(errorMsg);
            return;
        }
        
        // Warn but allow on HTTP for local networks (some browsers still allow it)
        if (!isSecureContext && !location.hostname.startsWith('192.168.') && !location.hostname.startsWith('10.')) {
            console.warn('Camera access may not work over HTTP. HTTPS is recommended.');
        }

        if (isScanning) {
            return;
        }

        // Show loading state
        startBtn.disabled = true;
        startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting Camera...';

        // Show scanner container first
        scannerContainer.style.display = 'block';
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';
        hideScannerError();

        // Initialize html5-qrcode
        html5QrCode = new Html5Qrcode("qr-reader");
        isScanning = true;

        // Determine optimal QR box size based on screen width
        const screenWidth = window.innerWidth;
        const qrboxSize = screenWidth <= 640 ? 200 : 250;

        const config = {
            fps: 10,
            qrbox: { width: qrboxSize, height: qrboxSize },
            aspectRatio: 1.0,
            disableFlip: false
        };

        // Error handler function
        function handleCameraError(err) {
            // Reset button state
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fas fa-camera"></i> Start Camera Scanner';
            cleanupScanner();
            
            // Handle specific error types
            let errorMessage = 'Unable to access camera. ';
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError' || (err.message && err.message.includes('Permission denied'))) {
                errorMessage = 'Camera permission denied. Please allow camera access in your browser settings and try again.';
                showScannerError(errorMessage);
                alert('Camera permission is required to scan QR codes. Please allow camera access in your browser settings and try again.');
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError' || (err.message && err.message.includes('No camera'))) {
                errorMessage = 'No camera found on this device. Please use manual input.';
                showScannerError(errorMessage);
                alert('No camera found on this device. Please use manual input.');
            } else if (err.message && err.message.includes('not available')) {
                errorMessage = 'Camera is not available on this device. Please use manual input.';
                showScannerError(errorMessage);
            } else {
                errorMessage += (err.message || 'Unknown error');
                showScannerError(errorMessage);
                alert('Unable to access camera: ' + (err.message || 'Unknown error'));
                console.error('Camera error details:', err);
            }
        }

        // Use back camera (environment) on mobile - html5-qrcode expects object format
        const cameraConfig = { facingMode: "environment" };

        // Start scanner - html5-qrcode will request permission automatically
        html5QrCode.start(
            cameraConfig,
            config,
            function(decodedText, decodedResult) {
                // Successfully scanned
                handleScanSuccess(decodedText);
            },
            function(errorMessage) {
                // Ignore scanning errors (they're frequent during scanning)
                if (errorMessage && !errorMessage.includes('No QR code found') && !errorMessage.includes('NotFoundException')) {
                    console.debug('Scanning:', errorMessage);
                }
            }
        ).then(function() {
            // Scanner started successfully
            console.log('QR Code scanner started successfully');
            startBtn.disabled = false;
        }).catch(function(err) {
            console.error("Camera error", err);
            
            // Try fallback to user camera if environment camera failed
            // But only if it's not a permission error
            if (err.message && !err.message.includes('Permission') && !err.message.includes('NotAllowedError') && !err.message.includes('PermissionDeniedError')) {
                console.log('Back camera failed, trying front camera as fallback...');
                const frontCameraConfig = { facingMode: "user" };
                
                html5QrCode.start(
                    frontCameraConfig,
                    config,
                    function(decodedText, decodedResult) {
                        handleScanSuccess(decodedText);
                    },
                    function(errorMessage) {
                        if (errorMessage && !errorMessage.includes('No QR code found') && !errorMessage.includes('NotFoundException')) {
                            console.debug('Scanning:', errorMessage);
                        }
                    }
                ).then(function() {
                    console.log('QR Code scanner started with front camera');
                    startBtn.disabled = false;
                }).catch(function(fallbackErr) {
                    console.error("Front camera also failed", fallbackErr);
                    handleCameraError(fallbackErr);
                });
                return;
            }
            
            handleCameraError(err);
        });
    }

    // Stop scanner
    function stopScanner() {
        if (!isScanning) {
            return;
        }

        if (html5QrCode) {
            html5QrCode.stop().then(function() {
                html5QrCode.clear();
                cleanupScanner();
            }).catch(function(err) {
                console.error("Error stopping scanner", err);
                cleanupScanner();
            });
        } else {
            cleanupScanner();
        }
    }

    // Cleanup scanner state
    function cleanupScanner() {
        html5QrCode = null;
        isScanning = false;
        scannerContainer.style.display = 'none';
        startBtn.style.display = 'inline-block';
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="fas fa-camera"></i> Start Camera Scanner';
        stopBtn.style.display = 'none';
        hideScannerError();
    }

    // Extract token from URL or return the value as-is
    function extractTokenFromUrl(text) {
        const trimmed = text.trim();
        
        // Check if it's a URL
        try {
            const url = new URL(trimmed);
            // Extract token from URL parameter
            const params = new URLSearchParams(url.search);
            const token = params.get('token');
            if (token) {
                return token;
            }
            
            // Also check hash or path if token is there
            const hashMatch = url.hash.match(/[?&]token=([^&]+)/);
            if (hashMatch) {
                return decodeURIComponent(hashMatch[1]);
            }
        } catch (e) {
            // Not a valid URL, treat as token
        }
        
        // If it contains "token=" try to extract it
        const tokenMatch = trimmed.match(/[?&]token=([^&]+)/);
        if (tokenMatch) {
            return decodeURIComponent(tokenMatch[1]);
        }
        
        // Otherwise return as-is (might be just the token)
        return trimmed;
    }

    // Handle successful scan
    function handleScanSuccess(decodedText) {
        // Extract token from URL or use the text directly
        const token = extractTokenFromUrl(decodedText);
        
        if (!token) {
            return;
        }
        
        tokenInput.value = token;
        
        // Stop scanner after successful scan
        stopScanner();
        
        // Show success feedback
        tokenInput.style.borderColor = '#34c759';
        tokenInput.style.borderWidth = '2px';
        setTimeout(function() {
            tokenInput.style.borderColor = '';
            tokenInput.style.borderWidth = '';
        }, 2000);
        
        // Focus on the input field
        tokenInput.focus();
    }

    // Event listeners
    startBtn.addEventListener('click', startScanner);
    stopBtn.addEventListener('click', stopScanner);

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (isScanning && html5QrCode) {
            stopScanner();
        }
    });
})();
</script>
