// DOM Elements
const videoFeed = document.getElementById('videoFeed');
const canvas = document.getElementById('canvas');
const startCameraBtn = document.getElementById('startCamera');
const captureBtn = document.getElementById('captureImage');
const statusMessage = document.getElementById('statusMessage');
const currentTimeElement = document.getElementById('currentTime');
const lastScanElement = document.getElementById('lastScan');

let stream = null;

// Update current time
function updateCurrentTime() {
    const now = new Date();
    currentTimeElement.textContent = now.toLocaleTimeString();
}

// Update time every second
setInterval(updateCurrentTime, 1000);
updateCurrentTime(); // Initial update

// Start camera
async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        });
        videoFeed.srcObject = stream;
        startCameraBtn.disabled = true;
        captureBtn.disabled = false;
        showStatus('Camera started successfully', 'success');
    } catch (err) {
        showStatus('Failed to access camera: ' + err.message, 'error');
        console.error('Error accessing camera:', err);
    }
}

// Stop camera
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        videoFeed.srcObject = null;
        stream = null;
        startCameraBtn.disabled = false;
        captureBtn.disabled = true;
    }
}

// Capture image and simulate biometric verification
async function captureImage() {
    if (!stream) return;

    // Setup canvas
    canvas.width = videoFeed.videoWidth;
    canvas.height = videoFeed.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(videoFeed, 0, 0, canvas.width, canvas.height);

    // Simulate processing
    showStatus('Processing biometric data...', 'processing');
    captureBtn.disabled = true;

    // Simulate API call delay
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Simulate random success/failure (90% success rate)
    const success = Math.random() < 0.9;

    if (success) {
        const studentId = Math.floor(Math.random() * 1000) + 1; // Simulate student ID
        const now = new Date();
        lastScanElement.textContent = `Student ID ${studentId} - ${now.toLocaleTimeString()}`;

        // Determine late threshold (hardcoded for now)
        const lateThreshold = new Date();
        lateThreshold.setHours(8, 30, 0, 0); // 08:30:00

        let status = 'present';
        let remarks = '';

        if (now > lateThreshold) {
            status = 'late';
            remarks = prompt('You are marked as late. Please enter remarks:', '') || '';
        }

        showStatus('Attendance marked successfully!', 'success');
        
        // Store attendance in localStorage
        const attendance = JSON.parse(localStorage.getItem('attendance') || '[]');
        attendance.push({
            studentId,
            timestamp: now.toISOString(),
            status,
            remarks
        });
        localStorage.setItem('attendance', JSON.stringify(attendance));
    } else {
        showStatus('Biometric verification failed. Please try again.', 'error');
    }

    captureBtn.disabled = false;
}

// Show status message
function showStatus(message, type) {
    statusMessage.className = 'p-4 rounded-lg ' + getStatusClass(type);
    statusMessage.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                ${getStatusIcon(type)}
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white">${message}</p>
            </div>
        </div>
    `;
    statusMessage.classList.remove('hidden');
}

// Get status message class based on type
function getStatusClass(type) {
    switch (type) {
        case 'success':
            return 'bg-green-500';
        case 'error':
            return 'bg-red-500';
        case 'processing':
            return 'bg-blue-500';
        default:
            return 'bg-gray-500';
    }
}

// Get status icon based on type
function getStatusIcon(type) {
    switch (type) {
        case 'success':
            return '<i class="fas fa-check-circle text-white text-xl"></i>';
        case 'error':
            return '<i class="fas fa-times-circle text-white text-xl"></i>';
        case 'processing':
            return '<i class="fas fa-spinner fa-spin text-white text-xl"></i>';
        default:
            return '<i class="fas fa-info-circle text-white text-xl"></i>';
    }
}

// Event Listeners
startCameraBtn.addEventListener('click', startCamera);
captureBtn.addEventListener('click', captureImage);

// Cleanup on page unload
window.addEventListener('beforeunload', stopCamera);

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    captureBtn.disabled = true;
});
