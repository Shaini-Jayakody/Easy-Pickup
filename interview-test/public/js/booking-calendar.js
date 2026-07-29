/**
 * Modern Booking Calendar - Complete Implementation
 */

class BookingCalendar {
    constructor(options = {}) {
        this.containerId = options.containerId || 'calendar-container';
        this.carId = options.carId || null;
        this.bookingId = options.bookingId || null;
        this.bookings = [];
        this.selectedStart = null;
        this.selectedEnd = null;
        this.currentMonth = new Date().getMonth();
        this.currentYear = new Date().getFullYear();
        this.onDateRangeSelect = options.onDateRangeSelect || null;
        this.onDateClick = options.onDateClick || null;
        this.isSelecting = false;
        
        // Get today's date at midnight for comparison
        this.today = new Date();
        this.today.setHours(0, 0, 0, 0);
        
        this.init();
    }

    init() {
        this.createCalendarContainer();
        this.loadBookings();
    }

    createCalendarContainer() {
        let container = document.getElementById(this.containerId);
        if (!container) {
            const carSelect = document.getElementById('car_id');
            if (carSelect) {
                const wrapper = document.createElement('div');
                wrapper.id = this.containerId;
                wrapper.className = 'booking-calendar-wrapper';
                wrapper.style.display = 'none';
                carSelect.parentNode.insertBefore(wrapper, carSelect.nextSibling);
                container = wrapper;
            }
        }
        
        if (container) {
            this.render();
        }
    }

    loadBookings() {
        if (!this.carId) {
            this.bookings = [];
            this.render();
            return;
        }
        
        const self = this;

        if (window.isEditMode && window.bookingId) {
        data.booking_id = window.bookingId;
    }
        $.ajax({
            url: '/bookings/get-car-bookings',
            type: 'GET',
            data: { car_id: this.carId ,
                 booking_id: this.bookingId || null 
            },
            success: function(response) {
                self.bookings = response.bookings || [];
                self.render();
            },
            error: function() {
                self.bookings = [];
                self.render();
            }
        });
    }

    setCarId(carId) {
        this.carId = carId;
        this.selectedStart = null;
        this.selectedEnd = null;
        this.isSelecting = false;
        
        const container = document.getElementById(this.containerId);
        if (container) {
            container.style.display = carId ? 'block' : 'none';
        }
        
        if (carId) {
            this.loadBookings();
        } else {
            this.bookings = [];
            this.render();
        }
        
        // Clear selected info
        this.updateSelectedInfo();
    }

    render() {
        const container = document.getElementById(this.containerId);
        if (!container) return;
        
        if (!this.carId) {
            container.innerHTML = '';
            container.style.display = 'none';
            return;
        }
        
        container.style.display = 'block';
        container.innerHTML = this.generateCalendarHTML();
        this.attachEventListeners();
        this.updateSelectedInfo();
        this.highlightBookedDates();
    }

    generateCalendarHTML() {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                            'July', 'August', 'September', 'October', 'November', 'December'];
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
        const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
        
        // Count bookings per day
        const bookingCounts = this.getBookingCounts();
        
        let html = `
            <div class="calendar-header">
                <button class="calendar-nav" data-dir="-1">‹</button>
                <span class="calendar-title">${monthNames[this.currentMonth]} ${this.currentYear}</span>
                <button class="calendar-nav" data-dir="1">›</button>
            </div>
            <div class="calendar-legend">
                <span class="legend-item">
                    <span class="legend-color available"></span> Available
                </span>
                <span class="legend-item">
                    <span class="legend-color booked"></span> Booked
                </span>
                <span class="legend-item">
                    <span class="legend-color selected"></span> Selected
                </span>
                <span class="legend-item">
                    <span class="legend-color today"></span> Today
                </span>
            </div>
            <div class="calendar-grid">
                <div class="calendar-row">
                    ${dayNames.map(day => `<div class="calendar-cell calendar-header-cell">${day}</div>`).join('')}
                </div>
        `;
        
