class LiveMonitoring {
    constructor(studentExamId, options = {}) {
        this.studentExamId = studentExamId;
        this.options = {
            trackTabSwitches: true,
            trackMouseEvents: false,
            screenshotInterval: 30,
            ...options
        };

        this.tabSwitchCount = 0;
        this.lastActivity = Date.now();
        this.isExamActive = true;

        this.init();
    }

    init() {
        if (this.options.trackTabSwitches) {
            this.trackTabSwitches();
        }

        if (this.options.trackMouseEvents) {
            this.trackMouseEvents();
        }

        if (this.options.screenshotInterval > 0) {
            this.startScreenshotCapture();
        }

        this.trackWindowFocus();
        this.preventCopyPaste();
        this.trackConnection();
    }

    trackTabSwitches() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.isExamActive) {
                this.tabSwitchCount++;
                this.logActivity('tab_switch', {
                    count: this.tabSwitchCount
                });

                if (this.tabSwitchCount >= 3) {
                    this.showWarning('Warning: Multiple tab switches detected');
                }
            }
        });
    }

    trackWindowFocus() {
        window.addEventListener('blur', () => {
            if (this.isExamActive) {
                this.lastBlurTime = Date.now();
                this.logActivity('window_blur', {
                    timestamp: this.lastBlurTime
                });
            }
        });

        window.addEventListener('focus', () => {
            if (this.isExamActive && this.lastBlurTime) {
                const blurDuration = (Date.now() - this.lastBlurTime) / 1000;
                this.logActivity('window_focus', {
                    blur_duration: blurDuration
                });

                if (blurDuration > 5) {
                    this.showWarning(`You left the exam window for ${Math.round(blurDuration)} seconds`);
                }
            }
        });
    }

    trackMouseEvents() {
        document.addEventListener('mouseleave', () => {
            if (this.isExamActive) {
                this.logActivity('mouse_leave', {});
            }
        });

        document.addEventListener('mouseenter', () => {
            if (this.isExamActive) {
                this.logActivity('mouse_enter', {});
            }
        });
    }

    preventCopyPaste() {
        document.addEventListener('copy', (e) => {
            if (this.isExamActive) {
                e.preventDefault();
                this.logActivity('copy_attempt', {});
                this.showWarning('Copying is not allowed during exam');
            }
        });

        document.addEventListener('paste', (e) => {
            if (this.isExamActive) {
                e.preventDefault();
                this.logActivity('paste_attempt', {});
                this.showWarning('Pasting is not allowed during exam');
            }
        });

        document.addEventListener('contextmenu', (e) => {
            if (this.isExamActive) {
                e.preventDefault();
                this.logActivity('right_click', {});
            }
        });

        // Detect developer tools
        this.detectDevTools();
    }

    detectDevTools() {
        const element = new Image();
        Object.defineProperty(element, 'id', {
            get: () => {
                this.logActivity('devtools_open', {});
                this.showWarning('Developer tools detected');
            }
        });
        console.log(element);
    }

    trackConnection() {
        window.addEventListener('online', () => {
            this.logActivity('connection_restored', {});
        });

        window.addEventListener('offline', () => {
            this.logActivity('connection_lost', {});
            this.showWarning('Internet connection lost. Please reconnect.');
        });
    }

    startScreenshotCapture() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.warn('Screenshot capture not supported');
            return;
        }

        this.captureScreenshot();
        setInterval(() => {
            this.captureScreenshot();
        }, this.options.screenshotInterval * 1000);
    }

    captureScreenshot() {
        // This would require additional setup for screen capture
        // For now, we'll just log that screenshot was taken
        this.logActivity('screenshot_taken', {
            timestamp: Date.now()
        });

        // TODO: Implement actual screenshot capture using html2canvas or similar
    }

    logActivity(type, data = {}) {
        $.ajax({
            url: `/student/exam/${this.studentExamId}/activity`,
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').content,
                type: type,
                data: data
            },
            error: (xhr) => {
                console.error('Failed to log activity:', xhr);
            }
        });
    }

    showWarning(message) {
        toastr.warning(message, 'Warning', {
            timeOut: 5000,
            extendedTimeOut: 3000,
            closeButton: true,
            progressBar: true
        });
    }

    stop() {
        this.isExamActive = false;
    }
}

// Initialize when exam page loads
$(document).ready(() => {
    const studentExamId = $('meta[name="student-exam-id"]').attr('content');
    if (studentExamId) {
        window.monitoring = new LiveMonitoring(studentExamId, {
            trackTabSwitches: true,
            screenshotInterval: 30
        });
    }
});
