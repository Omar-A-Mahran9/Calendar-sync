@extends('layouts.app')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.css">
@endpush
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div id='calendar'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss='modal' aria-label=></button>
                </div>
                <div class="modal-body">
                    <div>
                        <input type="hidden" id='eventId'>
                        <label for="title">
                            Title
                        </label>
                        <input type="text" placeholder="Enter Title" class="form-control" id='title' name="title"
                            value="" required>
                    </div>
                    <div>
                        <label for="is_all_day">
                            All Day
                        </label>
                        <input type="checkbox" id='is_all_day' checked name='is_all_day' value="" required>
                    </div>
                    <div>
                        <label for="startDateTime">
                            Start Date/Time
                        </label>
                        <input type="text" placeholder="Select start date" readonly class="form-control"
                            id="startDateTime" name="startDate" value="" required>
                    </div>
                    <div>
                        <label for="endDateTime">
                            End Date/Time
                        </label>
                        <input type="text" placeholder="Select end date" readonly class="form-control" id="endDateTime"
                            name="endDate" value="" required>
                    </div>
                    <div>
                        <label for="description">
                            Description </label>
                        <textarea placeholder="Enter Description" class="form-control" id="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger mr-auto" style="display:none" id='deleteEventBtn'
                        onclick="deleteEvent()">Delete Event</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitEventFormData()">Save changes</button>
                </div>

            </div>

        </div>
    @endsection


    @push('script')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        <script type="module"
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js">
        </script>
        <script>
            var calendar = null;
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    initialDate: new Date(),
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: "{{ route('refetch-events') }}",
                    dateClick: function(info) {
                        let startDate, endDate, allDay;
                        allDay = $('#is_all_day').prop('checked');
                        if (allDay) {
                            startDate = moment(info.date).format("YYYY-MM-DD");
                            endDate = moment(info.date).format("YYYY-MM-DD");
                            initializeStartDateEndDateFormat('Y-m-d', true)
                        } else {
                            initializeStartDateEndDateFormat('Y-m-d', false)
                            startDate = moment(info.date).format("YYYY-MM-DD HH:mm:ss");
                            endDate = moment(info.date).format("YYYY-MM-DD HH:mm:ss");
                        }
                        $('#startDateTime').val(startDate);
                        $('#endDateTime').val(endDate);
                        modalReset();
                        $("#eventModal").modal("show")
                    },
                    eventClick: function(info) {
                        modalReset();
                        $("#eventModal").modal("show");
                        const event = info.event;
                        $('#title').val(event.title);
                        $('#eventId').val(info.event.id);
                        $('#description').val(event.extendedProps.description);
                        $('#startDateTime').val(event.extendedProps.startDay);
                        $('#endDateTime').val(event.extendedProps.endDay);
                        $('#is_all_day').prop('checked', event.allDay);
                        $('#eventModal').modal('show');
                        $('#deleteEventBtn').show();
                        if (info.event.allDay) {
                            initializeStartDateEndDateFormat('Y-m-d', true)
                        } else {
                            initializeStartDateEndDateFormat('Y-m-d H:1', false)
                        }
                    }

                });
                calendar.render();
                $('#is_all_day').change(function() {
                    let is_all_day = $(this).prop('checked');
                    if (is_all_day) {
                        let start = $('#startDateTime').val().slice(0, 10);
                        $('#startDateTime').val(start);
                        let endDateTime = $('#endDateTime').val().slice(0, 10);
                        $('#endDateTime').val(endDateTime);
                        initializeStartDateEndDateFormat('Y-m-d', is_all_day)
                    } else {
                        let start = $('#startDateTime').val().slice(0, 10);
                        $('#startDateTime').val(start + '00:00');
                        let endDateTime = $('#endDateTime').val().slice(0, 10);
                        $('#endDateTime').val(endDateTime + '00:30');
                        initializeStartDateEndDateFormat('Y-m-d H:i', is_all_day)
                    }
                })
            })

            function initializeStartDateEndDateFormat(Format, allDay) {
                let timePicker = !allDay;
                $('#startDateTime').datetimepicker({
                    format: Format,
                    timepicker: timePicker
                });
                $('#endDateTime').datetimepicker({
                    format: Format,
                    timepicker: timePicker
                })
            }

            function modalReset() {
                $('#title').val("");
                $('#description').val("");
                $('#eventId').val("");
                $('#deleteEventBtn').hide();
            }

            function deleteEvent() {
                if (window.confirm("Are you sure, You want to delete this event")) {
                    let eventId = $("#eventId").val();
                    let url = '';
                    if (eventId) {
                        url = '{{ url('/') }}' + `/events/${eventId}`;
                    }
                    $.ajax({
                        type: 'DELETE',
                        url: url,
                        dataType: 'json',
                        data: {},
                        success: function(res) {
                            if (res.success) {
                                calendar.refetchEvents();
                                $('#eventModal').modal('hide');
                            } else {
                                alert('somthing going wrong')
                            }
                        }
                    })
                }
            }

            function submitEventFormData() {
                let eventId = $('#eventId').val();
                let url = "{{ route('events.store') }}";
                let postData = {
                    start: $('#startDateTime').val(),
                    end: $('#endDateTime').val(),
                    title: $('#title').val(),
                    description: $('#description').val(),
                    is_all_day: $('#is_all_day').prop('checked') ? 1 : 0
                };
                if (eventId) {
                    url = '{{ url('/') }}' + `/events/${eventId}`;
                    postData._method = "PUT";
                }
                $.ajax({
                    type: 'POST',
                    url: url,
                    dateType: 'json',
                    data: postData,
                    success: function(res) {
                        if (res.success) {
                            calendar.refetchEvents();
                            $('#eventModal').modal('hide');
                        } else {
                            alert('somthing going wrong')
                        }
                    }
                })
            }
        </script>
    @endpush