        let dayCount = 0;
        let rowHTML = '<div class="calendar-row">';
        
        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            rowHTML += `<div class="calendar-cell empty"></div>`;
            dayCount++;
        }
        
        // Day cells
        for (let day = 1; day <= daysInMonth; day++) {
            const dateObj = new Date(this.currentYear, this.currentMonth, day);
            const dateStr = this.formatDate(dateObj);
            const isToday = dateStr === this.formatDate(this.today);
            const isPast = dateObj < this.today;
            const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
            
            const isBooked = this.isDateBooked(dateStr);
            const bookingCount = bookingCounts[dateStr] || 0;
            const isSelected = this.isDateInRange(dateStr);
            
            let classes = 'calendar-cell day-cell';
            if (isToday) classes += ' today';
            if (isPast) classes += ' past';
            if (isBooked) classes += ' booked';
            if (isWeekend) classes += ' weekend';
            if (isSelected) {
                classes += ' selected';
                if (dateStr === this.selectedStart && dateStr === this.selectedEnd) {
                    classes += ' selected-single';
                } else if (dateStr === this.selectedStart) {
                    classes += ' selected-start';
                } else if (dateStr === this.selectedEnd) {
                    classes += ' selected-end';
                } else {
                    classes += ' selected-middle';
                }
            }
            
            let badges = '';
            if (isBooked && bookingCount > 0) {
                badges += `<span class="booked-badge">${bookingCount}</span>`;
                badges += `<span class="booked-icon">●</span>`;
            }
            
            rowHTML += `
                <div class="${classes}" data-date="${dateStr}" data-day="${day}" data-booked="${isBooked}">
                    ${day}
                    ${badges}
                </div>
            `;
            dayCount++;
            
            if (dayCount % 7 === 0) {
                rowHTML += '</div>';
                if (day < daysInMonth) {
                    rowHTML += '<div class="calendar-row">';
                }
            }
        }
        
        // Fill remaining cells
        while (dayCount % 7 !== 0) {
            rowHTML += `<div class="calendar-cell empty"></div>`;
            dayCount++;
        }
        
        if (dayCount % 7 === 0) {
            rowHTML += '</div>';
        }
        
        html += rowHTML + '</div>';
        
        // Footer
        const activeBookings = this.bookings.filter(b => 
            b.status === 'confirmed' || b.status === 'active'
        ).length;
        
        const hasSelection = this.selectedStart && this.selectedEnd;
        
        html += `
            <div class="calendar-footer">
                <span class="booking-count">
                    📅 <strong>${activeBookings}</strong> active booking${activeBookings !== 1 ? 's' : ''} this month
                </span>
                <span class="selected-info ${hasSelection ? 'visible' : ''}">
                    ✅ Selected: ${this.selectedStart ? this.formatDisplayDate(this.selectedStart) : ''}
                    ${this.selectedStart && this.selectedEnd ? ' → ' : ''}
                    ${this.selectedEnd ? this.formatDisplayDate(this.selectedEnd) : ''}
                    ${this.selectedStart && !this.selectedEnd ? ' (select end date)' : ''}
                </span>
                <button class="clear-selection ${hasSelection ? 'visible' : ''}" id="clear-selection-btn">
                    ✕ Clear
                </button>
            </div>
        `;
        
        return html;
    }

    getBookingCounts() {
        const counts = {};
        this.bookings.forEach(booking => {
            if (booking.status === 'cancelled') return;
            const start = new Date(booking.rental_start_date);
            const end = new Date(booking.rental_end_date);
            // Normalize to midnight
            start.setHours(0, 0, 0, 0);
            end.setHours(0, 0, 0, 0);
            
            for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                const key = this.formatDate(d);
                counts[key] = (counts[key] || 0) + 1;
            }
        });
        return counts;
    }

    isDateBooked(dateStr) {
        const checkDate = new Date(dateStr);
        checkDate.setHours(0, 0, 0, 0);
        
        for (const booking of this.bookings) {
            if (booking.status === 'cancelled') continue;
            
            const startDate = new Date(booking.rental_start_date);
            const endDate = new Date(booking.rental_end_date);
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(0, 0, 0, 0);
            
            if (checkDate >= startDate && checkDate <= endDate) {
                return true;
            }
        }
        return false;
    }

    isDateInRange(dateStr) {
        if (!this.selectedStart || !this.selectedEnd) {
            return dateStr === this.selectedStart;
        }
        
        const checkDate = new Date(dateStr);
        const start = new Date(this.selectedStart);
        const end = new Date(this.selectedEnd);
        checkDate.setHours(0, 0, 0, 0);
        start.setHours(0, 0, 0, 0);
        end.setHours(0, 0, 0, 0);
        
        return checkDate >= start && checkDate <= end;
    }

    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    formatDisplayDate(dateStr) {
        const parts = dateStr.split('-');
        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
    }

    highlightBookedDates() {
        const container = document.getElementById(this.containerId);
        if (!container) return;
        
        // No additional highlighting needed - already handled in render
    }

    attachEventListeners() {
        const container = document.getElementById(this.containerId);
        if (!container) return;
        
        // Navigation buttons
        container.querySelectorAll('.calendar-nav').forEach(btn => {
            btn.removeEventListener('click', this.handleNavClick);
            btn.addEventListener('click', this.handleNavClick.bind(this));
        });
        
        // Day click handlers
        container.querySelectorAll('.day-cell:not(.booked):not(.past)').forEach(cell => {
            cell.removeEventListener('click', this.handleDayClick);
            cell.addEventListener('click', this.handleDayClick.bind(this));
        });
        
        // Hover effects for range selection
        container.querySelectorAll('.day-cell:not(.booked):not(.past)').forEach(cell => {
            cell.removeEventListener('mouseenter', this.handleMouseEnter);
            cell.removeEventListener('mouseleave', this.handleMouseLeave);
            cell.addEventListener('mouseenter', this.handleMouseEnter.bind(this));
            cell.addEventListener('mouseleave', this.handleMouseLeave.bind(this));
        });
        
        // Clear selection button
        const clearBtn = document.getElementById('clear-selection-btn');
        if (clearBtn) {
            clearBtn.removeEventListener('click', this.clearSelection.bind(this));
            clearBtn.addEventListener('click', this.clearSelection.bind(this));
        }
    }

    handleNavClick(e) {
        const dir = parseInt(e.target.dataset.dir);
        this.currentMonth += dir;
        if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        } else if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        }
        this.render();
    }

    handleDayClick(e) {
        const cell = e.target.closest('.day-cell');
        if (!cell) return;
        
        const dateStr = cell.dataset.date;
        if (!dateStr) return;
        if (cell.dataset.booked === 'true') return;
        
        // Check if date is in the future or today
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selectedDate = new Date(dateStr);
        selectedDate.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            this.showTooltip('Cannot select past dates');
            return;
        }
        
        this.handleDateClick(dateStr);
    }

    handleMouseEnter(e) {
        const cell = e.target.closest('.day-cell');
        if (!cell) return;
        if (!this.selectedStart || this.selectedEnd) return;
        if (cell.dataset.booked === 'true') return;
        
        const dateStr = cell.dataset.date;
        if (!dateStr) return;
        
        const startDate = new Date(this.selectedStart);
        const endDate = new Date(dateStr);
        startDate.setHours(0, 0, 0, 0);
        endDate.setHours(0, 0, 0, 0);
        
        if (endDate < startDate) return;
        
        // Prevent hover highlighting across any already-booked dates.
        if (this.hasBookedDates(this.selectedStart, dateStr)) {
            return;
        }
        
        // Clear previous hover
        const container = document.getElementById(this.containerId);
        container.querySelectorAll('.day-cell.hover').forEach(el => {
            el.classList.remove('hover');
        });
        
        // Add hover to range
        container.querySelectorAll('.day-cell:not(.booked):not(.past)').forEach(el => {
            const d = el.dataset.date;
            if (!d) return;
            const checkDate = new Date(d);
            checkDate.setHours(0, 0, 0, 0);
            if (checkDate >= startDate && checkDate <= endDate) {
                el.classList.add('hover');
            }
        });
    }

    handleMouseLeave(e) {
        const container = document.getElementById(this.containerId);
        container.querySelectorAll('.day-cell.hover').forEach(el => {
            el.classList.remove('hover');
        });
    }

    handleDateClick(dateStr) {
        if (!this.selectedStart) {
            // First selection - set start
            this.selectedStart = dateStr;
            this.selectedEnd = null;
            this.isSelecting = true;
            this.render();
            this.updateDateInputs();
            this.showTooltip('Select end date for booking range');
        } else if (!this.selectedEnd) {
            // Second selection - set end
            const startDate = new Date(this.selectedStart);
            const endDate = new Date(dateStr);
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(0, 0, 0, 0);
            
            if (endDate < startDate) {
                // If end is before start, reset and start over
                this.selectedStart = dateStr;
                this.selectedEnd = null;
                this.render();
                this.updateDateInputs();
                this.showTooltip('Start date updated. Select end date.');
                return;
            }
            
            if (this.hasBookedDates(this.selectedStart, dateStr)) {
                this.showTooltip('⚠️ This range overlaps an existing booking for this car. Please choose a different range.');
                this.selectedStart = null;
                this.selectedEnd = null;
                this.isSelecting = false;
                this.render();
                this.updateDateInputs();
                return;
            }

            this.selectedEnd = dateStr;
            this.isSelecting = false;
            this.render();
            this.updateDateInputs();
            
            // Trigger callback
            if (this.onDateRangeSelect) {
                this.onDateRangeSelect(this.selectedStart, this.selectedEnd);
            }
            
            this.showTooltip('✅ Range selected. Existing bookings are highlighted in red and will be validated on submit.');
        } else {
            // Reset selection
            this.selectedStart = dateStr;
            this.selectedEnd = null;
            this.isSelecting = true;
            this.render();
            this.updateDateInputs();
            this.showTooltip('Select end date');
        }
        
        this.updateSelectedInfo();
    }

    hasBookedDates(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        start.setHours(0, 0, 0, 0);
        end.setHours(0, 0, 0, 0);
        
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dateStr = this.formatDate(d);
            if (this.isDateBooked(dateStr)) {
                return true;
            }
        }
        return false;
    }

    updateDateInputs() {
        const startInput = document.getElementById('rental_start_date');
        const endInput = document.getElementById('rental_end_date');
        
        if (this.selectedStart) {
            // Set to 9:00 AM by default
            startInput.value = this.selectedStart + 'T09:00';
        } else {
            startInput.value = '';
        }
        
        if (this.selectedEnd) {
            // Set to 5:00 PM by default
            endInput.value = this.selectedEnd + 'T17:00';
        } else {
            endInput.value = '';
        }
        
        // Trigger change events to update UI
        $(startInput).trigger('change');
        $(endInput).trigger('change');
    }

    updateSelectedInfo() {
        const container = document.getElementById(this.containerId);
        if (!container) return;
        
        const infoEl = container.querySelector('.selected-info');
        const clearBtn = container.querySelector('.clear-selection');
        const hasSelection = this.selectedStart && this.selectedEnd;
        
        if (infoEl) {
            if (this.selectedStart) {
                let text = '✅ Selected: ' + this.formatDisplayDate(this.selectedStart);
                if (this.selectedEnd) {
                    text += ' → ' + this.formatDisplayDate(this.selectedEnd);
                } else {
                    text += ' (select end date)';
                }
                infoEl.textContent = text;
                infoEl.classList.add('visible');
            } else {
                infoEl.classList.remove('visible');
            }
        }
        
        if (clearBtn) {
            if (hasSelection || this.selectedStart) {
                clearBtn.classList.add('visible');
            } else {
                clearBtn.classList.remove('visible');
            }
        }
    }

    clearSelection() {
        this.selectedStart = null;
        this.selectedEnd = null;
        this.isSelecting = false;
        this.render();
        this.updateDateInputs();
        this.updateSelectedInfo();
        this.showTooltip('Selection cleared');
    }

    showTooltip(message) {
        // Remove existing tooltip
        const existing = document.querySelector('.calendar-tooltip');
        if (existing) existing.remove();
        
        const tooltip = document.createElement('div');
        tooltip.className = 'calendar-tooltip';
        tooltip.textContent = message;
        document.body.appendChild(tooltip);
        
        setTimeout(() => {
            tooltip.classList.add('fade-out');
            setTimeout(() => tooltip.remove(), 400);
        }, 2500);
    }

    // Public methods for external use
    getSelectedRange() {
        if (this.selectedStart && this.selectedEnd) {
            return {
                start: this.selectedStart,
                end: this.selectedEnd
            };
        }
        return null;
    }

    clearSelectedRange() {
        this.clearSelection();
    }
}

