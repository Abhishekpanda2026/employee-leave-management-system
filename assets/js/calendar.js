// Calendar functionality
document.addEventListener('DOMContentLoaded', function() {
    // Current date
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    
    // DOM elements
    const calendarBody = document.getElementById('calendar-body');
    const currentMonthYear = document.getElementById('current-month-year');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const currentMonthBtn = document.getElementById('current-month');
    
    // Leave status colors
    const statusColors = {
        'approved': 'approved',
        'pending': 'pending', 
        'rejected': 'rejected'
    };
    
    // Render calendar
    function renderCalendar(month, year) {
        // Clear existing calendar
        calendarBody.innerHTML = '';
        
        // Set month and year in header
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        currentMonthYear.textContent = `${monthNames[month]} ${year}`;
        
        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay(); // 0 = Sunday, 1 = Monday, etc.
        
        // Create empty cells for days before the first day of the month
        for (let i = 0; i < startingDayOfWeek; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'calendar-day other-month';
            calendarBody.appendChild(emptyCell);
        }
        
        // Create cells for each day of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            
            // Add today class if this is the current day
            const today = new Date();
            if (date.getDate() === today.getDate() && 
                date.getMonth() === today.getMonth() && 
                date.getFullYear() === today.getFullYear()) {
                dayElement.classList.add('today');
            }
            
            // Create day number element
            const dayNumber = document.createElement('div');
            dayNumber.className = 'day-number';
            dayNumber.textContent = day;
            dayElement.appendChild(dayNumber);
            
            // Add leave events for this day
            addLeaveEvents(dayElement, date);
            
            // Add hover tooltip functionality
            dayElement.addEventListener('mouseenter', function(e) {
                showTooltip(e, dayElement, date);
            });
            
            dayElement.addEventListener('mouseleave', function() {
                hideTooltip();
            });
            
            calendarBody.appendChild(dayElement);
        }
    }
    
    // Add leave events to a day
    function addLeaveEvents(dayElement, date) {
        if (!leaveRequests) return;
        
        // Format date as YYYY-MM-DD for comparison
        const dateStr = date.toISOString().split('T')[0];
        
        // Find leave requests that fall on this date
        const dayLeaves = leaveRequests.filter(request => {
            const startDate = new Date(request.start_date);
            const endDate = new Date(request.end_date);
            
            // Check if the date falls within the leave request period
            return date >= startDate && date <= endDate;
        });
        
        // Add each leave event to the day
        dayLeaves.forEach(request => {
            const leaveDiv = document.createElement('div');
            leaveDiv.className = `leave-event ${statusColors[request.status]}`;
            leaveDiv.textContent = request.leave_type_name;
            
            // Add tooltip data
            leaveDiv.dataset.tooltip = `${request.leave_type_name} - ${request.status}`;
            
            dayElement.appendChild(leaveDiv);
        });
    }
    
    // Show tooltip with leave details
    function showTooltip(event, dayElement, date) {
        const tooltip = document.getElementById('tooltip');
        
        // Format date as YYYY-MM-DD for comparison
        const dateStr = date.toISOString().split('T')[0];
        
        // Find leave requests that fall on this date
        const dayLeaves = leaveRequests.filter(request => {
            const startDate = new Date(request.start_date);
            const endDate = new Date(request.end_date);
            return date >= startDate && date <= endDate;
        });
        
        if (dayLeaves.length > 0) {
            let tooltipContent = `<strong>${currentUserName}</strong><br>`;
            tooltipContent += `<strong>${date.toDateString()}</strong><br><br>`;
            
            dayLeaves.forEach(leave => {
                tooltipContent += `<div class="tooltip-leave ${statusColors[leave.status]}">
                    <strong>${leave.leave_type_name}</strong><br>
                    Status: ${leave.status}<br>
                    ${leave.start_date === leave.end_date ? 
                        `Date: ${leave.start_date}` : 
                        `Dates: ${leave.start_date} to ${leave.end_date}`}
                </div><br>`;
            });
            
            tooltip.innerHTML = tooltipContent;
            tooltip.style.display = 'block';
            
            // Position tooltip near the cursor
            const x = event.pageX + 10;
            const y = event.pageY - 10;
            tooltip.style.left = x + 'px';
            tooltip.style.top = y + 'px';
        }
    }
    
    // Hide tooltip
    function hideTooltip() {
        const tooltip = document.getElementById('tooltip');
        tooltip.style.display = 'none';
    }
    
    // Navigation event listeners
    prevMonthBtn.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar(currentMonth, currentYear);
    });
    
    nextMonthBtn.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar(currentMonth, currentYear);
    });
    
    currentMonthBtn.addEventListener('click', function() {
        const today = new Date();
        currentMonth = today.getMonth();
        currentYear = today.getFullYear();
        renderCalendar(currentMonth, currentYear);
    });
    
    // Initial render
    renderCalendar(currentMonth, currentYear);
    
    // Add click event to calendar days for more detailed view
    calendarBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('other-month')) {
            const dayNumber = e.target.querySelector('.day-number').textContent;
            const clickedDate = new Date(currentYear, currentMonth, parseInt(dayNumber));
            
            // Find leave requests for this day
            const dayLeaves = leaveRequests.filter(request => {
                const startDate = new Date(request.start_date);
                const endDate = new Date(request.end_date);
                return clickedDate >= startDate && clickedDate <= endDate;
            });
            
            if (dayLeaves.length > 0) {
                let modalContent = `<h5>Leave Details for ${clickedDate.toDateString()}</h5><ul>`;
                dayLeaves.forEach(leave => {
                    modalContent += `<li>
                        <strong>${leave.leave_type_name}</strong> - 
                        Status: <span class="${statusColors[leave.status]}">${leave.status}</span><br>
                        ${leave.start_date === leave.end_date ? 
                            `Date: ${leave.start_date}` : 
                            `Dates: ${leave.start_date} to ${leave.end_date}`}
                    </li>`;
                });
                modalContent += '</ul>';
                
                // Create a simple modal or alert to show details
                alert(modalContent.replace(/<[^>]*>/g, ''));
            }
        }
    });
});