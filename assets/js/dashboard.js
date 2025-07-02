// Dashboard JavaScript for 91CLUB Multi-Game Platform

let selectedAmount = 0;
let countdownInterval;
let currentRoundId = null;
let isCountdownActive = false;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupEventListeners();
    startCountdown();
    loadRecentResults();
    
    // Auto-refresh every 10 seconds
    setInterval(function() {
        refreshData();
    }, 10000);
});

// Initialize dashboard
function initializeDashboard() {
    // Set default bet amount
    const defaultAmount = document.querySelector('[data-amount="100"]');
    if (defaultAmount) {
        selectAmount(defaultAmount);
    }
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Setup event listeners
function setupEventListeners() {
    // Amount selection
    document.querySelectorAll('.bet-amount').forEach(button => {
        button.addEventListener('click', function() {
            selectAmount(this);
        });
    });
    
    // Custom amount input
    const customAmount = document.getElementById('customAmount');
    if (customAmount) {
        customAmount.addEventListener('input', function() {
            const value = parseInt(this.value) || 0;
            if (value > 0) {
                selectedAmount = value;
                clearAmountSelection();
            }
        });
    }
    
    // Bet buttons
    document.querySelectorAll('.bet-button').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            const value = this.dataset.value;
            const multiplier = parseFloat(this.dataset.multiplier);
            
            if (selectedAmount <= 0) {
                showError('Please select a bet amount first!');
                return;
            }
            
            placeBet(type, value, multiplier);
        });
    });
    
    // Confirm bet button
    const confirmBetBtn = document.getElementById('confirmBet');
    if (confirmBetBtn) {
        confirmBetBtn.addEventListener('click', function() {
            submitBet();
        });
    }
}

// Select bet amount
function selectAmount(button) {
    // Clear previous selections
    clearAmountSelection();
    
    // Select current button
    button.classList.add('active');
    selectedAmount = parseInt(button.dataset.amount);
    
    // Clear custom amount
    const customAmount = document.getElementById('customAmount');
    if (customAmount) {
        customAmount.value = '';
    }
}

// Clear amount selection
function clearAmountSelection() {
    document.querySelectorAll('.bet-amount').forEach(btn => {
        btn.classList.remove('active');
    });
}

// Place bet (show confirmation)
function placeBet(type, value, multiplier) {
    const potentialWin = selectedAmount * multiplier;
    
    const betDetails = `
        <div class="text-center">
            <h5>Bet Details</h5>
            <hr>
            <div class="row">
                <div class="col-6"><strong>Type:</strong></div>
                <div class="col-6">${capitalizeFirst(type)}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Choice:</strong></div>
                <div class="col-6">${capitalizeFirst(value)}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Amount:</strong></div>
                <div class="col-6">₹${selectedAmount}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Multiplier:</strong></div>
                <div class="col-6">${multiplier}x</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Potential Win:</strong></div>
                <div class="col-6 text-success"><strong>₹${potentialWin.toFixed(2)}</strong></div>
            </div>
        </div>
    `;
    
    document.getElementById('betDetails').innerHTML = betDetails;
    
    // Store bet data for submission
    const confirmBtn = document.getElementById('confirmBet');
    confirmBtn.dataset.type = type;
    confirmBtn.dataset.value = value;
    confirmBtn.dataset.multiplier = multiplier;
    confirmBtn.dataset.amount = selectedAmount;
    confirmBtn.dataset.potentialWin = potentialWin;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('betConfirmModal'));
    modal.show();
}

// Submit bet via AJAX
function submitBet() {
    const confirmBtn = document.getElementById('confirmBet');
    const betData = {
        type: confirmBtn.dataset.type,
        value: confirmBtn.dataset.value,
        multiplier: confirmBtn.dataset.multiplier,
        amount: confirmBtn.dataset.amount,
        potential_win: confirmBtn.dataset.potentialWin
    };
    
    // Show loading state
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Bet...';
    confirmBtn.disabled = true;
    
    fetch('../api/place_bet.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(betData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('betConfirmModal')).hide();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Bet Placed!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            
            // Update balance
            updateBalance(data.new_balance);
            
            // Reset form
            resetBetForm();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to place bet. Please try again.');
    })
    .finally(() => {
        // Reset button
        confirmBtn.innerHTML = 'Confirm Bet';
        confirmBtn.disabled = false;
    });
}

