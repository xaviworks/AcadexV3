{{--
    Toast Notification Handler Partial
    
    Handles session flash messages and converts them to toast notifications.
    Include this partial in views that need toast notifications.
--}}

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.notify?.success(@json(session('success')));
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.notify?.error(@json(session('error')));
        });
    </script>
@endif

@if(session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.notify?.success(@json(session('status')));
        });
    </script>
@endif

@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.notify?.warning(@json(session('warning')));
        });
    </script>
@endif
