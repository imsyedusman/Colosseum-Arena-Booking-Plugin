<div class="wrap caba-wrap p-6 bg-gray-50 min-h-screen">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Calendar Rezervări</h1>
        <p class="text-gray-600 mt-1">Trage evenimentele pentru a le schimba data sau ora. Apasă pe un eveniment pentru a-l edita în meniul Rezervări.</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 border border-gray-100">
        <div id="colosseum-arena-calendar"></div>
    </div>
</div>

<style>
/* Adjust FullCalendar elements slightly to match our flat style */
#colosseum-arena-calendar .fc .fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: #1f2937;
}
#colosseum-arena-calendar .fc .fc-button-primary {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
}
#colosseum-arena-calendar .fc .fc-button-primary:hover {
    background-color: #1d4ed8 !important;
}
#colosseum-arena-calendar .fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 4px;
    font-size: 0.85em;
    font-weight: 500;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('colosseum-arena-calendar');
    if(!calendarEl) return;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        locale: 'ro',
        firstDay: 1, // Luni
        editable: true,
        droppable: true,
        eventResizableFromStart: false,
        events: function(fetchInfo, successCallback, failureCallback) {
            jQuery.ajax({
                url: cab_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'cab_ajax',
                    nonce: cab_ajax_obj.nonce,
                    route: 'get_calendar_events'
                },
                success: function(response) {
                    successCallback(response.data);
                },
                error: function() {
                    failureCallback();
                }
            });
        },
        eventDrop: function(info) {
            updateEvent(info.event);
        },
        eventResize: function(info) {
            updateEvent(info.event);
        },
        eventClick: function(info) {
            window.location.href = '?page=colosseum-arena-booking-rezervari';
        }
    });
    
    calendar.render();

    function updateEvent(event) {
        var start = event.start;
        var end = event.end || new Date(start.getTime() + (60 * 60 * 1000)); // default 1 hr
        
        // Format YYYY-MM-DD
        var booking_date = start.getFullYear() + '-' + String(start.getMonth() + 1).padStart(2, '0') + '-' + String(start.getDate()).padStart(2, '0');
        
        // Format HH:mm:ss
        var start_time = String(start.getHours()).padStart(2, '0') + ':' + String(start.getMinutes()).padStart(2, '0') + ':00';
        var end_time = String(end.getHours()).padStart(2, '0') + ':' + String(end.getMinutes()).padStart(2, '0') + ':00';
        
        jQuery.ajax({
            url: cab_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'cab_ajax',
                nonce: cab_ajax_obj.nonce,
                route: 'update_booking_dates',
                id: event.id,
                booking_date: booking_date,
                start_time: start_time,
                end_time: end_time
            },
            success: function(res) {
                if(res.success) {
                    // console.log('Event updated');
                } else {
                    event.revert();
                    alert('Eroare la actualizarea rezervării.');
                }
            }
        });
    }
});
</script>