// Initialize calendar when DOM is ready
$(document).ready(function() {
    let calendar = null;
    
    // Create calendar container after car selection
    function initCalendar() {
        const carId = $('#car_id').val();
        if (!carId) {
            const container = document.getElementById('calendar-container');
            if (container) container.style.display = 'none';
            return;
        }
        
        const container = document.getElementById('calendar-container');
        if (container) container.style.display = 'block';
        
        if (!calendar) {
            calendar = new BookingCalendar({
                containerId: 'calendar-container',
                carId: carId,
                 bookingId: window.bookingId || null,
                onDateRangeSelect: function(start, end) {
                    console.log('Range selected:', start, end);
                    // Trigger availability check
                    $('#rental_start_date').trigger('change');
                    $('#rental_end_date').trigger('change');
                    
                    // Update submit button state
                    if (typeof updateSubmitButton === 'function') {
                        updateSubmitButton();
                    }
                }
            });
            // Make calendar globally accessible
            window.bookingCalendar = calendar;
        } else {
            calendar.setCarId(carId);
        }
    }
    
    // Initialize on car change
    $('#car_id').on('change', function() {
        initCalendar();
    });
    
    // Initialize if car is pre-selected
    if ($('#car_id').val()) {
        initCalendar();
    }
    
    // Handle date input changes to sync with calendar
    $('#rental_start_date, #rental_end_date').on('change', function() {
        if (calendar) {
            const startVal = $('#rental_start_date').val();
            const endVal = $('#rental_end_date').val();
            
            if (startVal) {
                const startDate = new Date(startVal);
                const startStr = startDate.toISOString().split('T')[0];
                
                if (endVal) {
                    const endDate = new Date(endVal);
                    const endStr = endDate.toISOString().split('T')[0];
                    
                    // Only update if different from current selection
                    if (calendar.selectedStart !== startStr || calendar.selectedEnd !== endStr) {
                        calendar.selectedStart = startStr;
                        calendar.selectedEnd = endStr;
                        calendar.render();
                        calendar.updateSelectedInfo();
                    }
                }
            }
        }
    });
});