// Start countdown timer
function startCountdown() {
    if (isCountdownActive) return;
    
    isCountdownActive = true;
    
    countdownInterval = setInterval(function() {
        fetch('../api/get_countdown.php')
            .then(response => response.json())
            .then(data => {
                const countdown = Math.max(0, data.countdown);
                document.getElementById('countdown').textContent = countdown;
                
                // Update round number if changed
                if (data.round_number !== currentRoundId) {
                    currentRoundId = data.round_number;
                    loadRecentResults();
                }
                
                // Visual feedback for last 10 seconds
                const countdownElement = document.getElementById('countdown');
                if (countdown <= 10 && countdown > 0) {
                    countdownElement.classList.add('text-danger', 'pulse');
                } else {
                    countdownElement.classList.remove('text-danger', 'pulse');
                }
                
                // If round ended, disable betting for a moment
                if (countdown === 0) {
                    disableBetting();
                    setTimeout(() => {
                        enableBetting();
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Countdown error:', error);
            });
    }, 1000);
}

// Disable betting
function disableBetting() {
    document.querySelectorAll('.bet-button').forEach(btn => {
        btn.disabled = true;
        btn.classList.add('loading');
    });
}

// Enable betting
function enableBetting() {
    document.querySelectorAll('.bet-button').forEach(btn => {
        btn.disabled = false;
        btn.classList.remove('loading');
    });
}

// Load recent results
function loadRecentResults() {
    fetch('../api/get_recent_results.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentResults');
            if (data.results && data.results.length > 0) {
                container.innerHTML = data.results.map(result => `
                    <div class="result-item ${result.result_color} fade-in">
                        <span>Round #${result.round_number}</span>
                        <div>
                            <span class="color-indicator color-${result.result_color}"></span>
                            ${result.result_number || '?'}
                            <small class="text-muted">(${result.result_size})</small>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="text-center text-muted">No results yet</div>';
            }
        })
        .catch(error => {
            console.error('Error loading results:', error);
        });
}

// Refresh all data
function refreshData() {
    loadRecentResults();
    updateNotifications();
    updateBalance();
}

// Update notifications
function updateNotifications() {
    fetch('../api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const count = document.getElementById('notificationCount');
            const list = document.getElementById('notificationList');
            
            if (count) count.textContent = data.count || 0;
            
            if (list && data.notifications) {
                list.innerHTML = data.notifications.map(notif => `
                    <li><a class="dropdown-item small" href="#">
                        <strong>${notif.title}</strong><br>
                        <span class="text-muted">${notif.message}</span>
                    </a></li>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error updating notifications:', error);
        });
}

// Update balance display
function updateBalance(newBalance = null) {
    if (newBalance !== null) {
        // Update all balance displays
        document.querySelectorAll('[data-balance]').forEach(element => {
            element.textContent = `₹${parseFloat(newBalance).toFixed(2)}`;
        });
    } else {
        // Fetch current balance
        fetch('../api/get_balance.php')
            .then(response => response.json())
            .then(data => {
                if (data.balance !== undefined) {
                    updateBalance(data.balance);
                }
            })
            .catch(error => {
                console.error('Error updating balance:', error);
            });
    }
}

// Reset bet form
function resetBetForm() {
    clearAmountSelection();
    selectedAmount = 0;
    const customAmount = document.getElementById('customAmount');
    if (customAmount) {
        customAmount.value = '';
    }
}

// Refresh round manually
function refreshRound() {
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    // Refresh data
    refreshData();
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }, 1000);
}

// Utility functions
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#3B82F6'
    });
}

function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Handle connection errors
window.addEventListener('online', function() {
    showSuccess('Connection restored!');
    refreshData();
});

window.addEventListener('offline', function() {
    showError('Connection lost. Please check your internet.');
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});